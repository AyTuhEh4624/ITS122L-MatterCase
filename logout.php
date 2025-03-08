<?php
   session_start();
   unset($_SESSION["username"]);
   unset($_SESSION["password"]);
   
   echo 'You logged out';
   header('Refresh: 1; URL = login.php');
?>