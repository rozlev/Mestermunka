<?php
session_start();
session_unset();
session_destroy();
session_start(); // 🔥 Új session teljesen

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "palinka_mesterei");

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "❌ Adatbázis kapcsolat sikertelen: " . $conn->connect_error]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim(strtolower($_POST["email"]));
    $password = trim($_POST["password"]);

    $query = "SELECT UserID, Nev, Email, Jelszo, Szerepkor FROM user WHERE Email = ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        die(json_encode(["status" => "error", "message" => "❌ SQL előkészítési hiba: " . $conn->error]));
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        die(json_encode(["status" => "error", "message" => "❌ Nem létezik ilyen profil!"]));
    }

    if (!password_verify($password, $user["Jelszo"])) {
        die(json_encode(["status" => "error", "message" => "❌ Hibás jelszó!"]));
    }

    $_SESSION["user_id"] = $user["UserID"];
    $_SESSION["username"] = $user["Nev"];
    $_SESSION["role"] = ($user["Szerepkor"] === 'admin') ? "admin" : "user";

    echo json_encode([
        "status" => "success",
        "name" => $_SESSION["username"],
        "role" => $_SESSION["role"],
        "session_debug" => $_SESSION
    ]);
    exit;
}

$conn->close();
?>
