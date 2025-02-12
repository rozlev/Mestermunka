<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "palinka_mesterei";

// Adatbázis kapcsolat
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Kapcsolati hiba: " . $conn->connect_error]));
}

// JSON adatok beolvasása
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["cart"]) || empty($data["cart"])) {
    die(json_encode(["error" => "Üres rendelési lista!"]));
}

foreach ($data["cart"] as $item) {
    $name = $conn->real_escape_string($item["name"]);
    $quantity = intval($item["quantity"]);

    $sql = "UPDATE palinka SET DB_szam = DB_szam - $quantity WHERE Nev = '$name' AND DB_szam >= $quantity";

    if (!$conn->query($sql)) {
        echo json_encode(["error" => "Hiba történt a frissítés során: " . $conn->error]);
        exit;
    }
}

echo json_encode(["success" => "Rendelés sikeresen leadva!"]);
$conn->close();
?>
