<?php
    require_once("includes/dbconnect.php");
    session_start();// Zapneme session
    session_destroy();// Smažeme všechna session
    //clean captured events
    header("location: login.php"); // Přsesmeruje na přihlašovací stránku
?>
