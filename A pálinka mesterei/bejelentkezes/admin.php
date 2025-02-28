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

if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);

    // 🔥 Először töröljük az adott pálinkához tartozó rendeléseket
    $stmt_rendeles = $conn->prepare("DELETE FROM rendeles WHERE PalinkaID = ?");
    $stmt_rendeles->bind_param("i", $delete_id);
    if (!$stmt_rendeles->execute()) {
        die("❌ Rendelési rekordok törlési hiba: " . $stmt_rendeles->error);
    }

    // 🔥 Ezután töröljük a képet a `kepek` táblából
    $stmt_kep = $conn->prepare("DELETE FROM kepek WHERE PalinkaID = ?");
    $stmt_kep->bind_param("i", $delete_id);
    if (!$stmt_kep->execute()) {
        die("❌ Kép törlési hiba: " . $stmt_kep->error);
    }

    // 🔥 Most töröljük a pálinkát a `palinka` táblából
    $stmt = $conn->prepare("DELETE FROM palinka WHERE PalinkaID = ?");
    $stmt->bind_param("i", $delete_id);
    if (!$stmt->execute()) {
        die("❌ Pálinka törlési hiba: " . $stmt->error);
    }

    header("Location: admin.php?deleted=success");
    exit;
}

// 🔥 Új pálinka hozzáadása
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $nev = $_POST['nev'];
    $alkohol = $_POST['alkohol'];
    $ar = $_POST['ar'];
    $keszlet = $_POST['keszlet'];
    $kep = $_POST['kep'];

    $stmt = $conn->prepare("INSERT INTO palinka (Nev, AlkoholTartalom, Ar, DB_szam) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdii", $nev, $alkohol, $ar, $keszlet);

    if ($stmt->execute()) {
        $palinkaID = $stmt->insert_id;

        $stmt_kep = $conn->prepare("INSERT INTO kepek (PalinkaID, KepURL) VALUES (?, ?)");
        $stmt_kep->bind_param("is", $palinkaID, $kep);
        $stmt_kep->execute();

        header("Location: admin.php?added=success");
        exit;
    } else {
        die("❌ Hiba történt: " . $stmt->error);
    }
}

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
    <title>Pálinka Admin</title>
</head>
<body>
    <h1>Pálinka Kezelőfelület</h1>
    <a href="logout.php">Kijelentkezés</a>

    <?php if ($_SESSION['role'] === 'admin'): ?>
        <h2>Új pálinka hozzáadása</h2>
        <form method="POST">
            <input type="text" name="nev" placeholder="Pálinka neve" required>
            <input type="text" name="alkohol" placeholder="Alkohol %" required>
            <input type="number" name="ar" placeholder="Ár (HUF)" required>
            <input type="text" name="kep" placeholder="Kép URL" required>
            <input type="number" name="keszlet" placeholder="Készlet (db)" required>
            <button type="submit" name="add">Hozzáadás</button>
        </form>
    <?php else: ?>
        <p>🚫 Csak az adminok adhatnak hozzá új pálinkát.</p>
    <?php endif; ?>

    <h2>Meglévő pálinkák</h2>
    <table border="1">
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
                <td><a href="admin.php?delete=<?= $p['PalinkaID'] ?>" onclick="return confirm('Biztosan törlöd ezt a pálinkát?')">🗑️ Törlés</a></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <?php if (isset($_GET['deleted'])): ?>
        <p style="color: green;">✅ Pálinka sikeresen törölve!</p>
    <?php endif; ?>

    <?php if (isset($_GET['added'])): ?>
        <p style="color: green;">✅ Pálinka sikeresen hozzáadva!</p>
    <?php endif; ?>
</body>
</html>
