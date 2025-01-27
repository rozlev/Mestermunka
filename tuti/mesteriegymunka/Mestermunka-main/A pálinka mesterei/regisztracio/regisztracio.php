<?php
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Adatok lekérése a POST kérésből
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Jelszó hashelése
    $birthdate = $_POST['birthdate'];

    // Ellenőrizzük, hogy az email már létezik
    $sql = "SELECT * FROM user WHERE Email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Ha létezik már az e-mail, hibaüzenet
        echo "Ez az e-mail már regisztrálva van.";
    } else {
        // Felhasználó hozzáadása az adatbázishoz
        $sql = "INSERT INTO user (Nev, Email, Jelszo, RegisztracioDatum, Eletkor, Szerepkor) 
                VALUES ('$username', '$email', '$password', CURDATE(), TIMESTAMPDIFF(YEAR, '$birthdate', CURDATE()), 'felhasználó')";

        if ($conn->query($sql) === TRUE) {
            // Ha sikeres volt az adatbázis művelet, akkor átirányítjuk a bejelentkezés oldalra
            header("Location: ../bejelentkezes/bejelentkezes.html");
            exit(); // Fontos, hogy kilépjünk a scriptből, hogy az átirányítás érvényesüljön
        } else {
            // Ha hiba történik, kiírjuk a hibát
            echo "Hiba történt a regisztráció során: " . $conn->error;
        }
    }
}

$conn->close();
?>
