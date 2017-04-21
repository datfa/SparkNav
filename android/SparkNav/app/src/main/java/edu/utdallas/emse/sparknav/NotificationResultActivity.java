package edu.utdallas.emse.sparknav;

import android.app.NotificationManager;
import android.bluetooth.BluetoothAdapter;
import android.bluetooth.BluetoothManager;
import android.bluetooth.le.BluetoothLeScanner;
import android.bluetooth.le.ScanCallback;
import android.bluetooth.le.ScanFilter;
import android.bluetooth.le.ScanRecord;
import android.bluetooth.le.ScanResult;
import android.bluetooth.le.ScanSettings;
import android.content.Context;
import android.content.Intent;
import android.content.pm.ActivityInfo;
import android.os.Bundle;
import android.os.CountDownTimer;
import android.os.Handler;
import android.os.Looper;
import android.os.ParcelUuid;
import android.support.v7.app.AppCompatActivity;
import android.util.Log;
import android.view.View;
import android.webkit.ConsoleMessage;
import android.webkit.JavascriptInterface;
import android.webkit.WebChromeClient;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.Button;
import android.widget.TextView;
import android.widget.Toast;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;
import java.util.StringTokenizer;

/**
 * Created by samiul on 3/8/17.
 */

public class NotificationResultActivity extends AppCompatActivity {

    public static final String TAG = "NotificationActivity";
    //private Button loadMapButton;
    //private Button deleteMapButton;
    WebView myWebView;
    BeaconScanner beaconScanner;
    static Context context;

    private Button scanButton;
    private int idIndex;

    @Override
    protected void onDestroy() {
        super.onDestroy();
        beaconScanner.stopScanner();
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        setRequestedOrientation (ActivityInfo.SCREEN_ORIENTATION_PORTRAIT);

        setContentView(R.layout.activity_map);

        Log.i(TAG, "NotificationResultActivity => onCreate");

        // When launched from an addAction Intent, we must manually cancel
        // the notification otherwise it will stay in the status bar
        Intent intent = getIntent();
        int notifyID = intent.getIntExtra("notifyID", 0);
        String emergencyLocation = intent.getStringExtra("emergencyLocation");
        String dst_loc_id = intent.getStringExtra("dst_loc_id");

        NotificationManager mgr = (NotificationManager)getSystemService(NOTIFICATION_SERVICE);
        mgr.cancel(notifyID);

        myWebView = (WebView) findViewById(R.id.webview);
        WebSettings webSettings = myWebView.getSettings();
        webSettings.setJavaScriptEnabled(true);
        myWebView.addJavascriptInterface(new WebViewInterface(), "AndroidErrorReporter");
        myWebView.setWebChromeClient(new CustomWebChromeClient());
        //myWebView.setWebViewClient(new WebViewClient());
        myWebView.setWebViewClient(new WebViewClient() {

            public void onPageFinished(WebView view, String url) {
                Log.i(TAG, "onPageFinished: " + url);
                //beaconScanner.startScanner();
            }
        });

        myWebView.getSettings().setLoadWithOverviewMode(true);  //for fit to screen
        myWebView.getSettings().setUseWideViewPort(true);       //for fit to screen

        myWebView.loadUrl(Constants.MAP_URL_NORMAL);

        beaconScanner = new BeaconScanner(this, this, myWebView, dst_loc_id);

        scanButton = (Button)findViewById(R.id.scanMapButton);
        scanButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                //beaconScanner.startScanner();
                //Utils.setEnabledViews(false, scanButton);
                //arrayAdapter.clear();
                beaconScanner.stopScanner();
                finish();
            }
        });
    }

   /* void callJavaScriptMethod(String data) {
        if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.KITKAT) {
            //myWebView.evaluateJavascript("loadMap();", null);
            myWebView.evaluateJavascript(data, null);
        } else {
            //myWebView.loadUrl("javascript:loadMap();");
        }
    }*/

    private class WebViewInterface{

        @JavascriptInterface
        public void onError(String error){
            throw new Error(error);
        }
    }

    class CustomWebChromeClient extends WebChromeClient {
        private static final String TAG = "CustomWebChromeClient";

        @Override
        public boolean onConsoleMessage(ConsoleMessage cm) {
            Log.i(TAG, String.format("====> %s @ %d: %s", cm.message(),
                    cm.lineNumber(), cm.sourceId()));
            return true;
        }
    }

}
