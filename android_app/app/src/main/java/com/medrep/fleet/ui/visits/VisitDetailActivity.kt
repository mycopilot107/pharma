package com.medrep.fleet.ui.visits

import android.net.Uri
import android.os.Bundle
import android.view.View
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.FileProvider
import com.medrep.fleet.databinding.ActivityVisitDetailBinding
import java.io.File

class VisitDetailActivity : AppCompatActivity() {

    companion object {
        const val EXTRA_VISIT_ID  = "visit_id"
        const val EXTRA_NEW_VISIT = "new_visit"
    }

    private lateinit var binding: ActivityVisitDetailBinding
    private val vm: VisitDetailViewModel by viewModels()

    private var photoUri: Uri? = null

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

        val visitId  = intent.getIntExtra(EXTRA_VISIT_ID, -1)
        val newVisit = intent.getBooleanExtra(EXTRA_NEW_VISIT, false)

        if (newVisit) {
            supportActionBar?.title = "New Visit"
            binding.groupCheckIn.visibility  = View.VISIBLE
            binding.groupCheckOut.visibility = View.GONE
            binding.groupDcr.visibility      = View.VISIBLE
            binding.btnSave.text = "Check In"
        } else if (visitId != -1) {
            supportActionBar?.title = "Visit Details"
            vm.load(this, visitId)
        }

        observeViewModel()

        binding.btnTakePhoto.setOnClickListener { launchCamera() }
        binding.btnClearSignature.setOnClickListener { binding.signaturePad.clear() }
        binding.btnSave.setOnClickListener { save() }
    }

    private fun observeViewModel() {
        vm.visit.observe(this) { visit ->
            if (visit == null) return@observe
            binding.tvCustomerName.text = visit.customer?.name ?: "Customer #${visit.customerId}"
            binding.tvCheckInTime.text  = "Check-in: ${visit.checkInTime}"

            if (visit.status == "completed") {
                binding.tvCheckOutTime.text = "Check-out: ${visit.checkOutTime ?: ""}"
                binding.groupCheckOut.visibility  = View.VISIBLE
                binding.groupDcr.visibility       = View.GONE
                binding.btnSave.visibility        = View.GONE
                binding.groupDcrSummary.visibility = View.VISIBLE
                binding.tvProducts.text = visit.productsPromoted?.joinToString(", ") ?: ""
                binding.tvSamples.text  = visit.samplesGiven?.toString() ?: "0"
                binding.tvFollowUp.text = visit.followUpDate ?: "N/A"
            } else {
                binding.groupDcr.visibility = View.VISIBLE
                binding.btnSave.text        = "Check Out"
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

    private fun launchCamera() {
        val file = File(cacheDir, "visit_photo_${System.currentTimeMillis()}.jpg")
        val uri  = FileProvider.getUriForFile(this, "$packageName.fileprovider", file)
        photoUri = uri
        cameraLauncher.launch(uri)
    }

    private fun save() {
        val notes    = binding.etNotes.text.toString().trim()
        val products = binding.etProducts.text.toString()
            .split(",").map { it.trim() }.filter { it.isNotEmpty() }
        val samples  = binding.etSamples.text.toString().toIntOrNull() ?: 0
        val followUp = binding.etFollowUp.text.toString().trim().ifEmpty { null }
        val sig      = binding.signaturePad.toBase64()

        vm.save(
            context         = this,
            notes           = notes,
            products        = products,
            samples         = samples,
            followUpDate    = followUp,
            signatureBase64 = sig,
            photoUri        = photoUri
        )
    }

    override fun onSupportNavigateUp(): Boolean {
        onBackPressedDispatcher.onBackPressed()
        return true
    }
}
