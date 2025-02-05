<?php
session_start();

// Kapcsolódás az adatbázishoz
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "palinka_mesterei";


$conn = new mysqli($servername, $username, $password, $dbname,);

if ($conn->connect_error) {
    die("Kapcsolódás sikertelen: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM user WHERE Email = '$email'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['Jelszo'])) {
            // Sikeres bejelentkezés, session indítása
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['user_name'] = $user['Nev'];

            // Válasz küldése a frontend számára
            echo json_encode(["status" => "success", "name" => $user['Nev']]);
        } else {
            echo json_encode(["status" => "error", "message" => "Hibás jelszó!"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Nem található felhasználó ezzel az email címmel!"]);
    }
}

$conn->close();
?>
