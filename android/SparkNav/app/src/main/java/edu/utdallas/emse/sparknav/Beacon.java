package edu.utdallas.emse.sparknav;

import android.util.Log;

/**
 * Created by samiul on 3/17/17.
 */

public class Beacon {
    public static final String TAG = "BeaconClass";
    public static final int MAX_COUNT_AVG = 10;

    String id;
    String name;
    int[] rssi = new int[10];
    double[] distance = new double[10];
    int head;
    int count;
    int txPower;
    long lastUpdateTimeStamp;

    public Beacon(String id, int rssi) {
        head = 0;
        count = 1;
        txPower = -51;
        this.id = id;
        this.name = id.substring(18, 20);
        this.rssi[head] = rssi;
        this.lastUpdateTimeStamp = System.currentTimeMillis()/1000;
        this.distance[head] = 10 * getDistance( rssi, txPower );
//        Log.i(TAG, "==> newBeacon: " + id + " RSSI: " + rssi +" Distance: " + String.valueOf(this.distance[head]) +
        Log.i(TAG, "==> newBeacon: " + name + " RSSI: " + rssi +" Distance: " + String.valueOf(this.distance[head]) +
                " Head: " + String.valueOf(head) + " Count: " + String.valueOf(count) );
        head++;
    }

    public String getId() {
        return id;
    }

    public String getName() {
        return name;
    }

    public long getLastUpdateTimeStamp() {
        return lastUpdateTimeStamp;
    }

    public void setId(String id) {
        this.id = id;
    }

    public void updateBeacon(int rssi) {
            if(count < MAX_COUNT_AVG) { //rssi/distance circular array will be full when MAX_COUNT_AVG
                count++;
            }
        this.rssi[head] = rssi;
        this.distance[head] = 10 * getDistance( rssi, txPower );
        this.lastUpdateTimeStamp = System.currentTimeMillis()/1000;
        Log.d(TAG, "==> updateBeacon: " + id + " RSSI: " + rssi +" Distance: " + String.valueOf(this.distance[head]) +
            " Head: " + String.valueOf(head) + " Count: " + String.valueOf(count) +
            " TS: " + String.valueOf(lastUpdateTimeStamp));
        head++;
        if(head == MAX_COUNT_AVG) {
            head = 0; //re-point to first cell
        }
    }

    public double getAvgDistance() {
        double totDistance = 0;
        for(int i=0; i<count; i++){
            totDistance += distance[i];
        }

        totDistance = totDistance / count;
        Log.d(TAG, "==> ID: " + name + " getAvgDistance: " + String.valueOf(totDistance));
        return totDistance;
    }

    public double getDistance(int rssi, int txPower) {
    /*
     * RSSI = TxPower - 10 * n * lg(d)
     * n = 2 (in free space)
     *
     * d = 10 ^ ((TxPower - RSSI) / (10 * n))
     */
        //for eddy add 41 as Eddystone standard returns RSSI@0m
        txPower -= 41;
        return Math.pow(10d, ((double) txPower - rssi) / (10 * 2));
    }

}
