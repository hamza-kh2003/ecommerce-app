<?php
require_once "Database.php";
session_start();

if (!isset($_SESSION["user"]["id"])) {
    header("Location: login.php");
    exit;
}

$conn = Database::getInstance();
$userId = (int)$_SESSION["user"]["id"];

$phone = trim($_POST["phone"] ?? "");
$governorate = trim($_POST["governorate"] ?? "");
$area = trim($_POST["area"] ?? "");
$street = trim($_POST["street"] ?? "");
$payment = trim($_POST["payment_method"] ?? "cash");

$errors = [];
if ($phone === "") $errors[] = "Phone required";
if ($governorate === "") $errors[] = "Governorate required";
if ($area === "") $errors[] = "Area required";
if ($street === "") $errors[] = "Street required";
if ($payment !== "cash") $errors[] = "Only cash is available now";

if ($errors) {
    $_SESSION["checkout_errors"] = $errors;
    header("Location: checkout.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT c.product_id, c.quantity, p.price, p.discount_percent
    FROM cart c
    JOIN products p ON p.id = c.product_id
    WHERE c.user_id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();

$cartItems = [];
$subtotal = 0.0;

while ($row = $res->fetch_assoc()) {
    $price = (float)$row["price"];
    $discount = (int)$row["discount_percent"];
    $final = $discount > 0 ? $price - ($price * $discount / 100) : $price;

    $qty = (int)$row["quantity"];
    $line = $final * $qty;
    $subtotal += $line;

    $cartItems[] = [
        "product_id" => (int)$row["product_id"],
        "qty" => $qty,
        "unit_price" => $final
    ];
}
$stmt->close();

if (!$cartItems) {
    header("Location: cart.php");
    exit;
}

$shipping = 2.00;
$total = $subtotal + $shipping;

$conn->begin_transaction();

try {
    $status = "pending";

    $stmt = $conn->prepare("
        INSERT INTO orders (user_id, total, status, phone, governorate, area, street, payment_method)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("idssssss", $userId, $total, $status, $phone, $governorate, $area, $street, $payment);
    $stmt->execute();
    $orderId = $stmt->insert_id;
    $stmt->close();

    $stmt = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, unit_price)
        VALUES (?, ?, ?, ?)
    ");

    foreach ($cartItems as $it) {
        $pid = $it["product_id"];
        $qty = $it["qty"];
        $unit = $it["unit_price"];
        $stmt->bind_param("iiid", $orderId, $pid, $qty, $unit);
        $stmt->execute();
    }
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    header("Location: invoice.php?id=" . $orderId);
    exit;

} catch (Throwable $e) {
    $conn->rollback();
    $_SESSION["checkout_errors"] = ["Order failed"];
    header("Location: checkout.php");
    exit;
}
