<?php

$hostname = "localhost";
$database = "sparknav";
$username = "root";
$password = "root123";

$connection = new mysqli(
	$hostname,
	$username,
	$password,
	$database);

if ($connection->connect_errno) {
	echo "Failed to connect to MySQL: (" . $connection->connect_errno . ") "
	. $connection->connect_error;
	die();
}

mysqli_set_charset($connection, 'utf8');

?>
