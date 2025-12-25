<?php
// Development settings - show all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Database connection configuration
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "root";
$dbname = "portfolio_prod";

$link = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

if (!$link) {
    die("Database connection failed: " . mysqli_connect_error());
}