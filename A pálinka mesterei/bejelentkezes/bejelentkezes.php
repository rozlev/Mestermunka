<?php
session_start();
$conn = new mysqli("localhost", "root", "", "palinka_mesterei");

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Adatbázis kapcsolat sikertelen!"]));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Ellenőrizzük, hogy létezik-e az email cím az adatbázisban
    $query = "SELECT * FROM user WHERE Email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo json_encode(["status" => "error", "message" => "Nem létezik ilyen profil!"]);
        exit;
    }

    // Jelszó ellenőrzés
    if (!password_verify($password, $user["Jelszo"])) {
        echo json_encode(["status" => "error", "message" => "Hibás felhasználónév vagy jelszó!"]);
        exit;
    }

    // Sikeres bejelentkezés
    $_SESSION["user_id"] = $user["UserID"];
    $_SESSION["username"] = $user["Nev"];
    
    echo json_encode(["status" => "success", "name" => $user["Nev"]]);
}

$conn->close();
?>
