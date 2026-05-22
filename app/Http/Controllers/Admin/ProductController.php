<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $products = Product::where('company_id', $companyId)
            ->when($request->search, fn ($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            }))
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'total' => Product::where('company_id', $companyId)->count(),
            'active' => Product::where('company_id', $companyId)->where('is_active', true)->count(),
            'inactive' => Product::where('company_id', $companyId)->where('is_active', false)->count(),
        ];

        return view('admin.products.index', compact('products', 'summary'));
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateProduct($request);

        Product::create([
            ...$validated,
            'company_id' => Auth::user()->company_id,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product added to catalog.');
    }

    public function edit(Product $product)
    {
        $this->authorizeProduct($product);

        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorizeProduct($product);
        $product->update([
            ...$this->validateProduct($request, $product),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $this->authorizeProduct($product);

        if ($product->orderItems()->exists()) {
            return back()->with('error', 'Cannot delete a product that has been ordered. Deactivate it instead.');
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product removed.');
    }

    protected function validateProduct(Request $request, ?Product $product = null): array
    {
        $companyId = Auth::user()->company_id;

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'nullable',
                'string',
                'max:64',
                Rule::unique('products', 'sku')->where('company_id', $companyId)->ignore($product?->id),
            ],
            'brand' => ['nullable', 'string', 'max:255'],
            'strength' => ['nullable', 'string', 'max:100'],
            'pack_size' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'mrp' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'unit_price' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    protected function authorizeProduct(Product $product): void
    {
        if ($product->company_id !== Auth::user()->company_id) {
            abort(403);
        }
    }
}
