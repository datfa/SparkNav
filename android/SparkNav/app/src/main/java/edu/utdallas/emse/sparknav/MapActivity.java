package edu.utdallas.emse.sparknav;

import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.util.Log;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;

import com.google.android.gms.common.api.GoogleApiClient;
//import com.google.android.gms.nearby.Nearby;
//import com.google.android.gms.nearby.messages.Message;
//import com.google.android.gms.nearby.messages.MessageListener;
//import com.google.android.gms.nearby.messages.Strategy;
//import com.google.android.gms.nearby.messages.SubscribeOptions;

public class MapActivity extends AppCompatActivity {

    private static final String TAG = "MapActivity";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_map);

        Log.i(TAG, "====> onCreate");

//        WebView myWebView = (WebView) findViewById(R.id.webview);
//        myWebView.setWebViewClient(new WebViewClient());
//        WebSettings webSettings = myWebView.getSettings();
//        webSettings.setJavaScriptEnabled(true);
//        myWebView.loadUrl("http://192.168.1.9/map");
    }
}
