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
    private Button loadMapButton;
    private Button deleteMapButton;
    WebView myWebView;


    /////////////////// BLE ONLY START ///////////////

    private static final long SCAN_TIME_MILLIS = 60 * 1000; //1000 = 1sec

    // Receives the runnable that stops scanning after SCAN_TIME_MILLIS.
    private static final Handler handler = new Handler(Looper.getMainLooper());

    // An aggressive scan for nearby devices that reports immediately.
    private static final ScanSettings SCAN_SETTINGS =
            new ScanSettings.Builder().
                    setScanMode(ScanSettings.SCAN_MODE_LOW_LATENCY)
                    .setReportDelay(0)
                    .build();

    // The Eddystone-UID frame type byte.
    // See https://github.com/google/eddystone for more information.
    private static final byte EDDYSTONE_UID_FRAME_TYPE = 0x00;

    // The Eddystone Service UUID, 0xFEAA.
    private static final ParcelUuid EDDYSTONE_SERVICE_UUID =
            ParcelUuid.fromString("0000FEAA-0000-1000-8000-00805F9B34FB");

    // A filter that scans only for devices with the Eddystone Service UUID.
    private static final ScanFilter EDDYSTONE_SCAN_FILTER = new ScanFilter.Builder()
            .setServiceUuid(EDDYSTONE_SERVICE_UUID)
            .build();

    private static final List<ScanFilter> SCAN_FILTERS = buildScanFilters();

    private static List<ScanFilter> buildScanFilters() {
        List<ScanFilter> scanFilters = new ArrayList<>();
        scanFilters.add(EDDYSTONE_SCAN_FILTER);
        return scanFilters;
    }

    private ScanCallback scanCallback;
    private BluetoothLeScanner scanner;
    private ArrayList<Beacon> arrayList;
    /////////////////// BLE ONLY END ///////////////

    private Button scanButton;
    private int idIndex;

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

        NotificationManager mgr = (NotificationManager)getSystemService(NOTIFICATION_SERVICE);
        mgr.cancel(notifyID);


        /////////////////// BLE ONLY START ///////////////
        idIndex = 1;
        arrayList = new ArrayList<>();
        scanCallback = new ScanCallback() {
            @Override
            public void onScanResult(int callbackType, ScanResult result) {
                ScanRecord scanRecord = result.getScanRecord();
                if (scanRecord == null) {
                    Log.w(TAG, "==> Null ScanRecord for device " + result.getDevice().getAddress());
                    return;
                }

                byte[] serviceData = scanRecord.getServiceData(EDDYSTONE_SERVICE_UUID);
                if (serviceData == null) {
                    Log.w(TAG, "==> NOT FOUND: EDDYSTONE_SERVICE_UUID ");
                    return;
                }

                // We're only interested in the UID frame time since we need the beacon ID to register.
                if (serviceData[0] != EDDYSTONE_UID_FRAME_TYPE) {
                    Log.w(TAG, "===> NOT FOUND: EDDYSTONE_UID_FRAME_TYPE ");
                    return;
                }

                // Extract the beacon ID from the service data. Offset 0 is the frame type, 1 is the
                // Tx power, and the next 16 are the ID.
                // See https://github.com/google/eddystone/eddystone-uid for more information.
                byte[] id = Arrays.copyOfRange(serviceData, 2, 18);

                String beaconID = Utils.toHexString(id);
                int rssi = result.getRssi();
                //Log.i(TAG, "==> id " + beaconID + ", rssi " + rssi);

                addUpdateBeaconList(arrayList, beaconID, rssi);

                String minDistanceBeacon = beaconWithMinDistance(arrayList);
                Log.i(TAG, "==> Beacon with min distance: " + minDistanceBeacon);

                //String buidlFunctionCall = "drawCurrentLocation('" + beaconID + String.valueOf(idIndex)+ "');";
                String buidlFunctionCall = "drawCurrentLocation('" + minDistanceBeacon + "');";
                //callJavaScriptMethod("drawCurrentLocation('0001020304050607081a04514000b000');");
                callJavaScriptMethod(buidlFunctionCall);
                idIndex++;

                //beaconDistance = (TextView) getActivity().findViewById(R.id.beaconDistance);
                //beaconDistance.setText(distance);
//                insertIntoListAndFetchStatus(beacon);
            }

            @Override
            public void onScanFailed(int errorCode) {
                Log.e(TAG, "==> onScanFailed errorCode " + errorCode);
            }
        };

        createScanner();
/////////////////// BLE ONLY END /////////////////

        myWebView = (WebView) findViewById(R.id.webview);
        WebSettings webSettings = myWebView.getSettings();
        webSettings.setJavaScriptEnabled(true);
        myWebView.addJavascriptInterface(new WebViewInterface(), "AndroidErrorReporter");
        myWebView.setWebChromeClient(new CustomWebChromeClient());
        myWebView.setWebViewClient(new WebViewClient());
        myWebView.getSettings().setLoadWithOverviewMode(true);  //for fit to screen
        myWebView.getSettings().setUseWideViewPort(true);       //for fit to screen
        myWebView.loadUrl("http://192.168.1.161/map/viewmap.php");
        //myWebView.loadUrl("http://192.168.43.131/map/viewmap.php");
        //myWebView.loadUrl("http://192.168.1.9/map/viewmap.php");

/*
        loadMapButton = (Button)findViewById(R.id.loadMapButton);
        loadMapButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                callJavaScriptMethod("makeCircle(50,50);");
            }
        });

        deleteMapButton = (Button)findViewById(R.id.deleteMapButton);
        deleteMapButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                callJavaScriptMethod("deleteCircle();");
            }
        });
*/

        scanButton = (Button)findViewById(R.id.scanMapButton);
        scanButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                //Utils.setEnabledViews(false, scanButton);
                //arrayAdapter.clear();
                scanner.startScan(SCAN_FILTERS, SCAN_SETTINGS, scanCallback);
                Log.i(TAG, "==> starting scan");
                //client = new ProximityBeaconImpl(getActivity(), accountNameView.getText().toString());
                CountDownTimer countDownTimer = new CountDownTimer(SCAN_TIME_MILLIS, 100) {
                    @Override
                    public void onTick(long millisUntilFinished) {
                        double i = (1 - millisUntilFinished / (double) SCAN_TIME_MILLIS) * 100;
                        //progressBar.setProgress((int) i);
                    }

                    @Override
                    public void onFinish() {
                        //progressBar.setProgress(100);
                    }
                };
                countDownTimer.start();

                Runnable stopScanning = new Runnable() {
                    @Override
                    public void run() {
                        scanner.stopScan(scanCallback);
                        Log.i(TAG, "==> stopped scan");
                        //Utils.setEnabledViews(true, scanButton);
                    }
                };
                handler.postDelayed(stopScanning, SCAN_TIME_MILLIS);
            }
        });
    }

    private String beaconWithMinDistance(ArrayList<Beacon> list) {
        double minDistance = 1000;
        String beaconID = "NONE";
        for (Beacon beacon : list) {
            double avgDistance = beacon.getAvgDistance();
            if( minDistance > avgDistance ) {
                minDistance = avgDistance;
                beaconID = beacon.getId();
            }
        }

        return beaconID;
    }

    private void addUpdateBeaconList(ArrayList<Beacon> list, String id, int rssi) {
        for (Beacon beacon : list) {
            if (beacon.getId().equals(id)) {
                beacon.updateBeacon(rssi);
                return;
            }
        }
        Beacon newBeacon = new Beacon(id, rssi);
        list.add(newBeacon);
    }

    void callJavaScriptMethod(String data) {
        if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.KITKAT) {
            //myWebView.evaluateJavascript("loadMap();", null);
            myWebView.evaluateJavascript(data, null);
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

    private void createScanner() {
        BluetoothManager btManager =
                (BluetoothManager)getSystemService(Context.BLUETOOTH_SERVICE);
        BluetoothAdapter btAdapter = btManager.getAdapter();
        if (btAdapter == null || !btAdapter.isEnabled()) {
            Intent enableBtIntent = new Intent(BluetoothAdapter.ACTION_REQUEST_ENABLE);
            startActivityForResult(enableBtIntent, Constants.REQUEST_CODE_ENABLE_BLE);
        }
        if (btAdapter == null || !btAdapter.isEnabled()) {
            Log.e(TAG, "=====> Can't enable Bluetooth");
            Toast.makeText(this, "Can't enable Bluetooth", Toast.LENGTH_SHORT).show();
            return;
        }
        scanner = btAdapter.getBluetoothLeScanner();
    }
}
