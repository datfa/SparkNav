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
import android.view.inputmethod.InputMethodManager;
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
import android.widget.SimpleAdapter;
import android.widget.TextView;
import android.widget.Toast;

import com.android.volley.Request;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.JsonArrayRequest;
import com.android.volley.toolbox.JsonObjectRequest;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

/**
 * A placeholder fragment containing a simple view.
 */
public class MainActivityFragment extends Fragment {

    private static final String TAG = MainActivityFragment.class.getSimpleName();

    private Button mapButton;
    Button exitNavButton;
    WebView webViewNormal;
    ImageView imageViewSparkNav;
    AutoCompleteTextView autoCompleteTextView;

    List<String> rooms = new ArrayList<String>();
    Map<String, String> map = new HashMap<String, String>();

//    List<Map<String, Integer>> rooms = new ArrayList<Map<String, Integer>>();
//    Map<String, Integer> map;


    public MainActivityFragment() {
    }

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        JsonObjectRequest jsObjRequest = new JsonObjectRequest
                (Request.Method.GET, Constants.ROOMS_URL, null, new Response.Listener<JSONObject>() {

                    @Override
                    public void onResponse(JSONObject response) {
                        //mTxtDisplay.setText("Response: " + response.toString());
                        Log.i(TAG, "================> onResponse: " + response.toString());
                    }

                }, new Response.ErrorListener() {

                    @Override
                    public void onErrorResponse(VolleyError error) {
                        // TODO Auto-generated method stub
                        Log.i(TAG, "================> onErrorResponse: " + error.toString());
                    }
                });


        JsonArrayRequest jsArrRequest = new JsonArrayRequest
                (Request.Method.GET, Constants.ROOMS_URL, null, new Response.Listener<JSONArray>() {

                    @Override
                    public void onResponse(JSONArray response) {
                        Log.i(TAG, "================> onResponse: " + response.toString());
                        try {
                            for (int i = 0; i < response.length(); i++) {
                                JSONObject jb = (JSONObject) response.get(i);
                                String room_name = jb.getString("name");
                                String loc_id = jb.getString("loc_id");
                                Log.i(TAG, "ROOM NAME: " + room_name);
                                Log.i(TAG, "LOC ID: " + loc_id);
                                rooms.add(room_name);
                                map.put(room_name, loc_id);
                            }
                        } catch (JSONException e) {
                            e.printStackTrace();
                        }
                    }
                }, new Response.ErrorListener() {

                    @Override
                    public void onErrorResponse(VolleyError error) {
                        // TODO Auto-generated method stub
                        Log.i(TAG, "================> onErrorResponse: " + error.toString());
                    }
                });

        // Access the RequestQueue through your singleton class.
        //For JsonObject
        //AppController.getInstance().addToRequestQueue(jsObjRequest);
        // For JsonArray
        AppController.getInstance().addToRequestQueue(jsArrRequest);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        View rootView = inflater.inflate(R.layout.fragment_main, container, false);

        autoCompleteTextView = (AutoCompleteTextView) rootView.findViewById(R.id.autoCompleteTextView);

        mapButton = (Button)rootView.findViewById(R.id.mapButton);
        mapButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(getActivity(), NotificationResultActivity.class);
                intent.putExtra("emergencyLocation", "NONE-EMERGENCY");
                String room_name = autoCompleteTextView.getText().toString();
                String dst_loc_id = map.get(room_name);
                Log.i(TAG, "DEST LOC ID: " + dst_loc_id);
                intent.putExtra("dst_loc_id", dst_loc_id);
                startActivity(intent);

               /* imageViewSparkNav.setVisibility(View.GONE);
                webViewNormal.setVisibility(View.VISIBLE);
                mapButton.setVisibility(View.GONE);
                autoCompleteTextView.setVisibility(View.GONE);
                exitNavButton.setVisibility(View.VISIBLE);
                //autoCompleteTextView.clearFocus();

                // hide virtual keyboard
                InputMethodManager inputManager = (InputMethodManager)
                        getActivity().getSystemService(Context.INPUT_METHOD_SERVICE);
                inputManager.hideSoftInputFromWindow(autoCompleteTextView.getWindowToken(),
                        InputMethodManager.RESULT_UNCHANGED_SHOWN);

                String room_name = autoCompleteTextView.getText().toString();
                String src_loc_id = "65"; //TODO: this will come from ble scanner
                String dst_loc_id = map.get(room_name);
                Log.i(TAG, "DEST LOC ID: " + dst_loc_id);
                String buildFunctionCall = "loadMap(" + src_loc_id + ", " + dst_loc_id + ");";
                callJavaScriptMethod(buildFunctionCall);*/
            }
        });

        exitNavButton = (Button)rootView.findViewById(R.id.exitNavButton);
        exitNavButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                webViewNormal.setVisibility(View.GONE);
                imageViewSparkNav.setVisibility(View.VISIBLE);
                mapButton.setVisibility(View.VISIBLE);
                autoCompleteTextView.setVisibility(View.VISIBLE);
                exitNavButton.setVisibility(View.GONE);
            }
        });

        //Creating the instance of ArrayAdapter containing list of room names
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
        exitNavButton.setVisibility(View.GONE);
        return rootView;
    }

    void callJavaScriptMethod(String data) {
        if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.KITKAT) {
            //myWebView.evaluateJavascript("loadMap();", null);
            webViewNormal.evaluateJavascript(data, null);
        } else {
            //myWebView.loadUrl("javascript:loadMap();");
        }
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
