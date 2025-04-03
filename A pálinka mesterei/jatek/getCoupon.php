<?php
require_once __DIR__ . "/db_connection.php";

header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $data = json_decode(file_get_contents("php://input"), true);
    $username = $data['username'] ?? '';
    $scoreID = $data['scoreID'] ?? 0;

    if (empty($username)) {
        echo json_encode(["status" => "error", "message" => "Nincs felhasználónév megadva."]);
        exit;
    }

    if (empty($scoreID)) {
        echo json_encode(["status" => "error", "message" => "Nincs pontszám azonosító megadva."]);
        exit;
    }

    $conn = getDBConnection();
    if (!$conn) {
        throw new Exception("Nem sikerült csatlakozni az adatbázishoz.");
    }

    // Felhasználó azonosítása és admin státusz ellenőrzése
    $stmt = $conn->prepare("SELECT UserID, Szerepkor FROM user WHERE Nev = ?");
    if (!$stmt) {
        throw new Exception("SQL prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Felhasználó nem található."]);
        exit;
    }

    $user = $result->fetch_assoc();
    $userID = $user['UserID'];
    $role = $user['Szerepkor'];

    // Ha admin, nem adunk kupont
    if ($role === 'admin') {
        echo json_encode(["status" => "error", "message" => "Adminisztrátorként nem szerezhetsz kupont."]);
        exit;
    }

    // Pontszám ellenőrzése
    $stmt = $conn->prepare("SELECT points FROM scores WHERE score_id = ? AND player_id = ?");
    if (!$stmt) {
        throw new Exception("SQL prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ii", $scoreID, $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Pontszám nem található vagy nem a felhasználóhoz tartozik."]);
        exit;
    }

    $scoreData = $result->fetch_assoc();
    $points = $scoreData['points'];

    if ($points < 15) {
        echo json_encode(["status" => "error", "message" => "A pontszám nem jogosít kuponra (minimum 15 pont szükséges)."]);
        exit;
    }

    // Tranzakció indítása a konzisztencia érdekében
    $conn->begin_transaction();

    // Első szabad kupon lekérdezése (UserID = NULL)
    $stmt = $conn->prepare("SELECT KuponKod, KuponID FROM kuponok WHERE UserID IS NULL LIMIT 1 FOR UPDATE");
    if (!$stmt) {
        throw new Exception("SQL prepare failed: " . $conn->error);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $couponData = $result->fetch_assoc();
        $coupon = $couponData['KuponKod'];
        $kuponID = $couponData['KuponID'];

        // Kupon hozzárendelése a felhasználóhoz
        $stmt = $conn->prepare("UPDATE kuponok SET UserID = ? WHERE KuponID = ?");
        if (!$stmt) {
            throw new Exception("SQL prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ii", $userID, $kuponID);
        $stmt->execute();

        // Tranzakció véglegesítése
        $conn->commit();

        echo json_encode(["status" => "success", "coupon" => $coupon]);
    } else {
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => "Nincs elérhető kupon az adatbázisban."]);
        exit;
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    if (isset($conn) && $conn->in_transaction) {
        $conn->rollback();
    }
    error_log("Error in getCoupon.php: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Szerver hiba: " . $e->getMessage()]);
    exit;
}
?>