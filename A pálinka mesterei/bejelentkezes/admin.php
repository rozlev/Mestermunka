<?php
session_start(); // Session indítása minden oldalon

// Ellenőrizzük, hogy be van-e jelentkezve és admin-e
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    die("🚫 Nincs jogosultságod az oldal megtekintésére!");
}

$json_file = 'palinkak.json';

// Betöltjük az adatokat a JSON fájlból
$palinkak = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : [];

// Új pálinka hozzáadása
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $uj_palinka = [
        'id' => uniqid(),
        'nev' => $_POST['nev'],
        'leiras' => $_POST['leiras'],
        'ar' => $_POST['ar'],
        'kep' => $_POST['kep'],
        'keszlet' => $_POST['keszlet']
    ];
    $palinkak[] = $uj_palinka;
    file_put_contents($json_file, json_encode($palinkak, JSON_PRETTY_PRINT));
    header("Location: admin.php");
    exit;
}

// Pálinka törlése
if (isset($_GET['delete'])) {
    $palinkak = array_filter($palinkak, fn($p) => $p['id'] !== $_GET['delete']);
    file_put_contents($json_file, json_encode(array_values($palinkak), JSON_PRETTY_PRINT));
    header("Location: admin.php");
    exit;
}
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
            <textarea name="leiras" placeholder="Leírás" required></textarea>
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
            <th>Leírás</th>
            <th>Ár</th>
            <th>Készlet</th>
            <th>Kép</th>
            <th>Művelet</th>
        </tr>
        <?php foreach ($palinkak as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= $p['nev'] ?></td>
                <td><?= $p['leiras'] ?></td>
                <td><?= $p['ar'] ?> HUF</td>
                <td><?= $p['keszlet'] > 0 ? $p['keszlet'] . ' db' : '🚫 Készlethiány!' ?></td>
                <td><img src="<?= $p['kep'] ?>" width="50"></td>
                <td><a href="admin.php?delete=<?= $p['id'] ?>">🗑️ Törlés</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
