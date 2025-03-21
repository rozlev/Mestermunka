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
    $operation = intval($_POST['modify_stock']);
    
    $change *= $operation;
    $result = $conn->query("SELECT DB_szam FROM palinka WHERE PalinkaID = $palinka_id");
    $row = $result->fetch_assoc();
    $current_stock = intval($row['DB_szam']);
    $new_stock = $current_stock + $change;

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

$result = $conn->query("SELECT p.PalinkaID, p.Nev, p.AlkoholTartalom, p.Ar, p.DB_szam, k.KepURL 
                        FROM palinka p
                        LEFT JOIN kepek k ON p.PalinkaID = k.PalinkaID");
$palinkak = $result->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['make_admin'])) {
    $user_id = intval($_POST['user_id']);
    $stmt = $conn->prepare("UPDATE user SET Szerepkor = 'admin' WHERE UserID = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        header("Location: admin.php?user_updated=success");
        exit;
    } else {
        die("❌ Hiba történt az adminná állítás során: " . $stmt->error);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_admin'])) {
    $user_id = intval($_POST['user_id']);
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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    $nev = $_POST['nev'];
    $alkohol = $_POST['alkohol'];
    $ar = intval($_POST['ar']);
    $keszlet = intval($_POST['keszlet']);
    $kep = $_POST['kep'];

    $stmt = $conn->prepare("INSERT INTO palinka (Nev, AlkoholTartalom, Ar, DB_szam) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssii", $nev, $alkohol, $ar, $keszlet);

    if ($stmt->execute()) {
        $last_id = $stmt->insert_id;
        $stmt_kep = $conn->prepare("INSERT INTO kepek (PalinkaID, KepURL) VALUES (?, ?)");
        $stmt_kep->bind_param("is", $last_id, $kep);
        $stmt_kep->execute();
        
        header("Location: admin.php?add_success=1");
        exit;
    } else {
        die("❌ Hiba történt a hozzáadás során: " . $stmt->error);
    }
}

if (isset($_GET['delete'])) {
    $palinka_id = intval($_GET['delete']);
    $stmt_kep = $conn->prepare("DELETE FROM kepek WHERE PalinkaID = ?");
    $stmt_kep->bind_param("i", $palinka_id);
    $stmt_kep->execute();

    $stmt = $conn->prepare("DELETE FROM palinka WHERE PalinkaID = ?");
    $stmt->bind_param("i", $palinka_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        header("Location: admin.php?delete_success=1");
        exit;
    } else {
        die("❌ Hiba történt a törlés során: " . $stmt->error);
    }
}

$result_users = $conn->query("SELECT UserID, Nev, Email FROM user WHERE Szerepkor != 'admin'");
$felhasznalok = $result_users->fetch_all(MYSQLI_ASSOC);

$result_admins = $conn->query("SELECT UserID, Nev, Email, Szerepkor FROM user WHERE Szerepkor = 'admin'");
$adminok = $result_admins->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Kezelőfelület</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #9b1c31;
            font-family: Arial, sans-serif;
            color: white;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
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
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            margin: 5px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-delete {
            background-color: #e74c3c;
            color: white;
        }
        .btn-back {
            background-color: #3498db;
            color: white;
        }
        .btn-back:hover {
            background-color: #2980b9;
        }
        .button-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 20px;
            gap: 10px;
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
            font-size: 14px;
        }
        th {
            background-color: #9b1c31;
            color: white;
        }
        .table-wrapper {
            overflow-x: auto;
        }
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
            margin-top: 20px;
            width: 100%;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        .form-container input {
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-container button {
            width: 100%;
            padding: 12px;
            background-color: #9b1c31;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
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
            font-size: 14px;
            cursor: pointer;
            border-radius: 5px;
        }
        .stock-btn:hover {
            background-color: #7e1627;
        }
        .stock-input {
            width: 50px;
            text-align: center;
            padding: 5px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 90%;
            max-width: 400px;
            text-align: center;
        }
        .modal-btn {
            background-color: #e74c3c;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            margin: 5px;
        }
        .modal-btn:hover {
            background-color: #c0392b;
        }
        #uzenet {
            color: black;
        }

        /* Reszponzív média lekérdezések */
        @media (max-width: 768px) {
            .container {
                width: 95%;
                padding: 10px;
            }
            h1, h2 {
                font-size: 1.5em;
            }
            .btn {
                padding: 8px 12px;
                font-size: 12px;
            }
            th, td {
                padding: 8px;
                font-size: 12px;
            }
            .form-container input {
                font-size: 12px;
                padding: 8px;
            }
            .form-container button {
                font-size: 12px;
                padding: 10px;
            }
            .stock-input {
                width: 40px;
            }
        }

        @media (max-width: 480px) {
            h1, h2 {
                font-size: 1.2em;
            }
            .btn {
                padding: 6px 10px;
                font-size: 10px;
            }
            th, td {
                padding: 6px;
                font-size: 10px;
            }
            .form-container {
                padding: 15px;
            }
            .form-container input {
                font-size: 10px;
                padding: 6px;
            }
            .form-container button {
                font-size: 10px;
                padding: 8px;
            }
            .stock-btn {
                padding: 4px 8px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Admin Kezelőfelület</h1>
    <div class="button-container">
        <a class="btn btn-back" href="../index.php">🏠 Vissza a főoldalra</a>
        <a class="btn btn-delete" href="logout.php">Kijelentkezés</a>
    </div>

    <h2>Meglévő Adminok</h2>
    <div class="table-wrapper">
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
    </div>

    <?php if (isset($_GET['user_updated']) && $_GET['user_updated'] == "success"): ?>
        <p style="color: green; font-weight: bold;">✅ A felhasználó adminná lett állítva!</p>
    <?php endif; ?>
    <h2>Felhasználók adminná állítása</h2>
    <div class="table-wrapper">
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
</div>

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

    <h2>Meglévő Pálinkák</h2>
   <div class="table-wrapper">
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
                    <?= $p['DB_szam'] ?> db
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="palinka_id" value="<?= $p['PalinkaID'] ?>">
                        <input type="number" name="change" class="stock-input" min="1" value="1" required>
                        <button type="submit" name="modify_stock" value="1" class="stock-btn">+</button>
                        <button type="submit" name="modify_stock" value="-1" class="stock-btn">-</button>
                    </form>
                </td>
                <td><img src="<?= htmlspecialchars($p['KepURL']) ?>" width="50"></td>
                <td><button class="btn btn-delete" onclick="confirmDelete(<?= $p['PalinkaID'] ?>)">🗑️ Törlés</button></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<div id="myModal" class="modal">
    <div class="modal-content">
        <p id="uzenet">Biztosan törölni szeretnéd ezt a pálinkát?</p>
        <button id="confirmDelete" class="modal-btn">Igen</button>
        <button id="cancelDelete" class="modal-btn">Mégsem</button>
    </div>
</div>

<script>
    var modal = document.getElementById("myModal");
    var confirmBtn = document.getElementById("confirmDelete");
    var cancelBtn = document.getElementById("cancelDelete");
    var deleteId = null;

    function confirmDelete(id) {
        deleteId = id;
        modal.style.display = "block";
    }

    confirmBtn.onclick = function () {
        window.location.href = "admin.php?delete=" + deleteId;
    }

    cancelBtn.onclick = function () {
        modal.style.display = "none";
    }

    window.onclick = function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    }
</script>
</body>
</html>