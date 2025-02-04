<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Nincs bejelentkezve']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Ellenőrizzük a POST adatokat
$data = json_decode(file_get_contents('php://input'), true);

// Debug kiírás: Mit kaptunk JSON-ban?
error_log("Received POST data: " . print_r($data, true));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ellenőrizzük, hogy a pontszám helyesen érkezett-e
    if (!isset($data['total_score']) || !is_numeric($data['total_score'])) {
        echo json_encode(['status' => 'error', 'message' => 'Hibás vagy hiányzó pontszám']);
        exit;
    }

    $score = intval($data['total_score']); // A kapott érték biztosan egész szám legyen
    error_log("Received score: " . $score); // Debug: Pontszám kiírása

    // SQL beszúrási művelet előkészítése
    $sql = "INSERT INTO scores (player_id, points) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        echo json_encode(['status' => 'error', 'message' => 'SQL előkészítési hiba: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("ii", $user_id, $score);

    // Végrehajtás és visszajelzés
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Pontszám elmentve']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Mentési hiba: ' . $stmt->error]);
    }
    
    $stmt->close();
}

$conn->close();
?>
