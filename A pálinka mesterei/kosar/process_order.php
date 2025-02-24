<?php
header('Content-Type: application/json');

session_start(); // Ha van bejelentkezési rendszered, ezt használd
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

if (!isset($_SESSION['user_id'])) {
    die(json_encode(["error" => "Bejelentkezés szükséges!"]));
}

$userID = $_SESSION['user_id']; // Bejelentkezett felhasználó azonosítója

if (!isset($data["cart"]) || empty($data["cart"])) {
    die(json_encode(["error" => "Üres rendelési lista!"]));
}

// Egyedi rendelés azonosító létrehozása
$orderGroupID = uniqid("ORDER_");

// Rendelések mentése és készlet frissítése
foreach ($data["cart"] as $item) {
    $name = $conn->real_escape_string($item["name"]);
    $quantity = intval($item["quantity"]);

    // Készlet ellenőrzése és ár lekérdezése
    $check_sql = "SELECT PalinkaID, DB_szam, Ar FROM palinka WHERE Nev = '$name'";
    $result = $conn->query($check_sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $palinkaID = $row["PalinkaID"];
        $currentStock = $row["DB_szam"];
        $price = $row["Ar"];
        
        if ($currentStock < $quantity) {
            echo json_encode(["error" => "Nincs elég készleten a(z) \"$name\" termékből! Csak $currentStock db elérhető."]);
            exit;
        }
    } else {
        echo json_encode(["error" => "A termék nem található az adatbázisban."]);
        exit;
    }

    // Készlet csökkentése
    $update_sql = "UPDATE palinka SET DB_szam = DB_szam - $quantity WHERE PalinkaID = $palinkaID";
    if (!$conn->query($update_sql)) {
        echo json_encode(["error" => "Hiba történt a készlet frissítése során: " . $conn->error]);
        exit;
    }

    // Rendelés mentése
    $totalPrice = $price * $quantity;
    $orderDate = date("Y-m-d");

    $insert_sql = "INSERT INTO rendeles (UserID, PalinkaID, Darab, ArTotal, RendelesDatum, RendelesCsoportID) 
                   VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("iiidss", $userID, $palinkaID, $quantity, $totalPrice, $orderDate, $orderGroupID);
    
    if (!$stmt->execute()) {
        echo json_encode(["error" => "Hiba történt a rendelés mentésekor: " . $stmt->error]);
        exit;
    }
}

echo json_encode(["success" => "Rendelés sikeresen leadva!", "orderGroupID" => $orderGroupID]);
$conn->close();
?>
