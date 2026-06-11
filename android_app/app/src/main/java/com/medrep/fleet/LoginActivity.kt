package com.medrep.fleet

import android.content.Intent
import android.os.Bundle
import android.view.View
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import com.medrep.fleet.data.api.ApiClient
import com.medrep.fleet.data.prefs.TokenPrefs
import com.medrep.fleet.databinding.ActivityLoginBinding
import kotlinx.coroutines.launch

class LoginActivity : AppCompatActivity() {

    private lateinit var binding: ActivityLoginBinding

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        if (TokenPrefs.isLoggedIn(this)) {
            goToMain()
            return
        }

        binding = ActivityLoginBinding.inflate(layoutInflater)
        setContentView(binding.root)

        binding.btnLogin.setOnClickListener { attemptLogin() }
    }

    private fun attemptLogin() {
        val email    = binding.etEmail.text.toString().trim()
        val password = binding.etPassword.text.toString()

        if (email.isEmpty() || password.isEmpty()) {
            binding.tvError.text = "Please enter email and password"
            binding.tvError.visibility = View.VISIBLE
            return
        }

        setLoading(true)
        binding.tvError.visibility = View.GONE

        lifecycleScope.launch {
            try {
                val response = ApiClient.create().login(
                    mapOf(
                        "email"       to email,
                        "password"    to password,
                        "device_name" to "MedRep Fleet Mobile"
                    )
                )
                if (response.isSuccessful) {
                    val body = response.body()!!
                    TokenPrefs.saveToken(this@LoginActivity, body.token)
                    TokenPrefs.saveUser(
                        this@LoginActivity,
                        body.user.id,
                        body.user.name,
                        body.user.email,
                        body.user.role
                    )
                    goToMain()
                } else {
                    binding.tvError.text = "Invalid credentials. Please try again."
                    binding.tvError.visibility = View.VISIBLE
                }
            } catch (e: Exception) {
                binding.tvError.text = "Network error: ${e.message}"
                binding.tvError.visibility = View.VISIBLE
            } finally {
                setLoading(false)
            }
        }
    }

    private fun setLoading(loading: Boolean) {
        binding.btnLogin.isEnabled = !loading
        binding.progressBar.visibility = if (loading) View.VISIBLE else View.GONE
    }

    private fun goToMain() {
        startActivity(Intent(this, MainActivity::class.java))
        finish()
    }
}
