<?php
session_start();

// Töröljük a munkamenet adatait
$_SESSION = array();

// Töröljük a munkamenet sütit
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Végül megsemmisítjük a munkamenetet
session_destroy();

// Átirányítunk az index.php-ra egy paraméterrel
header("Location: ../index.php?logout=true");
exit;
?>