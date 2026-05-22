<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::where('company_id', $request->user()->company_id)
            ->where('is_active', true)
            ->when($request->search, fn ($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            }))
            ->when($request->category, fn ($q, $cat) => $q->where('category', $cat))
            ->orderBy('name')
            ->paginate(50);

        return ProductResource::collection($products);
    }

    public function show(Request $request, Product $product)
    {
        if ($product->company_id !== $request->user()->company_id || ! $product->is_active) {
            abort(404);
        }

        return new ProductResource($product);
    }
}
