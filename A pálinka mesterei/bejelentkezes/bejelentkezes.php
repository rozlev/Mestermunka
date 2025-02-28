<?php
session_start();
header('Content-Type: application/json'); // JSON formátum beállítása

$conn = new mysqli("localhost", "root", "", "palinka_mesterei");

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Adatbázis kapcsolat sikertelen!"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim(strtolower($_POST["email"]));
    $password = trim($_POST["password"]);

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

    if (!password_verify($password, $user["Jelszo"])) {
        echo json_encode(["status" => "error", "message" => "Hibás felhasználónév vagy jelszó!"]);
        exit;
    }

    $_SESSION["user_id"] = $user["UserID"];
    $_SESSION["username"] = $user["Nev"];
    $_SESSION["role"] = $user["Role"];

    echo json_encode(["status" => "success", "name" => $user["Nev"], "role" => $user["Role"]]);
    exit;
}

$conn->close();
?>