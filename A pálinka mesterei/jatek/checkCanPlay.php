<?php
require_once __DIR__ . "/db_connection.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? '';

if (empty($username)) {
    echo json_encode(["status" => "error", "message" => "Nincs felhasználónév megadva.", "canPlay" => false]);
    exit;
}

$conn = getDBConnection();

// Lekérjük a felhasználó adatait
$stmt = $conn->prepare("SELECT UserID, Szerepkor FROM user WHERE Nev = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Felhasználó nem található.", "canPlay" => false]);
    exit;
}

$user = $result->fetch_assoc();
$userID = $user['UserID'];
$role = $user['Szerepkor'];

// Admin mindig játszhat
if ($role === 'admin') {
    echo json_encode(["status" => "success", "message" => "Admin mindig játszhat.", "canPlay" => true, "isAdmin" => true]);
    exit;
}

// Ellenőrizzük, hogy a felhasználó ezen a héten már játszott-e
$stmt = $conn->prepare("SELECT date FROM scores WHERE player_id = ? ORDER BY date DESC LIMIT 1");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

$currentTime = time();
$weekInSeconds = 7 * 24 * 60 * 60; // 7 nap másodpercben

if ($result->num_rows > 0) {
    $lastPlayed = $result->fetch_assoc()['date'];
    $lastPlayedTime = strtotime($lastPlayed);
    
    // Számoljuk ki a következő lehetséges játék időpontját
    $nextPlayTime = $lastPlayedTime + $weekInSeconds;
    
    // Ha az aktuális idő kisebb mint a következő lehetséges időpont, még nem játszhat
    if ($currentTime < $nextPlayTime) {
        echo json_encode([
            "status" => "error",
            "message" => "Hetente csak egyszer játszhatsz!",
            "canPlay" => false,
            "isAdmin" => false,
            "lastPlayed" => $lastPlayed,
            "nextPlayTime" => date('Y-m-d H:i:s', $nextPlayTime)
        ]);
        exit;
    }
}

// Ha ide eljutunk, akkor a felhasználó játszhat (vagy még nem játszott, vagy lejárt a várakozási idő)
echo json_encode(["status" => "success", "message" => "Játszhatsz!", "canPlay" => true, "isAdmin" => false]);
$stmt->close();
$conn->close();
?>