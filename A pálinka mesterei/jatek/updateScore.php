<?php
require 'db_connection.php'; // Adatbázis kapcsolat betöltése

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['username']) || !isset($data['score'])) {
        echo json_encode(["status" => "error", "message" => "Hiányzó adatok"]);
        exit;
    }

    $username = $data['username'];
    $score = intval($data['score']);

    // Játékos ID lekérése
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

    // Megnézzük, van-e már rekordja a játékosnak a scores táblában
    $scoreQuery = $conn->prepare("SELECT points FROM scores WHERE player_id = ?");
    $scoreQuery->bind_param("i", $player_id);
    $scoreQuery->execute();
    $scoreResult = $scoreQuery->get_result();

    if ($scoreResult->num_rows > 0) {
        // Ha már van pontszám, csak akkor frissítjük, ha az új nagyobb
        $row = $scoreResult->fetch_assoc();
        $currentHighScore = intval($row['points']);

        if ($score > $currentHighScore) {
            $updateQuery = $conn->prepare("UPDATE scores SET points = ? WHERE player_id = ?");
            $updateQuery->bind_param("ii", $score, $player_id);
            if ($updateQuery->execute()) {
                echo json_encode(["status" => "success", "message" => "Pontszám frissítve"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Hiba a pontszám frissítésekor"]);
            }
        } else {
            echo json_encode(["status" => "success", "message" => "Pontszám nem változott"]);
        }
    } else {
        // Ha még nincs rekordja, akkor újként beszúrjuk
        $insertQuery = $conn->prepare("INSERT INTO scores (player_id, points) VALUES (?, ?)");
        $insertQuery->bind_param("ii", $player_id, $score);
        if ($insertQuery->execute()) {
            echo json_encode(["status" => "success", "message" => "Új pontszám mentve"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Hiba a pontszám mentésekor"]);
        }
    }
} else {
    echo json_encode(["status" => "error", "message" => "Érvénytelen kérés"]);
}
?>
