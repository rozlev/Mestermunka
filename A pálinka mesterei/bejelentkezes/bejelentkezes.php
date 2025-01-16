<?php
session_start();

// Kapcsolódás az adatbázishoz
$servername = "localhost"; // vagy a saját adatbázis szervered IP-je
$username = "root"; // adatbázis felhasználó neve
$password = ""; // adatbázis jelszó
$dbname = "palinka_mesterei"; // adatbázis neve

$conn = new mysqli($servername, $username, $password, $dbname);

// Ellenőrizzük a kapcsolatot
if ($conn->connect_error) {
    die("Kapcsolódás sikertelen: " . $conn->connect_error);
}

// Ellenőrizzük a POST adatokat
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Adatok lekérése a POST kérésből
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Ellenőrzés az adatbázisban
    $sql = "SELECT * FROM user WHERE Email = '$email'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['Jelszo'])) {
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['user_name'] = $user['Nev'];
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Hibás jelszó!"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Nem található felhasználó ezzel az email címmel!"]);
    }
}

$conn->close();
?>
