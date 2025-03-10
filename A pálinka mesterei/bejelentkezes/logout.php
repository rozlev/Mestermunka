<?php
session_start();
session_unset();
session_destroy();

// 🚀 Session cookie törlése teljesen!
setcookie(session_name(), '', time() - 42000, '/');
setcookie("PHPSESSID", "", time() - 3600, "/");

header("Location: ../kijel/mama.php");
exit;
?>
