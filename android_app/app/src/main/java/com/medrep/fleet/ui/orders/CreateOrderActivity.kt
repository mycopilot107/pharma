package com.medrep.fleet.ui.orders

import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.text.Editable
import android.text.TextWatcher
import android.view.LayoutInflater
import android.view.View
import android.widget.ArrayAdapter
import android.widget.Toast
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import com.google.android.material.datepicker.CalendarConstraints
import com.google.android.material.datepicker.DateValidatorPointBackward
import com.google.android.material.datepicker.MaterialDatePicker
import com.medrep.fleet.data.model.Customer
import com.medrep.fleet.data.model.Product
import com.medrep.fleet.databinding.ActivityCreateOrderBinding
import com.medrep.fleet.databinding.ItemOrderProductRowBinding
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale
import java.util.TimeZone

class CreateOrderActivity : AppCompatActivity() {

    private lateinit var binding: ActivityCreateOrderBinding
    private val vm: CreateOrderViewModel by viewModels()

    private var selectedCustomer: Customer? = null
    private var selectedCustomerType: String = "doctor"
    private var customerList: List<Customer> = emptyList()
    private var productList: List<Product> = emptyList()
    private var orderDate: String = todayIso()

    private val customerHandler = Handler(Looper.getMainLooper())
    private var customerRunnable: Runnable? = null

    private val productRowBindings = mutableListOf<ItemOrderProductRowBinding>()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityCreateOrderBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(binding.toolbar)
        supportActionBar?.title = "New Order"
        supportActionBar?.setDisplayHomeAsUpEnabled(true)

        binding.etOrderDate.setText(todayDisplay())
        setupDatePicker()
        setupCustomerTypeChips()
        setupCustomerSearch()
        addProductRow()

        binding.btnAddProduct.setOnClickListener { addProductRow() }
        binding.btnSubmit.setOnClickListener { submit() }

        observeViewModel()
        // Load Doctors by default (first chip pre-selected)
        vm.loadCustomers(this, type = "doctor")
        vm.loadProducts(this)
    }

    // ── Customer type chips ───────────────────────────────────────────────────

    private fun setupCustomerTypeChips() {
        binding.chipGroupCustomerType.setOnCheckedStateChangeListener { _, checkedIds ->
            val type = when {
                checkedIds.contains(binding.chipTypeDoctor.id)      -> "doctor"
                checkedIds.contains(binding.chipTypeHospital.id)    -> "hospital"
                checkedIds.contains(binding.chipTypeClinic.id)      -> "clinic"
                checkedIds.contains(binding.chipTypeChemist.id)     -> "chemist"
                checkedIds.contains(binding.chipTypeDistributor.id) -> "distributor"
                else -> "doctor"
            }
            selectedCustomerType = type
            selectedCustomer = null
            binding.actvCustomer.setText("")
            vm.loadCustomers(this, type = type)
        }
    }

    // ── Customer search ───────────────────────────────────────────────────────

    private fun setupCustomerSearch() {
        vm.customers.observe(this) { customers ->
            customerList = customers
            val adapter = ArrayAdapter(
                this,
                android.R.layout.simple_dropdown_item_1line,
                customers.map { it.name }
            )
            binding.actvCustomer.setAdapter(adapter)
        }

        binding.actvCustomer.setOnFocusChangeListener { _, hasFocus ->
            if (hasFocus) binding.actvCustomer.showDropDown()
        }
        binding.actvCustomer.setOnClickListener { binding.actvCustomer.showDropDown() }

        binding.actvCustomer.addTextChangedListener(object : TextWatcher {
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) {}
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) {}
            override fun afterTextChanged(s: Editable?) {
                if (selectedCustomer != null && s.toString() != selectedCustomer?.name) {
                    selectedCustomer = null
                }
                customerRunnable?.let { customerHandler.removeCallbacks(it) }
                val q = s?.toString() ?: ""
                customerRunnable = Runnable {
                    vm.loadCustomers(
                        this@CreateOrderActivity,
                        type   = selectedCustomerType,
                        search = q.ifBlank { null }
                    )
                }
                customerHandler.postDelayed(customerRunnable!!, 400L)
            }
        })

        binding.actvCustomer.setOnItemClickListener { _, _, position, _ ->
            selectedCustomer = customerList.getOrNull(position)
        }
    }

    // ── Order date picker ─────────────────────────────────────────────────────

    private fun setupDatePicker() {
        val open = {
            val constraints = CalendarConstraints.Builder()
                .setValidator(DateValidatorPointBackward.now())
                .build()
            val picker = MaterialDatePicker.Builder.datePicker()
                .setTitleText("Order Date")
                .setCalendarConstraints(constraints)
                .setSelection(MaterialDatePicker.todayInUtcMilliseconds())
                .build()
            picker.addOnPositiveButtonClickListener { ms ->
                val utcFmt = SimpleDateFormat("yyyy-MM-dd", Locale.getDefault()).apply {
                    timeZone = TimeZone.getTimeZone("UTC")
                }
                val displayFmt = SimpleDateFormat("d MMM yyyy", Locale.getDefault()).apply {
                    timeZone = TimeZone.getTimeZone("UTC")
                }
                orderDate = utcFmt.format(Date(ms))
                binding.etOrderDate.setText(displayFmt.format(Date(ms)))
            }
            picker.show(supportFragmentManager, "order_date_picker")
        }
        binding.etOrderDate.setOnClickListener { open() }
        binding.tilOrderDate.setEndIconOnClickListener { open() }
    }

    // ── Dynamic product rows ──────────────────────────────────────────────────

    private fun addProductRow() {
        val rowBinding = ItemOrderProductRowBinding.inflate(
            LayoutInflater.from(this), binding.llProductRows, false
        )
        val rowIndex = productRowBindings.size + 1
        rowBinding.tvRowNumber.text = "Product $rowIndex"

        if (productList.isNotEmpty()) setProductAdapter(rowBinding)

        rowBinding.actvProduct.setOnFocusChangeListener { _, hasFocus ->
            if (hasFocus) rowBinding.actvProduct.showDropDown()
        }
        rowBinding.actvProduct.setOnClickListener { rowBinding.actvProduct.showDropDown() }

        rowBinding.btnRemoveRow.setOnClickListener {
            binding.llProductRows.removeView(rowBinding.root)
            productRowBindings.remove(rowBinding)
            renumberRows()
        }

        productRowBindings.add(rowBinding)
        binding.llProductRows.addView(rowBinding.root)
        updateRemoveButtonVisibility()
    }

    private fun setProductAdapter(rowBinding: ItemOrderProductRowBinding) {
        val names   = productList.map { buildProductLabel(it) }
        val adapter = ArrayAdapter(this, android.R.layout.simple_dropdown_item_1line, names)
        rowBinding.actvProduct.setAdapter(adapter)
    }

    private fun buildProductLabel(p: Product): String {
        val price = if (p.unitPrice > 0) " — ₹%.0f".format(p.unitPrice) else ""
        val brand = if (!p.brand.isNullOrBlank()) " (${p.brand})" else ""
        return "${p.name}$brand$price"
    }

    private fun renumberRows() {
        productRowBindings.forEachIndexed { i, rb -> rb.tvRowNumber.text = "Product ${i + 1}" }
        updateRemoveButtonVisibility()
    }

    private fun updateRemoveButtonVisibility() {
        val show = productRowBindings.size > 1
        productRowBindings.forEach { rb ->
            rb.btnRemoveRow.visibility = if (show) View.VISIBLE else View.GONE
        }
    }

    // ── Observe ───────────────────────────────────────────────────────────────

    private fun observeViewModel() {
        vm.products.observe(this) { products ->
            productList = products
            productRowBindings.forEach { setProductAdapter(it) }
        }

        vm.loading.observe(this) { loading ->
            binding.progressBar.visibility = if (loading) View.VISIBLE else View.GONE
            binding.btnSubmit.isEnabled    = !loading
        }

        vm.error.observe(this) { msg ->
            if (msg != null) Toast.makeText(this, msg, Toast.LENGTH_LONG).show()
        }

        vm.saved.observe(this) { saved ->
            if (saved) {
                Toast.makeText(this, "Order submitted!", Toast.LENGTH_SHORT).show()
                finish()
            }
        }
    }

    // ── Submit ────────────────────────────────────────────────────────────────

    private fun submit() {
        val customer = selectedCustomer
        if (customer == null) {
            binding.tilCustomer.error = "Please select a customer"
            return
        }
        binding.tilCustomer.error = null

        val items = mutableListOf<OrderLineItem>()
        for ((index, rb) in productRowBindings.withIndex()) {
            val label = rb.actvProduct.text.toString().trim()
            if (label.isEmpty()) {
                Toast.makeText(this, "Select a product for row ${index + 1}", Toast.LENGTH_SHORT).show()
                return
            }
            val product = productList.firstOrNull { buildProductLabel(it) == label }
            if (product == null) {
                Toast.makeText(this, "Invalid product in row ${index + 1}", Toast.LENGTH_SHORT).show()
                return
            }
            val qty = rb.etQty.text.toString().toIntOrNull()
            if (qty == null || qty < 1) {
                Toast.makeText(this, "Enter a valid quantity for row ${index + 1}", Toast.LENGTH_SHORT).show()
                return
            }
            val discount = rb.etDiscount.text.toString().toDoubleOrNull() ?: 0.0
            val remark   = rb.etRemark.text.toString().trim()
            items.add(OrderLineItem(product.id, qty, discount, remark.ifBlank { null }))
        }

        if (items.isEmpty()) {
            Toast.makeText(this, "Add at least one product", Toast.LENGTH_SHORT).show()
            return
        }

        vm.submitOrder(
            context    = this,
            customerId = customer.id,
            orderDate  = orderDate,
            items      = items,
            notes      = binding.etNotes.text.toString().trim(),
        )
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private fun todayIso(): String =
        SimpleDateFormat("yyyy-MM-dd", Locale.getDefault())
            .apply { timeZone = TimeZone.getTimeZone("UTC") }
            .format(Date())

    private fun todayDisplay(): String =
        SimpleDateFormat("d MMM yyyy", Locale.getDefault())
            .apply { timeZone = TimeZone.getTimeZone("UTC") }
            .format(Date())

    override fun onSupportNavigateUp(): Boolean {
        onBackPressedDispatcher.onBackPressed()
        return true
    }

    override fun onDestroy() {
        customerRunnable?.let { customerHandler.removeCallbacks(it) }
        super.onDestroy()
    }
}
