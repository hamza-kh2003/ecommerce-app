<?php
require_once "Database.php";
session_start();

if (!isset($_SESSION["user"]["id"])) {
    header("Location: login.php");
    exit;
}

$conn = Database::getInstance();
$userId = (int)$_SESSION["user"]["id"];

$orders = [];

$stmt = $conn->prepare("
    SELECT id, total, status, payment_method, governorate, area, street
    FROM orders
    WHERE user_id = ?
    ORDER BY id DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$r = $stmt->get_result();
while ($o = $r->fetch_assoc()) {
    $orders[] = $o;
}
$stmt->close();

$stmt = $conn->prepare("SELECT id, name, email, role, phone FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if (!$user) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

$profileErrors = $_SESSION["profile_errors"] ?? [];
$profileSuccess = $_SESSION["profile_success"] ?? "";
unset($_SESSION["profile_errors"], $_SESSION["profile_success"]);

$passErrors = $_SESSION["pass_errors"] ?? [];
$passSuccess = $_SESSION["pass_success"] ?? "";
unset($_SESSION["pass_errors"], $_SESSION["pass_success"]);
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

    <style>
    /* عام (آمن وما بكسر Bootstrap) */
* { box-sizing: border-box; }

body {
  margin: 0;
  background: #f4f6f8;
  color: #333;
  font-family: Arial, Helvetica, sans-serif;
}

/* صندوق صفحة الحساب فقط */
.account-box {
  max-width: 900px;
  margin: 40px auto;
  background: #fff;
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
}

/* كل شيء تحت محصور بالـ account-box */
.account-box h1 { margin-bottom: 10px; }

.account-box p {
  color: #666;
  font-size: 14px;
}

.account-box hr {
  border: none;
  border-top: 1px solid #ddd;
  margin: 25px 0;
}

/* صفوف الفورم داخل الحساب فقط (بدون كسر Bootstrap rows) */
.account-box .row {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
}

.account-box .row .field-group { flex: 1; }

/* عناصر الفورم داخل الحساب فقط */
.account-box label {
  display: block;
  font-size: 13px;
  margin-bottom: 6px;
  color: #555;
}

.account-box input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 14px;
}

.account-box input:focus {
  outline: none;
  border-color: #007bff;
}

/* أزرار وإجراءات داخل الحساب فقط */
.account-box .actions {
  display: flex;
  gap: 12px;
  margin-top: 20px;
}

.account-box button {
  padding: 10px 18px;
  border: none;
  border-radius: 4px;
  font-size: 14px;
  cursor: pointer;
}

/* ألوان الأزرار داخل الحساب */
.account-box .btn-save { background: #007bff; color: #fff; }
.account-box .btn-cancel { background: #6c757d; color: #fff; }
.account-box .btn-logout { background: #dc3545; color: #fff; margin-left: auto; }

/* رسائل النجاح/الخطأ داخل الحساب */
.account-box .msg {
  padding: 10px 12px;
  border-radius: 6px;
  font-size: 14px;
  margin: 12px 0;
}

.account-box .msg.err {
  background: #ffe3e5;
  color: #b02a37;
  border: 1px solid #f5c2c7;
}

.account-box .msg.ok {
  background: #d1e7dd;
  color: #0f5132;
  border: 1px solid #badbcc;
}

.account-box .small {
  font-size: 13px;
  color: #666;
}

/* موبايل */
@media (max-width: 700px) {
  .account-box .row { flex-direction: column; }
}

    </style>
  </head>

  <body>


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
                            <a href="./cart.php" class="nav-item nav-link ">Cart</a>
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
                                  <a href="myaccount.php" class="nav-item nav-link active">My Account</a>
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



    <div class="account-box">
      
   
      <h1>My Account</h1>
      <p>Manage your personal information and update your password.</p>
      <div class="small">
        Logged in as: <?= htmlspecialchars($user["email"]) ?> (<?= htmlspecialchars($user["role"]) ?>)
      </div>

      <?php if ($profileSuccess): ?>
        <div class="msg ok"><?= htmlspecialchars($profileSuccess) ?></div>
      <?php endif; ?>

      <?php if ($profileErrors): ?>
        <div class="msg err"><?= htmlspecialchars(implode(" | ", $profileErrors)) ?></div>
      <?php endif; ?>

      <form action="update_profile_action.php" method="post">
        <h3>Personal Information</h3>

        <div class="row">
          <div class="field-group">
            <label>Full Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user["name"] ?? "") ?>" />
          </div>

          <div class="field-group">
            <label>Phone Number</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($user["phone"] ?? "") ?>" />
          </div>
        </div>

        <div class="row">
          <div class="field-group">
            <label>Email Address</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user["email"] ?? "") ?>" />
          </div>
        </div>

        <div class="actions">
          <button type="submit" class="btn-save">Save Profile</button>
          <button type="reset" class="btn-cancel">Cancel</button>
          <button type="button" class="btn-logout" onclick="window.location.href='logout_action.php'">Logout</button>
        </div>
      </form>

      <hr />

      <?php if ($passSuccess): ?>
        <div class="msg ok"><?= htmlspecialchars($passSuccess) ?></div>
      <?php endif; ?>

      <?php if ($passErrors): ?>
        <div class="msg err"><?= htmlspecialchars(implode(" | ", $passErrors)) ?></div>
      <?php endif; ?>

      <form action="change_password_action.php" method="post">
        <h3>Change Password</h3>

        <div class="row">
          <div class="field-group">
            <label>Current Password</label>
            <input type="password" name="old_password" />
          </div>

          <div class="field-group">
            <label>New Password</label>
            <input type="password" name="new_password" />
          </div>
        </div>

        <div class="row">
          <div class="field-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" />
          </div>
        </div>

        <div class="actions">
          <button type="submit" class="btn-save">Change Password</button>
          <button type="reset" class="btn-cancel">Cancel</button>
        </div>
      </form>

<hr/>
      
<h3 >Your Orders</h3>

<?php if (!$orders): ?>
  <div class="msg err">No orders yet.</div>
<?php else: ?>
  <div class="d-flex flex-column gap-2">

    <?php foreach ($orders as $o): ?>
      <div class="border rounded p-3 bg-white">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <strong>Order #<?= (int)$o["id"] ?></strong>
            <div class="small">
                         <?php
$status = $o["status"];
$badge = "bg-secondary";
if ($status === "pending") $badge = "bg-warning text-dark";
if ($status === "completed") $badge = "bg-success";
if ($status === "cancelled") $badge = "bg-danger";
?>
Status: <span class="badge <?= $badge ?>"><?= htmlspecialchars($status) ?></span> |
  
              Payment: <strong><?= htmlspecialchars($o["payment_method"]) ?></strong>
            </div>
          </div>

          <div class="text-end">
            <div class="small">Total</div>
            <strong>$<?= number_format((float)$o["total"], 2) ?></strong>
          </div>
        </div>

        <div class="small mt-2">
          Delivery: <?= htmlspecialchars($o["governorate"]) ?>, <?= htmlspecialchars($o["area"]) ?>, <?= htmlspecialchars($o["street"]) ?>
        </div>
      </div>
    <?php endforeach; ?>

  </div>
<?php endif; ?>
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
