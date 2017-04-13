<?php
$val = $_POST["field5"];

//curl -H "Content-Type:application/json" -H "Authorization:key=AIzaSyDuY7yVM2QJEHYDvi2p6Qm_5vKYeP37Yq8" --data '{ "to": "/topics/topic1", "data": {"message": "Topic1 Message from GCM server"}}' https://gcm-http.googleapis.com/gcm/send

// API access key from Google API's Console
define('API_ACCESS_KEY', 'AIzaSyDuY7yVM2QJEHYDvi2p6Qm_5vKYeP37Yq8');
//$registrationIds = array( $_GET['id'] );

$registrationIds = "/topics/topic1";

// prep the bundle
$msg = array
	(
	'message' => $val,
);
$fields = array
	(
	'to' => $registrationIds,
	'data' => $msg,
);

$headers = array
	(
	'Authorization: key=' . API_ACCESS_KEY,
	'Content-Type: application/json',
);

$result = "NONE";

$ch = curl_init();
//curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
curl_setopt($ch, CURLOPT_URL, 'https://gcm-http.googleapis.com/gcm/send');

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
$result = curl_exec($ch);
curl_close($ch);

//echo $result;
echo "Message sent!!";

?>