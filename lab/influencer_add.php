<?php


    include("includes/dbconnect.php");
    include("includes/functions.php");  


    $influencer_name = mysqli_real_escape_string($link,$_POST['influencer_name']);
    $influencer_name = strtolower($influencer_name); // convert to lowercase
    
    $influencer_url = mysqli_real_escape_string($link,$_POST['influencer_url']);
    $influencer_url = strtolower($influencer_url); // convert to lowercase

    $influencer_description = mysqli_real_escape_string($link,$_POST['influencer_description']);

    $influencer_image = mysqli_real_escape_string($link, $_POST['influencer_image']);


    $add_influencer = "INSERT INTO influencers (influencer_name, influencer_url) VALUES ('$influencer_name', '$influencer_url')";
    $result = mysqli_query($link, $add_influencer) or die("MySQLi ERROR: ".mysqli_error($link));