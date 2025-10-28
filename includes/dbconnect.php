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
if (defined('IS_API_REQUEST') && IS_API_REQUEST) {
    if (!$link) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Database connection failed: ' . mysqli_connect_error()
        ]);
        exit;
    }
    return $link;
}

// For regular pages
if (!$link) {
    die("Database connection failed: " . mysqli_connect_error());
}