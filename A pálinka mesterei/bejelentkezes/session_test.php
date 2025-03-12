<?php
session_start();
echo "Felhasználó ID: " . ($_SESSION["user_id"] ?? "Nincs bejelentkezve") . "<br>";
echo "Szerepkör: " . ($_SESSION["role"] ?? "Nincs szerepkör beállítva") . "<br>";
?>
