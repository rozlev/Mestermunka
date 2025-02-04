<?php
$servername = "localhost"; // Ha XAMPP-et használsz, akkor maradhat localhost
$username = "root"; 
$password = ""; 
$database = "palinka_mesterei"; // Az adatbázis neve


$conn = new mysqli($servername, $username, $password, $database);

// Kapcsolat ellenőrzése
if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}
?>
