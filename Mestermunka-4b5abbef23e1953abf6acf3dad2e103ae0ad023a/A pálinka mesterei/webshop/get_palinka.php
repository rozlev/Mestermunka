<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "palinka_mesterei";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Kapcsolati hiba: " . $conn->connect_error]));
}

$sql = "SELECT p.Nev, p.AlkoholTartalom, p.Ar, p.DB_szam, k.KepURL 
        FROM palinka p
        LEFT JOIN kepek k ON p.PalinkaID = k.PalinkaID";

$result = $conn->query($sql);

$palinkak = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $palinkak[] = $row;
    }
} else {
    echo json_encode(["error" => "Nincsenek adatok az adatbázisban!"]);
    exit;
}

echo json_encode($palinkak);
$conn->close();
?>
