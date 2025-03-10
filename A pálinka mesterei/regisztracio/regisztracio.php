<?php
session_start();
$conn = new mysqli("localhost", "root", "", "palinka_mesterei");

if ($conn->connect_error) {
    die("Adatbázis kapcsolat sikertelen: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nev = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $jelszo = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $birthdate = $_POST["birthdate"];
    $regisztracio_datum = date("Y-m-d");

    // Életkor számítás
    $birthDateTime = new DateTime($birthdate);
    $today = new DateTime();
    $age = $today->diff($birthDateTime)->y;

    if ($age < 18) {
        echo json_encode(["error" => "Csak 18 éven felüliek regisztrálhatnak!"]);
        exit;
    }

    // Ellenőrzés, hogy a felhasználónév vagy e-mail már létezik-e
    $check_query = "SELECT * FROM user WHERE Nev = ? OR Email = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ss", $nev, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $existing = $result->fetch_assoc();
        if ($existing["Nev"] === $nev) {
            echo json_encode(["error" => "A felhasználónév már foglalt!"]);
            exit;
        }
        if ($existing["Email"] === $email) {
            echo json_encode(["error" => "Az e-mail cím már foglalt!"]);
            exit;
        }
    }

    // Sikeres regisztráció beszúrás az adatbázisba
    $insert_query = "INSERT INTO user (Nev, Email, Jelszo, RegisztracioDatum, Eletkor, Szerepkor) VALUES (?, ?, ?, ?, ?, 'felhasználó')";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ssssi", $nev, $email, $jelszo, $regisztracio_datum, $age);

    if ($stmt->execute()) {
        echo json_encode(["success" => "Sikeres regisztráció!"]);
    } else {
        echo json_encode(["error" => "Hiba történt a regisztráció során."]);
    }
}

$conn->close();

?>
