package edu.utdallas.emse.sparknav;

import android.content.Intent;

import com.google.android.gms.iid.InstanceIDListenerService;

/**
 * Created by samiul on 3/8/17.
 */

public class InstanceIDRefreshService extends InstanceIDListenerService {

    private static final String TAG = "GCM_EXERCISE_IDLS";

    /**
     * Called if InstanceID token is updated. This may occur if the security of
     * the previous token had been compromised. This call is initiated by the
     * InstanceID provider.
     */
    @Override
    public void onTokenRefresh() {
        // Fetch updated Instance ID token and notify our app's server of any changes (if applicable).
        Intent intent = new Intent(this, GcmRegisterService.class);
        startService(intent);
    }
}