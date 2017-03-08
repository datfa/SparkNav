package edu.utdallas.emse.sparknav;

import android.app.IntentService;
import android.content.Intent;
import android.os.Bundle;
import android.os.ResultReceiver;
import android.util.Log;

import com.google.android.gms.gcm.GcmPubSub;
import com.google.android.gms.gcm.GoogleCloudMessaging;
import com.google.android.gms.iid.InstanceID;

/**
 * Created by samiul on 3/8/17.
 */

public class GcmRegisterService extends IntentService {
    private final String TAG = "GCM_EXERCISE_REG";

    private final String topics[] = {"topic1","topic2"};

    public GcmRegisterService() {
        super("GcmRegisterService");
    }

    @Override
    protected void onHandleIntent(Intent intent) {
        if (intent != null) {
            ResultReceiver regReceiver =
                    intent.getParcelableExtra(Constants.KEY_REGISTRATION_RECEIVER);
            Bundle b = new Bundle();

            try {
                // TODO: get an Instance ID and use it to retrieve a registration token

                InstanceID instanceID = InstanceID.getInstance(this);
                String token = instanceID.getToken(getString(R.string.gcm_defaultSenderId),
                        GoogleCloudMessaging.INSTANCE_ID_SCOPE, null);
                Log.i(TAG, "GCM Registration Token: " + token);

                // TODO: Implement this method to send any registration to your app's servers.
                sendRegistrationToServer(token);

                // TODO: Subscribe to topic channels
                GcmPubSub pubSub = GcmPubSub.getInstance(this);
                for(String topic: topics) {
                    pubSub.subscribe(token, "/topics/" + topic, null);
                }


                if (regReceiver != null) {
                    b.putBoolean(Constants.KEY_REGISTRATION_COMPLETE, true);
                    regReceiver.send(Constants.RESULT_SUCCESS, b);
                }
            }
            catch (Exception e) {
                Log.e (TAG, e.toString());

                if (regReceiver != null) {
                    b.putBoolean(Constants.KEY_REGISTRATION_COMPLETE, false);
                    regReceiver.send(Constants.RESULT_ERROR, b);
                }
            }
        }
    }

    private void sendRegistrationToServer(String token) {
        // this function is where you would normally send the
        // registration token to your app server so you can
        // track the user that it is associated to. This allows
        // you to send personalized messages to them.
    }
}