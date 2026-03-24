<?php

session_start();
if (isset($_SESSION['logged']) && $_SESSION['logged'] === true) {

    header('Location: dashboard.php');
} else {
  
    header('Location: login.php');
}
exit;
