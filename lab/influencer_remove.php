<?php

    include("includes/dbconnect.php");
    include("includes/functions.php");


    $influencer_id = $_POST['influencer_id'];

    $remove_influencer = "DELETE FROM influencers WHERE influencer_id = $influencer_id";
    $result = mysqli_query($link, $remove_influencer) or die("MySQLi ERROR: ".mysqli_error($link));

?>