<?php
session_start(); // A munkamenet indítása
session_unset(); // Az összes munkameneti változó törlése
session_destroy(); // A munkamenet lezárása

header("Location: bejelentkezes/bejelentkezes.html"); // Átirányítás a bejelentkezési oldalra
exit();
?>
