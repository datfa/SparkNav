<?php
//var_dump($_POST["data"]);

$obj = json_decode($_POST["data"], false);

// build query

$last_x = 0;
$last_y = 0;

require_once 'connection.php';

//var_dump($obj);
foreach ($obj as $key => $value) {
	# code...
	// echo $value->x;
	// echo ", ";
	// echo $value->y;
	// echo "\n";

	$sql = "INSERT INTO path SET start_x=" . $last_x . ", start_y=" . $last_y . ", " .
	"end_x=" . $value->x . ", end_y=" . $value->y;

	$last_x = $value->x;
	$last_y = $value->y;

	mysqli_query($connection, $sql) or die(mysqli_error($connection));
}

echo "DONE";

?>