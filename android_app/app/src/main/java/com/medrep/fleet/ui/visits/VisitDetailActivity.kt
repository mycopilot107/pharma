package com.medrep.fleet.ui.visits

import android.net.Uri
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.text.Editable
import android.text.TextWatcher
import android.view.View
import android.widget.ArrayAdapter
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.FileProvider
import com.google.android.material.datepicker.CalendarConstraints
import com.google.android.material.datepicker.DateValidatorPointForward
import com.google.android.material.datepicker.MaterialDatePicker
import com.google.android.material.textfield.TextInputEditText
import com.google.android.material.textfield.TextInputLayout
import com.medrep.fleet.data.model.Customer
import com.medrep.fleet.databinding.ActivityVisitDetailBinding
import java.io.File
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale
import java.util.TimeZone

class VisitDetailActivity : AppCompatActivity() {

    companion object {
        const val EXTRA_VISIT_ID  = "visit_id"
        const val EXTRA_NEW_VISIT = "new_visit"
    }

    private lateinit var binding: ActivityVisitDetailBinding
    private val vm: VisitDetailViewModel by viewModels()

    private var isNewVisit = false
    private var photoUri: Uri? = null

    // checkout flow
    private var selectedFollowUpDate: String? = null

    // new-visit check-in flow
    private var selectedCheckInFollowUpDate: String? = null
    private var selectedCustomer: Customer? = null
    private var selectedCustomerType: String = "doctor"
    private var customerList: List<Customer> = emptyList()

    private val customerHandler = Handler(Looper.getMainLooper())
    private var customerRunnable: Runnable? = null

    private val cameraLauncher = registerForActivityResult(
        ActivityResultContracts.TakePicture()
    ) { success ->
        if (success) photoUri?.let { vm.pendingPhotoUri = it }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityVisitDetailBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(binding.toolbar)
        supportActionBar?.setDisplayHomeAsUpEnabled(true)

        val visitId = intent.getIntExtra(EXTRA_VISIT_ID, -1)
        isNewVisit  = intent.getBooleanExtra(EXTRA_NEW_VISIT, false)

        if (isNewVisit) {
            supportActionBar?.title = "New Visit"
            binding.groupCheckIn.visibility  = View.VISIBLE
            binding.groupCheckOut.visibility = View.GONE
            binding.groupDcr.visibility      = View.GONE
            binding.btnSave.text = "Check In"
            setupNewVisitForm()
        } else if (visitId != -1) {
            supportActionBar?.title = "Visit Details"
            vm.load(this, visitId)
        }

        observeViewModel()
        setupFollowUpPicker(binding.etFollowUp, binding.tilFollowUp) { date ->
            selectedFollowUpDate = date
        }

        binding.btnTakePhoto.setOnClickListener { launchCamera() }
        binding.btnSave.setOnClickListener { save() }
    }

    // ── New-visit form ────────────────────────────────────────────────────────

    private fun setupNewVisitForm() {
        // Customer type chips — each tap reloads the customer list by that type
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
            // Clear previous selection and reload by type
            selectedCustomer = null
            binding.actvCustomer.setText("")
            vm.loadCustomers(this, type = type)
        }

        // Customer autocomplete — observe LiveData from ViewModel
        vm.customers.observe(this) { customers ->
            customerList = customers
            val adapter = ArrayAdapter(
                this,
                android.R.layout.simple_dropdown_item_1line,
                customers.map { it.name }
            )
            binding.actvCustomer.setAdapter(adapter)
            // Show empty-state hint when the type has no customers
            if (customers.isEmpty()) {
                val typeName = selectedCustomerType.replaceFirstChar { it.uppercase() } + "s"
                binding.tvNoCustomers.text = "No $typeName found"
                binding.tvNoCustomers.visibility = View.VISIBLE
            } else {
                binding.tvNoCustomers.visibility = View.GONE
            }
        }

        binding.actvCustomer.setOnFocusChangeListener { _, hasFocus ->
            if (hasFocus) binding.actvCustomer.showDropDown()
        }
        binding.actvCustomer.setOnClickListener {
            binding.actvCustomer.showDropDown()
        }

        // Search with 400 ms debounce (still filtered by current type)
        binding.actvCustomer.addTextChangedListener(object : TextWatcher {
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) {}
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) {}
            override fun afterTextChanged(s: Editable?) {
                if (selectedCustomer != null && s.toString() != selectedCustomer?.name) {
                    selectedCustomer = null
                }
                customerRunnable?.let { customerHandler.removeCallbacks(it) }
                val query = s?.toString() ?: ""
                customerRunnable = Runnable {
                    vm.loadCustomers(
                        this@VisitDetailActivity,
                        type   = selectedCustomerType,
                        search = query.ifBlank { null }
                    )
                }
                customerHandler.postDelayed(customerRunnable!!, 400L)
            }
        })

        binding.actvCustomer.setOnItemClickListener { _, _, position, _ ->
            selectedCustomer = customerList.getOrNull(position)
        }

        // Initial load — Doctors pre-selected
        vm.loadCustomers(this, type = "doctor")

        // Follow-up date for check-in form
        setupFollowUpPicker(binding.etCheckInFollowUp, binding.tilCheckInFollowUp) { date ->
            selectedCheckInFollowUpDate = date
        }

        binding.btnCheckInPhoto.setOnClickListener { launchCamera() }
    }

    // ── Shared date picker factory ────────────────────────────────────────────

    private fun setupFollowUpPicker(
        editText: TextInputEditText,
        til: TextInputLayout,
        onSelected: (String) -> Unit
    ) {
        val open = {
            val constraints = CalendarConstraints.Builder()
                .setValidator(DateValidatorPointForward.now())
                .build()
            val picker = MaterialDatePicker.Builder.datePicker()
                .setTitleText("Next Follow-up Date")
                .setCalendarConstraints(constraints)
                .build()
            picker.addOnPositiveButtonClickListener { ms ->
                val utcFmt = SimpleDateFormat("yyyy-MM-dd", Locale.getDefault()).apply {
                    timeZone = TimeZone.getTimeZone("UTC")
                }
                val displayFmt = SimpleDateFormat("d MMM yyyy", Locale.getDefault()).apply {
                    timeZone = TimeZone.getTimeZone("UTC")
                }
                onSelected(utcFmt.format(Date(ms)))
                editText.setText(displayFmt.format(Date(ms)))
            }
            picker.show(supportFragmentManager, "follow_up_${editText.id}")
        }
        editText.setOnClickListener { open() }
        til.setEndIconOnClickListener { open() }
    }

    // ── Observe ViewModel ─────────────────────────────────────────────────────

    private fun observeViewModel() {
        vm.visit.observe(this) { visit ->
            if (visit == null) return@observe

            val name = visit.customer?.name ?: visit.placeName ?: "Visit #${visit.id}"
            binding.tvCustomerName.text       = name
            binding.tvCustomerName.visibility = View.VISIBLE
            binding.tvCheckInTime.text        = "Check-in: ${visit.checkInTime ?: "—"}"
            binding.tvCheckInTime.visibility  = View.VISIBLE

            if (visit.status == "completed") {
                binding.tvCheckOutTime.text        = "Check-out: ${visit.checkOutTime ?: "—"}"
                binding.groupCheckOut.visibility   = View.VISIBLE
                binding.groupDcr.visibility        = View.GONE
                binding.btnSave.visibility         = View.GONE
                binding.groupDcrSummary.visibility = View.VISIBLE
                binding.tvProducts.text = visit.productsPromoted?.joinToString(", ") ?: "—"
                binding.tvSamples.text  = visit.samplesGiven?.toString() ?: "0"
                binding.tvFollowUp.text = visit.followUpDate ?: "Not set"
            } else {
                binding.groupDcr.visibility = View.VISIBLE
                binding.btnSave.text        = "Check Out"
                visit.followUpDate?.let { date ->
                    selectedFollowUpDate = date
                    binding.etFollowUp.setText(formatApiDate(date))
                }
            }

            if (visit.isMockDetected) {
                binding.bannerMockGps.visibility = View.VISIBLE
            }
        }

        vm.loading.observe(this) { loading ->
            binding.progressBar.visibility = if (loading) View.VISIBLE else View.GONE
        }

        vm.error.observe(this) { msg ->
            if (msg != null) Toast.makeText(this, msg, Toast.LENGTH_LONG).show()
        }

        vm.saved.observe(this) { saved ->
            if (saved) {
                Toast.makeText(this, "Saved successfully", Toast.LENGTH_SHORT).show()
                finish()
            }
        }
    }

    // ── Save / Check-in ───────────────────────────────────────────────────────

    private fun save() {
        if (isNewVisit) {
            vm.checkIn(
                context      = this,
                customerId   = selectedCustomer?.id,
                visitType    = customerTypeToVisitType(selectedCustomerType),
                notes        = binding.etCheckInNotes.text.toString().trim(),
                followUpDate = selectedCheckInFollowUpDate,
                photoUri     = photoUri,
            )
        } else {
            val notes    = binding.etNotes.text.toString().trim()
            val products = binding.etProducts.text.toString()
                .split(",").map { it.trim() }.filter { it.isNotEmpty() }
            val samples  = binding.etSamples.text.toString().toIntOrNull() ?: 0
            vm.save(
                context      = this,
                notes        = notes,
                products     = products,
                samples      = samples,
                followUpDate = selectedFollowUpDate,
                photoUri     = photoUri,
            )
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Map the 5 customer types to the 3 available visit_type enum values. */
    private fun customerTypeToVisitType(customerType: String): String = when (customerType) {
        "hospital"    -> "hospital"
        "chemist",
        "distributor" -> "chemist"
        else          -> "doctor"   // doctor, clinic
    }

    private fun launchCamera() {
        val file = File(cacheDir, "visit_photo_${System.currentTimeMillis()}.jpg")
        val uri  = FileProvider.getUriForFile(this, "$packageName.fileprovider", file)
        photoUri = uri
        cameraLauncher.launch(uri)
    }

    private fun formatApiDate(apiDate: String): String {
        return try {
            val inFmt  = SimpleDateFormat("yyyy-MM-dd", Locale.getDefault())
            val outFmt = SimpleDateFormat("d MMM yyyy", Locale.getDefault())
            outFmt.format(inFmt.parse(apiDate)!!)
        } catch (_: Exception) {
            apiDate
        }
    }

    override fun onSupportNavigateUp(): Boolean {
        onBackPressedDispatcher.onBackPressed()
        return true
    }

    override fun onDestroy() {
        customerRunnable?.let { customerHandler.removeCallbacks(it) }
        super.onDestroy()
    }
}
