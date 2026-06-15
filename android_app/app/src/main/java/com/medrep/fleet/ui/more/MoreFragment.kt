package com.medrep.fleet.ui.more

import android.content.Intent
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.fragment.app.Fragment
import androidx.navigation.fragment.findNavController
import com.medrep.fleet.LoginActivity
import com.medrep.fleet.R
import com.medrep.fleet.data.prefs.TokenPrefs
import com.medrep.fleet.databinding.FragmentMoreBinding

class MoreFragment : Fragment() {

    private var _binding: FragmentMoreBinding? = null
    private val binding get() = _binding!!

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
    ): View {
        _binding = FragmentMoreBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        val name = TokenPrefs.getUserName(requireContext())
        binding.tvUserName.text  = name
        binding.tvUserEmail.text = TokenPrefs.getUserEmail(requireContext())
        binding.tvUserRole.text  = TokenPrefs.getUserRole(requireContext())
            .replaceFirstChar { it.uppercase() }
        binding.tvAvatarInitial.text = name.firstOrNull()?.uppercaseChar()?.toString() ?: "?"

        binding.cardTourPlan.setOnClickListener {
            findNavController().navigate(R.id.tourPlanFragment)
        }

        binding.btnLogout.setOnClickListener {
            TokenPrefs.clear(requireContext())
            val intent = Intent(requireContext(), LoginActivity::class.java)
            intent.flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
            startActivity(intent)
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
