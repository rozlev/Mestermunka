<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Nincs bejelentkezve']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['score'])) {
        echo json_encode(['status' => 'error', 'message' => 'Hiányzó pontszám']);
        exit;
    }

    $score = intval($data['score']);
    $sql = "INSERT INTO scores (player_id, points) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $score);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Pontszám elmentve']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Hiba történt a mentéskor']);
    }
    
    $stmt->close();
}

$conn->close();
?>
