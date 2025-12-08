<?php
// Prevent displaying errors directly
error_reporting(0);
ini_set('display_errors', 0);

// Database connection configuration
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "root";
$dbname = "portfolio_db";

// Try to connect to the database
$link = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

// For API endpoints
//For regular pages
if (!$link) {
    die("Database connection failed: " . mysqli_connect_error());
}