package com.medrep.fleet

import android.app.Application
import com.medrep.fleet.data.prefs.TokenPrefs
import org.osmdroid.config.Configuration

class MedRepApp : Application() {
    override fun onCreate() {
        super.onCreate()
        Configuration.getInstance().userAgentValue = packageName
        TokenPrefs.init(this)
    }
}
