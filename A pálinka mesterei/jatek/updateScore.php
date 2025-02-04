<?php
require 'db_connection.php'; // Adatbázis kapcsolat

header('Content-Type: application/json');

// PHP hibajelentések bekapcsolása
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['username']) || !isset($data['score'])) {
        echo json_encode(["status" => "error", "message" => "Hiányzó adatok"]);
        exit;
    }

    $username = $data['username'];
    $score = intval($data['score']);

    // Játékos ID lekérdezése
    $query = $conn->prepare("SELECT UserID FROM user WHERE Nev = ?");
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "A játékos nem található"]);
        exit;
    }

    $row = $result->fetch_assoc();
    $player_id = $row['UserID'];

    // Pontszám mentése vagy frissítése
    $insertQuery = $conn->prepare("
        INSERT INTO scores (player_id, points, date) 
        VALUES (?, ?, NOW()) 
        ON DUPLICATE KEY UPDATE points = GREATEST(points, VALUES(points))
    ");
    $insertQuery->bind_param("ii", $player_id, $score);

    if ($insertQuery->execute()) {
        echo json_encode(["status" => "success", "message" => "Pontszám mentve"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Hiba a pontszám mentésekor"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Érvénytelen kérés"]);
}
?>
