<?php
//var_dump($_POST["data"]);
$obj = json_decode($_POST["data"], false);

// build query

$last_loc = 0;

require_once 'connection.php';

//var_dump($obj);
foreach ($obj as $key => $value) {
	# code...
	// echo $value->x;
	// echo ", ";
	// echo $value->y;
	// echo "\n";

	$sql = "SELECT ID FROM location WHERE x=" . $value->x . " AND y=" . $value->y;
	$rsItems = mysqli_query($connection, $sql) or die(mysqli_error($connection));

	if ($row_rsItems = mysqli_fetch_assoc($rsItems)) {
		$loc_id = $row_rsItems[ID];
	} else {
		$sql = "INSERT INTO location SET x=" . $value->x . ", y=" . $value->y;
		mysqli_query($connection, $sql) or die(mysqli_error($connection));
		$loc_id = $connection->insert_id;
	}

	echo "LAST: $last_loc - LOC: $loc_id \n";

	if ($last_loc != 0) {

		$sql = "SELECT ID FROM path WHERE loc_start=" . $last_loc . " AND loc_end=" . $loc_id;
		$rsItems = mysqli_query($connection, $sql) or die(mysqli_error($connection));

		if ($row_rsItems = mysqli_fetch_assoc($rsItems)) {
			// path already exists
		} else {
			$sql = "INSERT INTO path SET loc_start=" . $last_loc . ", loc_end=" . $loc_id;
			mysqli_query($connection, $sql) or die(mysqli_error($connection));
		}
	}

	$last_loc = $loc_id;
}

echo "DONE";

?>