package edu.utdallas.emse.sparknav;


import android.app.SearchManager;
import android.content.ComponentName;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.os.Handler;
import android.os.ResultReceiver;;
import android.support.design.widget.FloatingActionButton;
import android.support.design.widget.Snackbar;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.SearchView;
import android.support.v7.widget.Toolbar;
import android.util.Log;
import android.view.MenuInflater;
import android.view.View;
import android.view.Menu;
import android.view.MenuItem;

import com.google.android.gms.common.ConnectionResult;
import com.google.android.gms.common.GoogleApiAvailability;

public class MainActivity extends AppCompatActivity {

    private final String TAG = "MAIN_ACTIVITY";
    private static final int PLAY_SERVICES_RESOLUTION_REQUEST = 9000;

    private boolean mRegistered;
    protected RegistrationReceiver mRegReceiver;


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        // subclass of ResultReceiver that indicates when registration
        // has taken place

        mRegReceiver = new RegistrationReceiver(new Handler());

        if (checkPlayServices()) {
            Intent intent = new Intent(this, GcmRegisterService.class);
            intent.putExtra(Constants.KEY_REGISTRATION_RECEIVER, mRegReceiver);
            startService(intent);
        }

        Toolbar toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);

      /*
      FloatingActionButton fab = (FloatingActionButton) findViewById(R.id.fab);
        fab.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Snackbar.make(view, "Replace with your own action", Snackbar.LENGTH_LONG)
                        .setAction("Action", null).show();
            }
        });
        */
    }

    @Override
    public void onStart() {
        super.onStart();
    }

    @Override
    public void onStop() {
        super.onStop();
    }

    /*@Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.menu_main, menu);
        return true;
    }*/

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the options menu from XML
        MenuInflater inflater = getMenuInflater();
        inflater.inflate(R.menu.menu_main, menu);

        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
        int id = item.getItemId();

        //noinspection SimplifiableIfStatement
        if (id == R.id.action_settings) {
            return true;
        }

        return super.onOptionsItemSelected(item);
    }

    private boolean checkPlayServices() {
        GoogleApiAvailability apiAvailability = GoogleApiAvailability.getInstance();
        int resultCode = apiAvailability.isGooglePlayServicesAvailable(this);
        if (resultCode != ConnectionResult.SUCCESS) {
            if (apiAvailability.isUserResolvableError(resultCode)) {
                apiAvailability.getErrorDialog(this, resultCode, PLAY_SERVICES_RESOLUTION_REQUEST)
                        .show();
            } else {
                Log.i(TAG, "This device is not supported.");
                finish(); // ends this Activity
            }
            return false;
        }
        return true;
    }

    class RegistrationReceiver extends ResultReceiver {
        public RegistrationReceiver(Handler handler) {
            super(handler);
        }

        @Override
        protected void onReceiveResult(int resultCode, Bundle resultData) {
//            ProgressBar pb = (ProgressBar)findViewById(R.id.progressBar);
//            // regardless of result, hide the progress bar
//            pb.setVisibility(ProgressBar.GONE);

            mRegistered = resultData.getBoolean(Constants.KEY_REGISTRATION_COMPLETE);

            //TextView status = (TextView)findViewById(R.id.tvGcmStatus);
            if (mRegistered) {
                //status.setText(getString(R.string.reg_success));
                Log.i(TAG, getString(R.string.reg_success));
            }
            else {
                //status.setText(getString(R.string.reg_error));
                Log.i(TAG, getString(R.string.reg_error));
            }
        }
    }

}
