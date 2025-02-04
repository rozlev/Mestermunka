<?php
session_start();
include 'db.php'; // Adatbázis kapcsolat

if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "Nincs bejelentkezve"]);
    exit();
}

$username = $_SESSION['username'];
$score = isset($_POST['score']) ? intval($_POST['score']) : 0;

if ($score > 0) {
    $stmt = $conn->prepare("INSERT INTO scores (username, score) VALUES (?, ?)");
    $stmt->bind_param("si", $username, $score);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Hiba az adatbázisban"]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Érvénytelen pontszám"]);
}
?>
