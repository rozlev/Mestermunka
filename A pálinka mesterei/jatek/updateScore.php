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

if ($role === 'admin') {
    error_log("[DEBUG] Admin bejelentkezett: $username");
    echo json_encode(["status" => "success", "message" => "Admin játék lejátszva, de a pont nem lett mentve.", "canPlay" => true]);
    exit;
}

// Ellenőrizzük, hogy a felhasználó ezen a héten már játszott-e
$stmt = $conn->prepare("SELECT date FROM scores WHERE player_id = ? ORDER BY date DESC LIMIT 1");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $lastPlayed = $result->fetch_assoc()['date'];
    $lastPlayedTime = strtotime($lastPlayed);
    $currentTime = time();
    $weekAgo = strtotime("-7 days", $currentTime);
    
    error_log("[DEBUG] Last played: $lastPlayed - Timestamp: $lastPlayedTime, Week Ago: $weekAgo, Current Time: $currentTime");
    
    if ($lastPlayedTime > $weekAgo) {
        error_log("[DEBUG] Játékos már játszott ezen a héten: $username");
        echo json_encode(["status" => "error", "message" => "Hetente csak egyszer játszhatsz!", "canPlay" => false]);
        exit;
    }
}

// Ha még nem játszott a héten, mentjük a pontszámát és engedjük játszani
$stmt = $conn->prepare("INSERT INTO scores (player_id, points, date) VALUES (?, ?, NOW())");
$stmt->bind_param("ii", $userID, $score);

if ($stmt->execute()) {
    error_log("[DEBUG] Pontszám mentve: $score - Játékos: $username");
    echo json_encode(["status" => "success", "message" => "Pontszám sikeresen mentve.", "canPlay" => true]);
} else {
    error_log("[DEBUG] Hiba történt a pont mentése közben!");
    echo json_encode(["status" => "error", "message" => "Hiba a pont mentése közben.", "canPlay" => false]);
}

$stmt->close();
$conn->close();
?>