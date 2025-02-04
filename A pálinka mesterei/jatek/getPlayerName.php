<?php
session_start();

if (isset($_SESSION['user_id'], $_SESSION['user_name'])) {
    echo json_encode(["status" => "success", "username" => $_SESSION['user_name']]);
} else {
    echo json_encode(["status" => "error", "message" => "Nincs bejelentkezve"]);
}
?>
