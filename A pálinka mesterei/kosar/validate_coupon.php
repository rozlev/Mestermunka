<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "palinka_mesterei";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "Kapcsolati hiba: " . $conn->connect_error]));
}

$data = json_decode(file_get_contents("php://input"), true);
$couponCode = $conn->real_escape_string($data["couponCode"] ?? "");

if (empty($couponCode)) {
    echo json_encode(["error" => "Kérlek, add meg a kupon kódot!"]);
    exit;
}

$check_sql = "SELECT KuponID FROM kuponok WHERE KuponKod = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("s", $couponCode);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(["success" => true, "discountPercentage" => 20]);
} else {
    echo json_encode(["error" => "Érvénytelen kuponkód!"]);
}

$stmt->close();
$conn->close();
?>