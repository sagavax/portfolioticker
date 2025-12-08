<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$dbhost = "localhost";
$dbuser = "root";
$dbpass = "root";
$dbname = "portfolio_db";

echo "Trying to connect to database...<br>";
$link = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

if (!$link) {
    echo "Connection FAILED: " . mysqli_connect_error() . "<br>";
    echo "Error number: " . mysqli_connect_errno() . "<br>";
} else {
    echo "Connection SUCCESSFUL!<br>";
    echo "Database '$dbname' connected.<br>";
    mysqli_close($link);
}
?>