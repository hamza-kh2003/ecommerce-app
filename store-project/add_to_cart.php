<?php
require_once "Database.php";
session_start();

$conn = Database::getInstance();

$productId = (int)($_POST["product_id"] ?? 0);
$qty = (int)($_POST["qty"] ?? 1);
if ($qty < 1) $qty = 1;

if ($productId <= 0) {
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare("SELECT id FROM products WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $productId);
$stmt->execute();
$stmt->store_result();
$exists = $stmt->num_rows > 0;
$stmt->close();

if (!$exists) {
    header("Location: index.php");
    exit;
}

$userId = (int)($_SESSION["user"]["id"] ?? 0);

if ($userId > 0) {
    $stmt = $conn->prepare("
        INSERT INTO cart (user_id, product_id, quantity)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
    ");
    $stmt->bind_param("iii", $userId, $productId, $qty);
    $stmt->execute();
    $stmt->close();
} else {
    if (!isset($_SESSION["cart"])) $_SESSION["cart"] = [];
    if (!isset($_SESSION["cart"][$productId])) $_SESSION["cart"][$productId] = 0;
    $_SESSION["cart"][$productId] += $qty;
}

header("Location:cart.php");
exit;
