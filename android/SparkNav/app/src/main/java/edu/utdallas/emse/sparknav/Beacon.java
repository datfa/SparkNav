package edu.utdallas.emse.sparknav;

import android.util.Log;

/**
 * Created by samiul on 3/17/17.
 */

public class Beacon {
    public static final String TAG = "BeaconClass";
    public static final int MAX_COUNT = 5;

    String id;
    int[] rssi = new int[10];
    double[] distance = new double[10];
    int head;
    int count;
    int txPower;

    public Beacon(String id, int rssi) {
        head = 0;
        count = 1;
        txPower = -51;
        this.id = id;
        this.rssi[head] = rssi;
        this.distance[head] = 10 * getDistance( rssi, txPower );
        Log.i(TAG, "==> newBeacon: " + id + " RSSI: " + rssi +" Distance: " + String.valueOf(this.distance[head]) +
                " Head: " + String.valueOf(head) + " Count: " + String.valueOf(count) );
        head++;
    }

    public String getId() {
        return id;
    }

    public void setId(String id) {
        this.id = id;
    }

    public void updateBeacon(int rssi) {
            if(count < MAX_COUNT) { //rssi/distance circular array will be full when MAX_COUNT
                count++;
            }
        this.rssi[head] = rssi;
        this.distance[head] = 10 * getDistance( rssi, txPower );
        Log.i(TAG, "==> updateBeacon: " + id + " RSSI: " + rssi +" Distance: " + String.valueOf(this.distance[head]) +
            " Head: " + String.valueOf(head) + " Count: " + String.valueOf(count) );
        head++;
        if(head == MAX_COUNT) {
            head = 0; //re-point to first cell
        }
    }

    public double getAvgDistance() {
        double totDistance = 0;
        for(int i=0; i<count; i++){
            totDistance += distance[i];
        }

        totDistance = totDistance / count;
        Log.i(TAG, "==> ID: " + id + " getAvgDistance: " + String.valueOf(totDistance));
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
