<?php
error_reporting(0);
date_default_timezone_set('Asia/Kolkata');

function connect_database() {
	$fetchType = "array";
	$dbHost = "localhost";
	$dbLogin = "Tarik7-353033376eab";
	$dbPwd = "Tarik@786";
	$dbName = "Tarik7-353033376eab";
	$con = mysqli_connect($dbHost, $dbLogin, $dbPwd, $dbName);
	if (!$con) {
		die("Database Connection failed: " . mysqli_connect_errno());
	}
	return ($con);
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'Tarik7-353033376eab');
define('DB_PASSWORD', 'Tarik@786');
define('DB_NAME', 'Tarik7-353033376eab');
?>