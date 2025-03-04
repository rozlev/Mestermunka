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


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modify_stock'])) {
    $palinka_id = intval($_POST['palinka_id']);
    $change = intval($_POST['change']);
    $operation = intval($_POST['modify_stock']); // Most már számként kezeljük
    
    // A change-t szorozzuk az operáció értékével (-1 vagy 1)
    $change *= $operation;

    // Jelenlegi készlet lekérdezése
    $result = $conn->query("SELECT DB_szam FROM palinka WHERE PalinkaID = $palinka_id");
    $row = $result->fetch_assoc();
    $current_stock = intval($row['DB_szam']);

    $new_stock = $current_stock + $change;

// Ha az új készlet 0-ra csökken, akkor nem hiba, csak figyelmeztetés
if ($new_stock < 0) {
    $new_stock = 0;
}


    $stmt = $conn->prepare("UPDATE palinka SET DB_szam = ? WHERE PalinkaID = ?");
    $stmt->bind_param("ii", $new_stock, $palinka_id);

    if ($stmt->execute()) {
        header("Location: admin.php?stock_updated=success");
        exit;
    } else {
        die("❌ Hiba történt a készlet módosítása során: " . $stmt->error);
    }
}

// 🔥 Pálinkák lekérése az adatbázisból
$result = $conn->query("SELECT p.PalinkaID, p.Nev, p.AlkoholTartalom, p.Ar, p.DB_szam, k.KepURL 
                        FROM palinka p
                        LEFT JOIN kepek k ON p.PalinkaID = k.PalinkaID");
$palinkak = $result->fetch_all(MYSQLI_ASSOC);




// 🔥 Felhasználó adminná állítása
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['make_admin'])) {
    $user_id = intval($_POST['user_id']);

    // Frissítjük az adott felhasználó szerepkörét adminná
    $stmt = $conn->prepare("UPDATE user SET Szerepkor = 'admin' WHERE UserID = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        header("Location: admin.php?user_updated=success");
        exit;
    } else {
        die("❌ Hiba történt az adminná állítás során: " . $stmt->error);
    }
}

// 🔥 Admin jogának visszavonása (admin -> felhasználó)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_admin'])) {
    $user_id = intval($_POST['user_id']);

    // Ne engedjük az aktuálisan bejelentkezett adminnak a saját jogának elvételét
    if ($user_id == $_SESSION["user_id"]) {
        die("❌ Nem veheted el a saját admin jogodat!");
    }

    $stmt = $conn->prepare("UPDATE user SET Szerepkor = 'felhasználó' WHERE UserID = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        header("Location: admin.php?admin_removed=success");
        exit;
    } else {
        die("❌ Hiba történt az admin jog elvétele során: " . $stmt->error);
    }
}

// 🔥 Új pálinka hozzáadása
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    $nev = $_POST['nev'];
    $alkohol = $_POST['alkohol'];
    $ar = intval($_POST['ar']);
    $keszlet = intval($_POST['keszlet']);
    $kep = $_POST['kep'];

    // Előkészített SQL beszúrás
    $stmt = $conn->prepare("INSERT INTO palinka (Nev, AlkoholTartalom, Ar, DB_szam) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssii", $nev, $alkohol, $ar, $keszlet);

    if ($stmt->execute()) {
        $last_id = $stmt->insert_id; // Az új pálinka ID-ja

        // Kép URL mentése a `kepek` táblába
        $stmt_kep = $conn->prepare("INSERT INTO kepek (PalinkaID, KepURL) VALUES (?, ?)");
        $stmt_kep->bind_param("is", $last_id, $kep);
        $stmt_kep->execute();

        
        header("Location: admin.php?add_success=1");
        exit;
    } else {
        die("❌ Hiba történt a hozzáadás során: " . $stmt->error);
    }
    
}





// 🔥 Meglévő felhasználók lekérése (csak azok, akik még NEM adminok)
$result_users = $conn->query("SELECT UserID, Nev, Email FROM user WHERE Szerepkor != 'admin'");
$felhasznalok = $result_users->fetch_all(MYSQLI_ASSOC);

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
            background-color: #9b1c31;
            font-family: Arial, sans-serif;
            color: white;
        }
        .container {
            width: 80%;
            margin: auto;
            background: #f8e7eb;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            color: black;
        }
        h1, h2 {
            color: #9b1c31;
            text-align: center;
        }
        .btn {
            padding: 12px 18px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 10px;
            border-radius: 8px;
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
            padding: 15px;
            text-align: center;
        }
        th {
            background-color: #9b1c31;
            color: white;
        }

        .btn {
            padding: 12px 18px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 10px;
            border-radius: 8px;
        }

        .form-container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
            margin-top: 20px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        .form-container input {
            width: 100%;
            margin-bottom: 15px;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        .form-container button {
            width: 100%;
            padding: 14px;
            background-color: #9b1c31;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        .form-container button:hover {
            background-color: #7e1627;
        }
                .stock-btn {
            background-color: #9b1c31;
            color: white;
            border: none;
            padding: 5px 10px;
            font-size: 18px;
            cursor: pointer;
            border-radius: 5px;
        }
        .stock-btn:hover {
            background-color: #7e1627;
        }
        .stock-input {
            width: 50px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
        <h1>Admin Kezelőfelület</h1>
        <a class="btn btn-delete" href="logout.php">Kijelentkezés</a>



        <h2>Meglévő Adminok</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Név</th>
                <th>Email</th>
                <th>Művelet</th>
            </tr>
            <?php foreach ($adminok as $admin): ?>
                <tr>
                    <td><?= htmlspecialchars($admin['UserID']) ?></td>
                    <td><?= htmlspecialchars($admin['Nev']) ?></td>
                    <td><?= htmlspecialchars($admin['Email']) ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($admin['UserID']) ?>">
                            <button type="submit" name="remove_admin" class="btn btn-delete">Admin jog elvétele</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>


        <h2>Felhasználók adminná állítása</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Név</th>
                <th>Email</th>
                <th>Művelet</th>
            </tr>
            <?php foreach ($felhasznalok as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['UserID']) ?></td>
                    <td><?= htmlspecialchars($user['Nev']) ?></td>
                    <td><?= htmlspecialchars($user['Email']) ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['UserID']) ?>">
                            <button type="submit" name="make_admin" class="btn btn-add">Adminná állítás</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
</div>

                <br>






        <div class="container">
        <h2>Új Pálinka Hozzáadása</h2>
        <div class="form-container">
            <form method="POST">
                <input type="text" name="nev" placeholder="Pálinka neve" required>
                <input type="text" name="alkohol" placeholder="Alkohol %" required>
                <input type="number" name="ar" placeholder="Ár (HUF)" required>
                <input type="text" name="kep" placeholder="Kép URL" required>
                <input type="number" name="keszlet" placeholder="Készlet (db)" required>
                <button type="submit" name="add">➕ Hozzáadás</button>
            </form>
            </div>

                <?php if (isset($_GET['stock_updated']) && $_GET['stock_updated'] == "success"): ?>
        <p style="color: green; font-weight: bold;">✅ A készlet frissítve!</p>
    <?php endif; ?>
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
                <td><?= htmlspecialchars($p['Nev']) ?></td>
                <td><?= htmlspecialchars($p['AlkoholTartalom']) ?></td>
                <td><?= $p['Ar'] ?> HUF</td>
                <td>
                    <form method="POST" style="display: flex; align-items: center;">
                        <input type="hidden" name="palinka_id" value="<?= $p['PalinkaID'] ?>">
                        <button type="submit" name="modify_stock" value="-1" class="stock-btn">➖</button>
                        <input type="number" name="change" class="stock-input" value="1" min="1">
                        <button type="submit" name="modify_stock" value="1" class="stock-btn">➕</button>
                    </form>
                    <p style="margin-top: 5px;"><?= $p['DB_szam'] ?> db</p>
                </td>
                <td><img src="<?= htmlspecialchars($p['KepURL']) ?>" width="50"></td>
                <td><a class="btn btn-delete" href="admin.php?delete=<?= $p['PalinkaID'] ?>" onclick="return confirm('Biztosan törlöd ezt a pálinkát?')">🗑️ Törlés</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
    </div>
</body>
</html>

