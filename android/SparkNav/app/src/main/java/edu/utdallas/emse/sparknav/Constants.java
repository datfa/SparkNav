package edu.utdallas.emse.sparknav;

/**
 * Created by samiul on 3/8/17.
 */

public class Constants {
    public static final int RESULT_SUCCESS = 0;
    public static final int RESULT_ERROR = 1;
    public static final String KEY_REGISTRATION_COMPLETE = "REGISTRATION_COMPLETE";
    public static final String KEY_REGISTRATION_RECEIVER = "REGISTRATION_RECEIVER";

    ///// FOR BLE START ////
    static final int REQUEST_CODE_ENABLE_BLE = 1001;
    ///// FOR BLE END ////

    //public static final String MAP_DOMAIN = "192.168.137.45";
    //public static final String MAP_DOMAIN = "ec2-35-166-130-129.us-west-2.compute.amazonaws.com";
    //public static final String MAP_DOMAIN = "192.168.1.7";
    public static final String MAP_DOMAIN = "192.168.1.166";
    //public static final String MAP_DOMAIN = "10.42.0.1";

    public static final String MAP_URL_ROOT = "http://" + MAP_DOMAIN + "/map/";
    public static final String REST_URL = "http://" + MAP_DOMAIN + ":8080/";

    //public static final String MAP_URL_EMERGENCY = MAP_URL_ROOT + "viewmap.php";
    public static final String MAP_URL_NORMAL = MAP_URL_ROOT + "viewmap.php";
    //public static final String MAP_URL_NORMAL = MAP_URL_ROOT + "viewmapdummy.php";
    public static final String ROOMS_URL = REST_URL + "getrooms";

}
