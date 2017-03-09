package edu.utdallas.emse.sparknav;

import android.app.NotificationManager;
import android.content.Intent;
import android.os.Bundle;
import android.support.v7.app.AppCompatActivity;
import android.util.Log;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;

/**
 * Created by samiul on 3/8/17.
 */

public class NotificationResultActivity extends AppCompatActivity {

    public static final String TAG = "NotificationActivity";
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_map);

        Log.i(TAG, "NotificationResultActivity => onCreate");

        // When launched from an addAction Intent, we must manually cancel
        // the notification otherwise it will stay in the status bar
        Intent intent = getIntent();
        int notifyID = intent.getIntExtra("notifyID", 0);

        NotificationManager mgr = (NotificationManager)getSystemService(NOTIFICATION_SERVICE);
        mgr.cancel(notifyID);


        WebView myWebView = (WebView) findViewById(R.id.webview);
        WebSettings webSettings = myWebView.getSettings();
        webSettings.setJavaScriptEnabled(true);
        myWebView.setWebViewClient(new WebViewClient());
        myWebView.getSettings().setLoadWithOverviewMode(true);  //for fit to screen
        myWebView.getSettings().setUseWideViewPort(true);       //for fit to screen
        myWebView.loadUrl("http://192.168.1.161/map");
    }
}
