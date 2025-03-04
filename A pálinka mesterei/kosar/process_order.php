<?php
header('Content-Type: application/json');
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "palinka_mesterei";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "Kapcsolati hiba: " . $conn->connect_error]));
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($_SESSION['user_id'])) {
    die(json_encode(["error" => "Bejelentkezés szükséges!"]));
}

$userID = $_SESSION['user_id'];

if (!isset($data["cart"]) || empty($data["cart"])) {
    die(json_encode(["error" => "Üres rendelési lista!"]));
}

// Lekérjük a felhasználó email címét
$get_user_email_query = "SELECT Email FROM user WHERE UserID = ?";
$get_user_email_stmt = $conn->prepare($get_user_email_query);
$get_user_email_stmt->bind_param("i", $userID);
$get_user_email_stmt->execute();
$get_user_email_stmt->bind_result($user_email);
$get_user_email_stmt->fetch();
$get_user_email_stmt->close();

if (!$user_email) {
    die(json_encode(["error" => "Hiba történt a felhasználó email címének lekérésekor."]));
}

// Egyedi rendelés azonosító létrehozása
$orderGroupID = uniqid("ORDER_");
$orderDetails = "";
$finalTotal = 0;

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

    // Összegyűjtjük a rendelési adatokat az emailhez
    $orderDetails .= "$name - $quantity db\n";
    $finalTotal += $totalPrice;
}

$conn->close();

// Email küldés a rendelésről
$api_key = "621ded9e-627c-45c1-8367-3477df11ce78"; // Web3Forms API kulcs
$post_fields = http_build_query([
    "access_key"    => $api_key,
    "subject"       => "Rendelés visszaigazolás - Pálinka Mesterei",
    "from name"     => "Pálinka Mesterei",
    "from email"    => "palinkamesterei@gmail.com", // 'from email' át lett nevezve
    "replyto"       => $user_email,
    "message"       => 
        "Kedves Vásárló!\n\n" .
        "Örömmel értesítünk, hogy a rendelésedet sikeresen rögzítettük. Kérjük, olvasd át az alábbi részleteket:\n\n" .
        
        "-------------------- Rendelési információk --------------------\n\n" .
        
        "RENDELÉSI AZONOSÍTÓ: #$orderGroupID\n\n" .
        
        "RENDELT TÉTELEK:\n" .
        "$orderDetails\n\n" .
        
        "-----------------------------------------------------------------\n\n" .
        
        "🛒 VÉGÖSSZEG: " . number_format($finalTotal, 0, ',', ' ') . " Ft\n\n" .
        
        "-----------------------------------------------------------------\n\n" .

        "A rendelésedet hamarosan feldolgozzuk, és értesíteni fogunk a kiszállítás pontos idejéről és részleteiről.\n\n" .

        "Amennyiben bárminemű kérdésed lenne, kérjük, ne habozz kapcsolatba lépni velünk. Segítünk mindenben!\n\n" .

        "------------------------------------------------------------\n\n" .
        
        "KÖSZÖNJÜK, HOGY MINKET VÁLASZTOTTÁL!\n\n" .
        
        "PÁLINKA MESTEREI csapata"
]);





$ch = curl_init("https://api.web3forms.com/submit");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
$response = curl_exec($ch);
curl_close($ch);

echo json_encode(["success" => "Rendelés sikeresen leadva!", "orderGroupID" => $orderGroupID]);
?>
