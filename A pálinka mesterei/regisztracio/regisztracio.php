<?php
session_start();
$conn = new mysqli("localhost", "root", "", "palinka_mesterei");

if ($conn->connect_error) {
    die("Adatbázis kapcsolat sikertelen: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nev = trim($_POST["username"]); // Módosítás: Nev
    $email = trim($_POST["email"]); // Email helyesen
    $jelszo = password_hash($_POST["password"], PASSWORD_BCRYPT); // Jelszó hashelése
    $regisztracio_datum = date("Y-m-d"); // Automatikusan a mai dátum
    $eletkor = NULL; // Nem számolunk életkort most

    // Felhasználónév és e-mail ellenőrzése
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

    // Ha nincs ilyen felhasználó, beszúrás az adatbázisba
    $insert_query = "INSERT INTO user (Nev, Email, Jelszo, RegisztracioDatum, Eletkor, Szerepkor) VALUES (?, ?, ?, ?, ?, 'felhasználó')";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ssssi", $nev, $email, $jelszo, $regisztracio_datum, $eletkor);

    if ($stmt->execute()) {
        echo json_encode(["success" => "Sikeres regisztráció!"]);
    } else {
        echo json_encode(["error" => "Hiba történt a regisztráció során."]);
    }
}

$conn->close();
?>
