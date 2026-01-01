<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "includes/dbconnect.php";
include "includes/functions.php";
header("Cache-Control: max-age=0");


if(isset($_POST['login'])){
      $username=mysqli_real_escape_string($link, $_POST['username']);
      $password=mysqli_real_escape_string($link, $_POST['password']);
       
            
      $get_login="select * from users where login = '$username' and password = '$password'";
      $result = mysqli_query($link, $get_login) or die("MySQL ERROR: " . mysqli_error($link));
      echo $get_login;
      //$row = mysqli_fetch_array($db,$result);
      $overeni = mysqli_num_rows($result);
      echo "Pocet riadkov:".$overeni;
      
      if($overeni == 1) {
          $_SESSION['login'] = stripslashes($username);
          //$row = mysqli_fetch_array($result);
          echo "<div class='overlay'><div class='logon_information success'><i class='fa fa-check-circle'></i></div></div>"; 
          echo "<script>setTimeout(function(){
            window.location = 'index.php';
          }, 3000)</script>";

          //header("location:dashboard.php");
       } elseif ($overeni==0) {
            echo "<div class='overlay'><div class='logon_information error'><i class='fas fa-times-circle'></i></div></div>";
            echo "<script>setTimeout(function(){
              window.location = 'login.php';
            }, 3000)</script>";
            
          }
      }
 ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portforlio Tracker</title>
  <link rel="stylesheet" type="text/css" href="css/login.css?<?php echo time(); ?>">
  <link rel="stylesheet" type="text/css" href="css/style.css?<?php echo time(); ?>">
	<link href='https://fonts.googleapis.com/css?family=Noto+Sans:400,700,400italic,700italic' rel='stylesheet' type='text/css'>
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" rel="stylesheet">
  <script defer src="js/loin.js?<?php echo time() ?>"></script>
	<link rel="icon" type="image/png" sizes="32x32" href="investment.png">
</head>
<body>
  <main>
     <div class="login-page">
          <div class="form">
           <form class="login-form" action="" method="post">
              <input type="text" placeholder="username" name="username" autocomplete="off">
              <input type="password" placeholder="password" name="password" autocomplete="off" />
              <button name="login">login</button>
              </form>
          </div>
    </main>      
</body>
</html>