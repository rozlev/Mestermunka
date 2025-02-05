<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "palinka_mesterei";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}
?>
