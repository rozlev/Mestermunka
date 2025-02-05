<?php
require 'db_connection.php'; // Az adatbázis kapcsolat betöltése

header('Content-Type: application/json');

// Lekérdezzük a legjobb 10 játékost
$query = "SELECT u.Nev AS username, s.points 
          FROM scores s 
          JOIN user u ON s.player_id = u.UserID 
          ORDER BY s.points DESC 
          LIMIT 10";

$result = $conn->query($query);
$leaderboard = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $leaderboard[] = $row;
    }
}

// JSON visszaküldése
echo json_encode($leaderboard);
?>
