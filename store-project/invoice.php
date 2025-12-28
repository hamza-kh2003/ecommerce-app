<?php
require_once "Database.php";
session_start();

if (!isset($_SESSION["user"]["id"])) {
    header("Location: login.php");
    exit;
}

$conn = Database::getInstance();
$userId = (int)$_SESSION["user"]["id"];
$orderId = (int)($_GET["id"] ?? 0);

$stmt = $conn->prepare("
    SELECT id, total, status, phone, governorate, area, street, payment_method
    FROM orders
    WHERE id = ? AND user_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT oi.quantity, oi.unit_price, p.name
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
while ($row = $res->fetch_assoc()) {
    $items[] = $row;
}
$stmt->close();

$shipping = 2.00;
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Invoice</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
  <div class="card p-4">
    <h2 class="mb-3">Invoice</h2>

    <div class="mb-3">
      <div><strong>Order #</strong> <?= (int)$order["id"] ?></div>
      <div><strong>Status:</strong> <?= htmlspecialchars($order["status"]) ?></div>
      <div><strong>Payment:</strong> <?= htmlspecialchars($order["payment_method"]) ?></div>
    </div>

    <div class="mb-3">
      <h5>Delivery</h5>
      <div><?= htmlspecialchars($order["phone"]) ?></div>
      <div><?= htmlspecialchars($order["governorate"]) ?>, <?= htmlspecialchars($order["area"]) ?></div>
      <div><?= htmlspecialchars($order["street"]) ?></div>
    </div>

    <h5 class="mt-3">Items</h5>
    <table class="table">
      <thead>
        <tr>
          <th>Product</th>
          <th>Qty</th>
          <th>Unit</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $sub = 0.0;
        foreach ($items as $it):
          $line = (float)$it["unit_price"] * (int)$it["quantity"];
          $sub += $line;
        ?>
          <tr>
            <td><?= htmlspecialchars($it["name"]) ?></td>
            <td><?= (int)$it["quantity"] ?></td>
            <td>$<?= number_format((float)$it["unit_price"], 2) ?></td>
            <td>$<?= number_format($line, 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="text-end">
      <div>Subtotal: <strong>$<?= number_format($sub, 2) ?></strong></div>
      <div>Shipping: <strong>$<?= number_format($shipping, 2) ?></strong></div>
      <div class="fs-5 mt-2">Total: <strong>$<?= number_format((float)$order["total"], 2) ?></strong></div>
    </div>

    <div class="mt-4">
      <a href="index.php" class="btn btn-primary">Back to Shop</a>
    </div>
  </div>
</div>

</body>
</html>
