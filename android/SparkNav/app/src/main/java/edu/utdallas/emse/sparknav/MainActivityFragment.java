package edu.utdallas.emse.sparknav;

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
import android.graphics.Color;
import android.os.CountDownTimer;
import android.os.Handler;
import android.os.Looper;
import android.os.ParcelUuid;
import android.support.annotation.Nullable;
import android.support.v4.app.Fragment;
import android.os.Bundle;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.webkit.ConsoleMessage;
import android.webkit.JavascriptInterface;
import android.webkit.WebChromeClient;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.ArrayAdapter;
import android.widget.AutoCompleteTextView;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;

/**
 * A placeholder fragment containing a simple view.
 */
public class MainActivityFragment extends Fragment {

    private static final String TAG = MainActivityFragment.class.getSimpleName();

    private Button mapButton;
    WebView webViewNormal;
    ImageView imageViewSparkNav;

    String[] rooms = {"2.11", "2.12", "3.11", "3.12", "4.11", "4.22", "4.12", "4.13"};

    public MainActivityFragment() {
    }

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        View rootView = inflater.inflate(R.layout.fragment_main, container, false);


        mapButton = (Button)rootView.findViewById(R.id.mapButton);
        mapButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
/*
                Intent intent = new Intent(getActivity(), NotificationResultActivity.class);
                startActivity(intent);
*/
                imageViewSparkNav.setVisibility(View.GONE);
                webViewNormal.setVisibility(View.VISIBLE);
            }
        });

        //Creating the instance of ArrayAdapter containing list of fruit names
        ArrayAdapter<String> adapter = new ArrayAdapter<String>
                (getActivity(), android.R.layout.select_dialog_item, rooms);
        //Getting the instance of AutoCompleteTextView
        AutoCompleteTextView actv = (AutoCompleteTextView) rootView.findViewById(R.id.autoCompleteTextView);
        actv.setThreshold(1);//will start working from first character
        actv.setAdapter(adapter);//setting the adapter data into the AutoCompleteTextView
        actv.setTextColor(Color.RED);

        imageViewSparkNav = (ImageView) rootView.findViewById(R.id.imageSparkNav);


        webViewNormal = (WebView) rootView.findViewById(R.id.webviewNormal);
        WebSettings webSettings = webViewNormal.getSettings();
        webSettings.setJavaScriptEnabled(true);
        webViewNormal.addJavascriptInterface(new MainActivityFragment.WebViewInterface(), "AndroidErrorReporter");
        webViewNormal.setWebChromeClient(new MainActivityFragment.CustomWebChromeClient());
        webViewNormal.setWebViewClient(new WebViewClient());
        webViewNormal.getSettings().setLoadWithOverviewMode(true);  //for fit to screen
        webViewNormal.getSettings().setUseWideViewPort(true);       //for fit to screen
        webViewNormal.loadUrl(Constants.MAP_URL_NORMAL);

        webViewNormal.setVisibility(View.GONE);
        return rootView;
    }

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
            Log.d(TAG, String.format("====> %s @ %d: %s", cm.message(),
                    cm.lineNumber(), cm.sourceId()));
            return true;
        }
    }
}
