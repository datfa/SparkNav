<?php

require_once 'connection.php';

$sql = "SELECT * FROM  path";
$rsItems = mysqli_query($connection, $sql) or die(mysqli_error($connection));

// Build array of data items
$arRows = array();

while ($row_rsItems = mysqli_fetch_assoc($rsItems)) {
	$myObj = new stdClass();
	//array_push ( $arRows, $row_rsItems );
	$myObj->x = $row_rsItems[start_x] + 0;
	$myObj->y = $row_rsItems[start_y] + 0;
	$myObj->name = $row_rsItems[name];

	//echo $myObj->x . " - " . $myObj->y . "\n";

	array_push($arRows, $myObj);

	// echo "MOVIE : $row_rsItems[id] => $row_rsItems[count]<BR>";
}

$myJSON = json_encode($arRows);
echo $myJSON;

// foreach ($arRows as &$value) {
// foreach ( $arRows as $key => $value ) {
// 	// echo "MOVIE : $value[id] => $values[count]<BR>";
// 	// echo "{$key} => {$value[id]} => {$value[count]} <br>";
// 	$percentage = ($value [count] * 100) / $total;
// 	$percentage = number_format ( $percentage, 2, '.', '' );

?>