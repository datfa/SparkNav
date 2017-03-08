package edu.utdallas.emse.sparknav;

import android.os.Bundle;
import android.util.Log;

import com.google.android.gms.gcm.GcmListenerService;

/**
 * Created by samiul on 3/8/17.
 */

public class SparkNavGcmListenerService extends GcmListenerService {

    private static final String TAG = "GCM_EXERCISE_GLS";

    @Override
    public void onMessageReceived(String from, Bundle data) {
        String message = data.getString("message");

        if (from.startsWith("/topics/")) {
            Log.d(TAG, "Received a topic broadcast");
            // message received from some topic.
        }
        else {
            Log.d(TAG, "Received a downstream message");
            // normal downstream message.
        }
        Log.d(TAG, "From: " + from);
        Log.d(TAG, "Message: " + message);
    }
}