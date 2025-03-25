<?php
require_once __DIR__ . "/db_connection.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? '';
$score = $data['score'] ?? 0;

if (empty($username)) {
    echo json_encode(["status" => "error", "message" => "Nincs felhasználónév megadva."]);
    exit;
}

$conn = getDBConnection();

// Lekérjük a felhasználó adatait
$stmt = $conn->prepare("SELECT UserID, Szerepkor FROM user WHERE Nev = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Felhasználó nem található."]);
    exit;
}

$user = $result->fetch_assoc();
$userID = $user['UserID'];
$role = $user['Szerepkor'];

// Admin pont nem menthető
if ($role === 'admin') {
    echo json_encode(["status" => "success", "message" => "Admin pont nem mentve."]);
    exit;
}

// Pont mentése normál felhasználóknak
$stmt = $conn->prepare("INSERT INTO scores (player_id, points, date) VALUES (?, ?, NOW())");
$stmt->bind_param("ii", $userID, $score);

if ($stmt->execute()) {
    $scoreID = $conn->insert_id; // Get the newly inserted score_id
    echo json_encode([
        "status" => "success",
        "message" => "Pontszám sikeresen mentve.",
        "scoreID" => $scoreID // Return the score_id
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Hiba a pont mentése közben."]);
}

$stmt->close();
$conn->close();
?>