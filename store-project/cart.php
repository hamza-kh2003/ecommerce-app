<?php
require_once "Database.php";
session_start();

$conn = Database::getInstance();

$userId = (int)($_SESSION["user"]["id"] ?? 0);
$items = [];
$subtotal = 0.0;

if ($userId > 0) {
    $stmt = $conn->prepare("
        SELECT 
            c.product_id,
            c.quantity,
            p.name,
            p.price,
            p.discount_percent,
            p.image
        FROM cart c
        JOIN products p ON p.id = c.product_id
        WHERE c.user_id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $price = (float)$row["price"];
        $discount = (int)$row["discount_percent"];
        $final = $discount > 0 ? $price - ($price * $discount / 100) : $price;
        $lineTotal = $final * (int)$row["quantity"];
        $subtotal += $lineTotal;

        $items[] = [
            "product_id" => (int)$row["product_id"],
            "name" => $row["name"],
            "image" => $row["image"],
            "qty" => (int)$row["quantity"],
            "final_price" => $final,
            "line_total" => $lineTotal
        ];
    }
    $stmt->close();
} else {
    $sessionCart = $_SESSION["cart"] ?? [];
    $productIds = array_keys($sessionCart);

    if ($productIds) {
        $placeholders = implode(",", array_fill(0, count($productIds), "?"));
        $types = str_repeat("i", count($productIds));

        $sql = "
            SELECT id, name, price, discount_percent, image
            FROM products
            WHERE id IN ($placeholders)
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$productIds);
        $stmt->execute();
        $res = $stmt->get_result();

        $map = [];
        while ($p = $res->fetch_assoc()) {
            $map[(int)$p["id"]] = $p;
        }
        $stmt->close();

        foreach ($sessionCart as $pid => $qty) {
            $pid = (int)$pid;
            $qty = (int)$qty;
            if (!isset($map[$pid])) continue;

            $price = (float)$map[$pid]["price"];
            $discount = (int)$map[$pid]["discount_percent"];
            $final = $discount > 0 ? $price - ($price * $discount / 100) : $price;
            $lineTotal = $final * $qty;
            $subtotal += $lineTotal;

            $items[] = [
                "product_id" => $pid,
                "name" => $map[$pid]["name"],
                "image" => $map[$pid]["image"],
                "qty" => $qty,
                "final_price" => $final,
                "line_total" => $lineTotal
            ];
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Digiline</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap"
        rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">


    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100">
     
     <div class="flex-grow-1">
    <!-- Spinner Start -->
    <div id="spinner"
        class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->


      <!-- Navbar & Hero Start -->
    <div class="container-fluid nav-bar p-0">
        <div class="row gx-0 bg-primary px-5 align-items-center">
            <div class="col-lg-3 d-none d-lg-block">
                <nav class="navbar navbar-light position-relative" style="width: 250px;">
                    <button class="navbar-toggler border-0 fs-4 w-100 px-0 text-start" type="button"
                        data-bs-toggle="collapse" data-bs-target="#allCat">
                      <!--<h4 class="m-0"><i class="fa fa-bars me-2"></i>All Categories</h4>--> 
                 <h1 class="display-5 text-secondary m-0" ><i class="fas fa-shopping-bag text-white me-2" ></i>Digiline</h1>
                    </button>
                     <!-- <div class="collapse navbar-collapse rounded-bottom" id="allCat">
                        <div class="navbar-nav ms-auto py-0">
                            <ul class="list-unstyled categories-bars">
                                <li>
                                    <div class="categories-bars-item">
                                        <a href="#">Accessories</a>
                                        <span>(3)</span>
                                    </div>
                                </li>
                                <li>
                                    <div class="categories-bars-item">
                                        <a href="#">Electronics & Computer</a>
                                        <span>(5)</span>
                                    </div>
                                </li>
                                <li>
                                    <div class="categories-bars-item">
                                        <a href="#">Laptops & Desktops</a>
                                        <span>(2)</span>
                                    </div>
                                </li>
                                <li>
                                    <div class="categories-bars-item">
                                        <a href="#">Mobiles & Tablets</a>
                                        <span>(8)</span>
                                    </div>
                                </li>
                                <li>
                                    <div class="categories-bars-item">
                                        <a href="#">SmartPhone & Smart TV</a>
                                        <span>(5)</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>-->
                </nav>
            </div>
            <div class="col-12 col-lg-9">
                <nav class="navbar navbar-expand-lg navbar-light bg-primary ">
                    <a href="" class="navbar-brand d-block d-lg-none">
                        <h1 class="display-5 text-secondary m-0"><i
                                class="fas fa-shopping-bag text-white me-2"></i>Digiline</h1>
                        <!-- <img src="img/logo.png" alt="Logo"> -->
                    </a>
                    <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarCollapse">
                        <span class="fa fa-bars fa-1x"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarCollapse">
                        <div class="navbar-nav ms-auto py-0">
                            <a href="./index.php" class="nav-item nav-link">Home</a>
                             <a href="./allproducts.php" class="nav-item nav-link">All Products</a>
                            <a href="#" class="nav-item nav-link active">Cart</a>
                            <a href="<?= isset($_SESSION["user"]["id"]) ? "logout_action.php" : "login.php" ?>" class="nav-item nav-link"><?= isset($_SESSION["user"]["id"]) ? "logout" : "login" ?></a>
                           <!--  <div class="nav-item dropdown">
                                <a href="#" class="nav-link" data-bs-toggle="dropdown"><span
                                        class="dropdown-toggle">Pages</span></a>
                                <div class="dropdown-menu m-0">
                                    <a href="bestseller.html" class="dropdown-item">Bestseller</a>
                                    <a href="cart.html" class="dropdown-item">Cart Page</a>
                                    <a href="cheackout.html" class="dropdown-item">Cheackout</a>
                                    <a href="404.html" class="dropdown-item">404 Page</a>
                                </div>
                            </div>-->
                                <?php if (isset($_SESSION["user"]["id"])): ?>
                                  <a href="myaccount.php" class="nav-item nav-link">My Account</a>
                              <?php endif; ?>

                                <?php if (isset($_SESSION["user"]["id"]) && $_SESSION["user"]["role"] === "admin"): ?>
                              <a href="admin/products_list.php" class="nav-item nav-link">Admin Panel</a>
                              <?php endif; ?>
                              
                      
                        </div>
                        <a href="" class="btn btn-secondary rounded-pill py-2 px-4 px-lg-3 mb-3 mb-md-3 mb-lg-0"><i
                                class="fa fa-mobile-alt me-2"></i> +0123 456 7890</a>
                    </div>
                </nav>
            </div>
        </div>
    </div>
    <!-- Navbar & Hero End -->
   

    <!-- Cart Page Start -->
    <div class="container-fluid py-5">
       
<div class="container">
  <h2 class="mb-4">Your Cart</h2>

  <?php if (!$items): ?>
    <div class="alert alert-info">Cart is empty</div>
    <a class="btn btn-primary" href="index.php">Back to shop</a>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>Product</th>
            <th>Price</th>
            <th style="width:140px;">Qty</th>
            <th>Total</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $it): ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-3">
                  <img src="admin/<?= htmlspecialchars($it["image"]) ?>" style="width:60px;height:60px;object-fit:cover">
                  <div><?= htmlspecialchars($it["name"]) ?></div>
                </div>
              </td>
              <td>$<?= number_format($it["final_price"], 2) ?></td>
              <td>
                <form action="update_cart.php" method="post" class="d-flex gap-2">
                  <input type="hidden" name="product_id" value="<?= (int)$it["product_id"] ?>">
                  <input type="number" min="1" name="qty" class="form-control" value="<?= (int)$it["qty"] ?>">
                  <button class="btn btn-sm btn-success" type="submit">Update</button>
                </form>
              </td>
              <td>$<?= number_format($it["line_total"], 2) ?></td>
              <td>
                <form action="remove_from_cart.php" method="post">
                  <input type="hidden" name="product_id" value="<?= (int)$it["product_id"] ?>">
                  <button class="btn btn-sm btn-danger" type="submit">Remove</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4">
      <a class="btn btn-outline-primary" href="allproducts.php">Continue shopping</a>
    </div>

    <?php
$shipping = 2.00;
$total = $subtotal + $shipping;
?>

<div class="row mt-4 justify-content-end">
  <div class="col-md-5">
    <div class="border rounded p-4 bg-light">
      <h4 class="mb-3">Cart Total</h4>

      <div class="d-flex justify-content-between mb-2">
        <span>Subtotal</span>
        <strong>$<?= number_format($subtotal, 2) ?></strong>
      </div>

      <div class="d-flex justify-content-between mb-2">
        <span>Shipping</span>
        <strong>$<?= number_format($shipping, 2) ?></strong>
      </div>

      <hr>

      <div class="d-flex justify-content-between mb-3">
        <span>Total</span>
        <strong>$<?= number_format($total, 2) ?></strong>
      </div>

      <?php if (!$items): ?>
        <button class="btn btn-primary w-100" disabled>Checkout</button>
      <?php else: ?>
        <?php if ($userId > 0): ?>
          <a class="btn btn-primary w-100" href="checkout.php">Proceed Checkout</a>
        <?php else: ?>
          <a class="btn btn-primary w-100" href="login.php">Login to Checkout</a>
        <?php endif; ?>
      <?php endif; ?>

    </div>
  </div>
</div>


    <?php if ($userId <= 0): ?>
      <div class="alert alert-warning mt-4">
        You are not logged in. Your cart is saved temporarily.
        <a href="login.php">Login</a> to save it to your account.
      </div>
    <?php endif; ?>

  <?php endif; ?>
</div>
    </div>
    <!-- Cart Page End -->

    </div>
<!-- Copyright Start -->
   <div class="container-fluid copyright py-4">
  <div class="container">
    <div class="row align-items-center">


      <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
        <span class="text-white">
          <i class="fas fa-copyright me-2"></i>
          Digiline, All rights reserved.
        </span>
      </div>

     
      <div class="col-md-6 text-center text-md-end">
        <a href="#" class="text-white me-3 fs-5">
          <i class="fab fa-facebook-f"></i>
        </a>
        <a href="#" class="text-white me-3 fs-5">
          <i class="fab fa-instagram"></i>
        </a>
        <a href="#" class="text-white me-3 fs-5">
          <i class="fab fa-twitter"></i>
        </a>
        <a href="#" class="text-white fs-5">
          <i class="fab fa-whatsapp"></i>
        </a>
      </div>

    </div>
  </div>
</div>
    <!-- Copyright End -->


    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>


    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>

</html>