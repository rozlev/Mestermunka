<?php
session_start();

// Ellenőrizzük, hogy be van-e jelentkezve és admin-e
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    die("🚫 Nincs jogosultságod az oldal megtekintésére!");
}

// Kapcsolódás az adatbázishoz
$conn = new mysqli("localhost", "root", "", "palinka_mesterei");

if ($conn->connect_error) {
    die("❌ Adatbázis kapcsolat hiba: " . $conn->connect_error);
}

// 🔥 Új admin hozzáadása
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $nev = $_POST['nev'];
    $email = $_POST['email'];
    $jelszo = password_hash($_POST['jelszo'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO user (Nev, Email, Jelszo, Szerepkor) VALUES (?, ?, ?, 'admin')");
    $stmt->bind_param("sss", $nev, $email, $jelszo);

    if ($stmt->execute()) {
        header("Location: admin.php?admin_added=success");
        exit;
    } else {
        die("❌ Hiba történt: " . $stmt->error);
    }
}

// 🔥 Betöltjük az adminokat az adatbázisból
$result_admins = $conn->query("SELECT UserID, Nev, Email, Szerepkor FROM user WHERE Szerepkor = 'admin'");
$adminok = $result_admins->fetch_all(MYSQLI_ASSOC);

// 🔥 Betöltjük a pálinkákat az adatbázisból
$result = $conn->query("SELECT p.PalinkaID, p.Nev, p.AlkoholTartalom, p.Ar, p.DB_szam, k.KepURL 
                        FROM palinka p
                        LEFT JOIN kepek k ON p.PalinkaID = k.PalinkaID");
$palinkak = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Kezelőfelület</title>
    <link rel="stylesheet" href="style.css"> <!-- A főoldal CSS fájlja -->
    <style>
        body {
            background-color: #9b1c31; /* Bordó háttér */
            font-family: Arial, sans-serif;
            color: white;
        }
        .container {
            width: 80%;
            margin: auto;
            background: #f8e7eb; /* Világos rózsaszín doboz */
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            color: black;
        }
        h1, h2 {
            color: #9b1c31;
        }
        .btn {
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            margin: 5px;
            border-radius: 5px;
        }
        .btn-delete {
            background-color: #e74c3c;
            color: white;
        }
        .btn-delete:hover {
            background-color: #c0392b;
        }
        .btn-add {
            background-color: #9b1c31;
            color: white;
        }
        .btn-add:hover {
            background-color: #7e1627;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            color: black;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #9b1c31;
            color: white;
        }
        input, button {
            padding: 10px;
            margin: 10px 0;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #9b1c31;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #7e1627;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Kezelőfelület</h1>
        <a class="btn btn-delete" href="logout.php">Kijelentkezés</a>

        <h2>Új Admin Hozzáadása</h2>
        <form method="POST">
            <input type="text" name="nev" placeholder="Admin neve" required>
            <input type="email" name="email" placeholder="Email cím" required>
            <input type="password" name="jelszo" placeholder="Jelszó" required>
            <button type="submit" name="add_admin" class="btn btn-add">Admin hozzáadása</button>
        </form>

        <h2>Meglévő Adminok</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Név</th>
                <th>Email</th>
                <th>Szerepkör</th>
            </tr>
            <?php foreach ($adminok as $admin): ?>
                <tr>
                    <td><?= $admin['UserID'] ?></td>
                    <td><?= $admin['Nev'] ?></td>
                    <td><?= $admin['Email'] ?></td>
                    <td><?= $admin['Szerepkor'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h2>Új pálinka hozzáadása</h2>
        <form method="POST">
            <input type="text" name="nev" placeholder="Pálinka neve" required>
            <input type="text" name="alkohol" placeholder="Alkohol %" required>
            <input type="number" name="ar" placeholder="Ár (HUF)" required>
            <input type="text" name="kep" placeholder="Kép URL" required>
            <input type="number" name="keszlet" placeholder="Készlet (db)" required>
            <button type="submit" name="add" class="btn btn-add">Hozzáadás</button>
        </form>

        <h2>Meglévő pálinkák</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Név</th>
                <th>Alkohol %</th>
                <th>Ár</th>
                <th>Készlet</th>
                <th>Kép</th>
                <th>Művelet</th>
            </tr>
            <?php foreach ($palinkak as $p): ?>
                <tr>
                    <td><?= $p['PalinkaID'] ?></td>
                    <td><?= $p['Nev'] ?></td>
                    <td><?= $p['AlkoholTartalom'] ?></td>
                    <td><?= $p['Ar'] ?> HUF</td>
                    <td><?= $p['DB_szam'] > 0 ? $p['DB_szam'] . ' db' : '🚫 Készlethiány!' ?></td>
                    <td><img src="<?= $p['KepURL'] ?>" width="50"></td>
                    <td><a class="btn btn-delete" href="admin.php?delete=<?= $p['PalinkaID'] ?>" onclick="return confirm('Biztosan törlöd ezt a pálinkát?')">🗑️ Törlés</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
