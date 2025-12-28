<?php
require_once "Database.php";
session_start();

$conn = Database::getInstance();

$productId = (int)($_POST["product_id"] ?? 0);
$qty = (int)($_POST["qty"] ?? 1);
if ($qty < 1) $qty = 1;

$userId = (int)($_SESSION["user"]["id"] ?? 0);

if ($productId <= 0) {
    header("Location: cart.php");
    exit;
}

if ($userId > 0) {
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("iii", $qty, $userId, $productId);
    $stmt->execute();
    $stmt->close();
} else {
    if (isset($_SESSION["cart"][$productId])) {
        $_SESSION["cart"][$productId] = $qty;
    }
}

header("Location: cart.php");
exit;
