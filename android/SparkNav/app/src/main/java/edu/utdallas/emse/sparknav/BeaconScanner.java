package edu.utdallas.emse.sparknav;

import android.app.Activity;
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
import android.os.CountDownTimer;
import android.os.Handler;
import android.os.Looper;
import android.os.ParcelUuid;
import android.util.Log;
import android.webkit.WebView;
import android.widget.Toast;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;

/**
 * Created by samiul on 4/16/17.
 */

public class BeaconScanner {

    public static final String TAG = "BeaconScanner";

    Context mContext;
    Activity activity;
    WebView myWebView;

    boolean sourceDetected;
    double lastReadDistance;

    String src_loc_id;
    String dst_loc_id;

    long scanCount;

    boolean alreadyScanning;

    /////////////////// BLE ONLY START ///////////////

    private static final long SCAN_TIME_MILLIS = 3 * 60 * 1000; //1000 = 1sec, 60 * 1000 = 1 min

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


    public BeaconScanner(Context mContext, Activity activity, WebView myWebView, String dst_loc_id) {
        this.mContext = mContext.getApplicationContext();
        this.activity = activity;
        this.myWebView = myWebView;
        this.dst_loc_id = dst_loc_id;

        lastReadDistance = 0;
        scanCount = 0;
        sourceDetected = false;
        alreadyScanning = false;

        /////////////////// BLE ONLY START ///////////////
        arrayList = new ArrayList<>();
        scanCallback = new ScanCallback() {
            @Override
            public void onScanResult(int callbackType, ScanResult result) {
                scanCount++;
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
                //Log.i(TAG, "ScanCount ==> " + scanCount);
                if( 4 < scanCount) {
                    update_current_location(minDistanceBeacon);
                    removeDeadBeacon(arrayList);
                }
            }

            @Override
            public void onScanFailed(int errorCode) {
                Log.e(TAG, "==> onScanFailed errorCode " + errorCode);
            }
        };

        createScanner();
/////////////////// BLE ONLY END /////////////////
    }

    private void update_current_location(String beaconId) {
        String buildFunctionCall;
        beaconId = beaconId.toUpperCase();
        if( false == sourceDetected ) {
            if( 1 < lastReadDistance ) {
                Log.i(TAG, "update_current_location: Ignore any beacon which are far than 1 meter");
                return;
            }
            sourceDetected = true;
//            src_loc_id = getLocIdFromBeaconId( beaconId );
            if(dst_loc_id == null) {
                buildFunctionCall = "loadMapBeaconExit('" + beaconId + "');";
            } else {
                buildFunctionCall = "loadMapBeacon('" + beaconId + "', " + dst_loc_id + ");";
            }
            Log.i(TAG, buildFunctionCall);
            callJavaScriptMethod(buildFunctionCall);
        } else {
            buildFunctionCall = "drawCurrentLocation('" + beaconId + "');";
            Log.i(TAG, buildFunctionCall);
            callJavaScriptMethod(buildFunctionCall);
        }
    }

    private String beaconWithMinDistance(ArrayList<Beacon> list) {
        double minDistance = 1000;
        String beaconID = "NONE";
        String beaconName = "NONE";
        for (Beacon beacon : list) {
            double avgDistance = beacon.getAvgDistance();
            if( minDistance > avgDistance ) {
                minDistance = avgDistance;
                beaconID = beacon.getId();
                beaconName = beacon.getName();
            }
        }

        lastReadDistance = minDistance;
        Log.i(TAG, "Min distance Beacon ==> " + beaconName);
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

    private void removeDeadBeacon(ArrayList<Beacon> list) {
        long currentTimeStamp = System.currentTimeMillis()/1000;
        for (Beacon beacon : list) {
            if ( 4 < ( currentTimeStamp - beacon.getLastUpdateTimeStamp()) ) {
                beacon.updateBeacon(-200);
            }
        }
    }

    private void createScanner() {
        BluetoothManager btManager =
                (BluetoothManager)mContext.getSystemService(Context.BLUETOOTH_SERVICE);
        BluetoothAdapter btAdapter = btManager.getAdapter();
        if (btAdapter == null || !btAdapter.isEnabled()) {
            Intent enableBtIntent = new Intent(BluetoothAdapter.ACTION_REQUEST_ENABLE);
            activity.startActivityForResult(enableBtIntent, Constants.REQUEST_CODE_ENABLE_BLE);
        }
        if (btAdapter == null || !btAdapter.isEnabled()) {
            Log.e(TAG, "=====> Can't enable Bluetooth");
            //Toast.makeText(this, "Can't enable Bluetooth", Toast.LENGTH_SHORT).show();
            return;
        }
        scanner = btAdapter.getBluetoothLeScanner();

    }

    public void stopScanner() {
        if( false == alreadyScanning ) {
            Log.i(TAG, "=====> Sanner Not Running");
            return;
        }
        scanner.stopScan(scanCallback);
    }

    public void startScanner() {
        if( alreadyScanning ) {
            Log.i(TAG, "===================> Already Scanning <====================");
            return;
        }
        alreadyScanning = true;
        scanner.startScan(SCAN_FILTERS, SCAN_SETTINGS, scanCallback);
        Log.i(TAG, "=====> starting scan");

//        CountDownTimer countDownTimer = new CountDownTimer(SCAN_TIME_MILLIS, 100) {
//            @Override
//            public void onTick(long millisUntilFinished) {
//                double i = (1 - millisUntilFinished / (double) SCAN_TIME_MILLIS) * 100;
//                //progressBar.setProgress((int) i);
//            }
//
//            @Override
//            public void onFinish() {
//                //progressBar.setProgress(100);
//            }
//        };
//        countDownTimer.start();

        Runnable stopScanning = new Runnable() {
            @Override
            public void run() {
                scanner.stopScan(scanCallback);
                Log.i(TAG, "=====> stopped scan");
                alreadyScanning = false;
                //Utils.setEnabledViews(true, scanButton);
            }
        };
        handler.postDelayed(stopScanning, SCAN_TIME_MILLIS);
    }

    void callJavaScriptMethod(String data) {
        if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.KITKAT) {
            //myWebView.evaluateJavascript("loadMap();", null);
            myWebView.evaluateJavascript(data, null);
        } else {
            //myWebView.loadUrl("javascript:loadMap();");
        }
    }
}
