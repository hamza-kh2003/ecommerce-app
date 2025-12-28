<?php
require_once "Database.php";
session_start();

if (!isset($_SESSION["user"]["id"])) {
    header("Location: login.php");
    exit;
}

$conn = Database::getInstance();
$userId = (int)$_SESSION["user"]["id"];

$stmt = $conn->prepare("
    SELECT c.quantity, p.price, p.discount_percent
    FROM cart c
    JOIN products p ON p.id = c.product_id
    WHERE c.user_id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();

$subtotal = 0.0;

while ($row = $res->fetch_assoc()) {
    $price = (float)$row["price"];
    $discount = (int)$row["discount_percent"];
    $final = $discount > 0 ? $price - ($price * $discount / 100) : $price;
    $subtotal += $final * (int)$row["quantity"];
}
$stmt->close();

if ($subtotal <= 0) {
    header("Location: cart.php");
    exit;
}

$shipping = 2.00;
$total = $subtotal + $shipping;

$errors = $_SESSION["checkout_errors"] ?? [];
unset($_SESSION["checkout_errors"]);
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Checkout</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
  
  <h2 class="mb-4">Checkout</h2>
  

  <?php if ($errors): ?>
    <div class="alert alert-danger"><?= htmlspecialchars(implode(" | ", $errors)) ?></div>
  <?php endif; ?>

  <div class="row g-4">
    
    <div class="col-md-7">
      <div class="card p-4">
         <div class="mt-4">
         <a href="index.php" class="btn btn-primary">Back to Shop</a>
           </div>
        <h5 class="mb-3 mt-3">Delivery Information</h5>

        <form action="place_order.php" method="post">
          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input name="phone" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Governorate</label>
            <input name="governorate" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Area</label>
            <input name="area" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Street</label>
            <input name="street" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Payment Method</label>
            <select name="payment_method" class="form-select" required>
              <option value="cash" selected>Cash</option>
            </select>
          </div>

          <button class="btn btn-primary w-100" type="submit">Place Order</button>
        </form>
      </div>
    </div>

    <div class="col-md-5">
      <div class="card p-4">
        <h5 class="mb-3">Order Summary</h5>

        <div class="d-flex justify-content-between mb-2">
          <span>Subtotal</span>
          <strong>$<?= number_format($subtotal, 2) ?></strong>
        </div>

        <div class="d-flex justify-content-between mb-2">
          <span>Shipping</span>
          <strong>$<?= number_format($shipping, 2) ?></strong>
        </div>

        <hr>

        <div class="d-flex justify-content-between">
          <span>Total</span>
          <strong>$<?= number_format($total, 2) ?></strong>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
