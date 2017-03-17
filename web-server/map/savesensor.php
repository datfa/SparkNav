<?php
//var_dump($_POST);

$obj = json_decode($_POST["sensor"], false);

//$obj = json_decode($_POST, false);

// build query

require_once 'connection.php';

//var_dump($obj);

// foreach ($obj as $key => $value) {
// 	# code...
// 	// echo $value->x;
// 	// echo ", ";
// 	// echo $value->y;
// 	// echo "\n";

$sql = "UPDATE path SET name='" . $obj->id . "' WHERE start_x=" . $obj->x . " AND start_y=" . $obj->y;
// 	"end_x=" . $value->x . ", end_y=" . $value->y;

// 	$last_x = $value->x;
// 	$last_y = $value->y;

mysqli_query($connection, $sql) or die(mysqli_error($connection));
// }

echo "DONE";

?>