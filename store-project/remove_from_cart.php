<?php
require_once "Database.php";
session_start();

$conn = Database::getInstance();

$productId = (int)($_POST["product_id"] ?? 0);
$userId = (int)($_SESSION["user"]["id"] ?? 0);

if ($productId <= 0) {
    header("Location: cart.php");
    exit;
}

if ($userId > 0) {
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $stmt->close();
} else {
    unset($_SESSION["cart"][$productId]);
}

header("Location: cart.php");
exit;
