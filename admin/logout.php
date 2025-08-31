<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
session_unset(); 
session_destroy(); 

setcookie("admin_email", "", time() - 3600, "/"); // clear cookie

header("Location: login.php");
exit();
