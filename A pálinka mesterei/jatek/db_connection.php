<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "palinka_mesterei";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}


function getDBConnection() {
    $servername = "localhost";
    $username = "root";  // XAMPP esetén az alapértelmezett felhasználó "root"
    $password = "";       // XAMPP esetén nincs jelszó (hagyjuk üresen)
    $dbname = "palinka_mesterei";  // Az adatbázis neve

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Kapcsolódási hiba: " . $conn->connect_error);
    }

    return $conn;
}


?>
