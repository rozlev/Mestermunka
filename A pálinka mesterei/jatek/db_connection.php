<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "palinka_mesterei";
$port = 3307;

$conn = new mysqli($servername, $username, $password, $database, $port);

if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}
?>
