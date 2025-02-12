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

// JSON adatok beolvasása
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["cart"]) || empty($data["cart"])) {
    die(json_encode(["error" => "Üres rendelési lista!"]));
}

foreach ($data["cart"] as $item) {
    $name = $conn->real_escape_string($item["name"]);
    $quantity = intval($item["quantity"]);

    // Készlet ellenőrzése
    $check_sql = "SELECT DB_szam FROM palinka WHERE Nev = '$name'";
    $result = $conn->query($check_sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $currentStock = $row["DB_szam"];

        if ($currentStock < $quantity) {
            echo json_encode(["error" => "Nincs elég készleten a(z) \"$name\" termékből! Csak $currentStock db elérhető."]);
            exit;
        }
    } else {
        echo json_encode(["error" => "A termék nem található az adatbázisban."]);
        exit;
    }

    // Ha van elég készlet, levonjuk
    $update_sql = "UPDATE palinka SET DB_szam = DB_szam - $quantity WHERE Nev = '$name'";
    if (!$conn->query($update_sql)) {
        echo json_encode(["error" => "Hiba történt a frissítés során: " . $conn->error]);
        exit;
    }
}

echo json_encode(["success" => "Rendelés sikeresen leadva!"]);
$conn->close();
?>
