<?php
require_once "Database.php";
session_start();
$conn = Database::getInstance();

$cats = [];
$catRes = $conn->query("SELECT id, name FROM categories ORDER BY name");
while ($c = $catRes->fetch_assoc()) {
    $cats[] = $c;
}

$catId = (int)($_GET["cat"] ?? 0);


$search = trim($_GET["search"] ?? "");

$params = [];
$types = "";
$where = [];

$sql = "
    SELECT id, name, description, price, discount_percent, image
    FROM products
";

if ($search !== "") {
    $where[] = "(name LIKE ? OR description LIKE ?)";
    $like = "%" . $search . "%";
    $params[] = $like;
    $params[] = $like;
    $types .= "ss";
}

if ($catId > 0) {
    $where[] = "category_id = ?";
    $params[] = $catId;
    $types .= "i";
}

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

if (!$params) {
    $result = $conn->query($sql);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
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

  <main class="flex-fill">

    <!-- Spinner Start -->
    <div id="spinner"
        class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->


    <!-- Topbar Start -->
   <!-- <div class="container-fluid px-5 d-none border-bottom d-lg-block">
        <div class="row gx-0 align-items-center">
            <div class="col-lg-4 text-center text-lg-start mb-lg-0">
                <div class="d-inline-flex align-items-center" style="height: 45px;">
                    <a href="#" class="text-muted me-2"> Help</a><small> / </small>
                    <a href="#" class="text-muted mx-2"> Support</a><small> / </small>
                    <a href="#" class="text-muted ms-2"> Contact</a>
                </div>
            </div>
            <div class="col-lg-4 text-center d-flex align-items-center justify-content-center">
                <small class="text-dark">Call Us:</small>
                <a href="#" class="text-muted">(+012) 1234 567890</a>
            </div>

            <div class="col-lg-4 text-center text-lg-end">
                <div class="d-inline-flex align-items-center" style="height: 45px;">
                    <div class="dropdown">
                        <a href="#" class="dropdown-toggle text-muted me-2" data-bs-toggle="dropdown"><small>
                                USD</small></a>
                        <div class="dropdown-menu rounded">
                            <a href="#" class="dropdown-item"> Euro</a>
                            <a href="#" class="dropdown-item"> Dolar</a>
                        </div>
                    </div>
                    <div class="dropdown">
                        <a href="#" class="dropdown-toggle text-muted mx-2" data-bs-toggle="dropdown"><small>
                                English</small></a>
                        <div class="dropdown-menu rounded">
                            <a href="#" class="dropdown-item"> English</a>
                            <a href="#" class="dropdown-item"> Turkish</a>
                            <a href="#" class="dropdown-item"> Spanol</a>
                            <a href="#" class="dropdown-item"> Italiano</a>
                        </div>
                    </div>
                    <div class="dropdown">
                        <a href="#" class="dropdown-toggle text-muted ms-2" data-bs-toggle="dropdown"><small><i
                                    class="fa fa-home me-2"></i> My Dashboard</small></a>
                        <div class="dropdown-menu rounded">
                            <a href="#" class="dropdown-item"> Login</a>
                            <a href="#" class="dropdown-item"> Wishlist</a>
                            <a href="#" class="dropdown-item"> My Card</a>
                            <a href="#" class="dropdown-item"> Notifications</a>
                            <a href="#" class="dropdown-item"> Account Settings</a>
                            <a href="#" class="dropdown-item"> My Account</a>
                            <a href="#" class="dropdown-item"> Log Out</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>-->
     <!-- Topbar start -->
    <!--<div class="container-fluid px-5 py-4 d-none d-lg-block">
        <div class="row gx-0 align-items-center text-center">
            <div class="col-md-4 col-lg-3 text-center text-lg-start">
                <div class="d-inline-flex align-items-center">
                    <a href="" class="navbar-brand p-0">
                        <h1 class="display-5 text-primary m-0"><i
                                class="fas fa-shopping-bag text-secondary me-2"></i>Electro</h1>
                       <img src="img/logo.png" alt="Logo">
                    </a>
                </div>
            </div>
            <div class="col-md-4 col-lg-6 text-center">
                <div class="position-relative ps-4">
                    <div class="d-flex border rounded-pill">
                        <input class="form-control border-0 rounded-pill w-100 py-3" type="text"
                            data-bs-target="#dropdownToggle123" placeholder="Search Looking For?">
                        <select class="form-select text-dark border-0 border-start rounded-0 p-3" style="width: 200px;">
                            <option value="All Category">All Category</option>
                            <option value="Pest Control-2">Category 1</option>
                            <option value="Pest Control-3">Category 2</option>
                            <option value="Pest Control-4">Category 3</option>
                            <option value="Pest Control-5">Category 4</option>
                        </select>
                        <button type="button" class="btn btn-primary rounded-pill py-3 px-5" style="border: 0;"><i
                                class="fas fa-search"></i></button>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3 text-center text-lg-end">
                <div class="d-inline-flex align-items-center">
                    <a href="#" class="text-muted d-flex align-items-center justify-content-center me-3"><span
                            class="rounded-circle btn-md-square border"><i class="fas fa-random"></i></i></a>
                    <a href="#" class="text-muted d-flex align-items-center justify-content-center me-3"><span
                            class="rounded-circle btn-md-square border"><i class="fas fa-heart"></i></a>
                    <a href="#" class="text-muted d-flex align-items-center justify-content-center"><span
                            class="rounded-circle btn-md-square border"><i class="fas fa-shopping-cart"></i></span>
                        <span class="text-dark ms-2">$0.00</span></a>
                </div>
            </div>
        </div>
    </div> -->
    <!-- Topbar End -->

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
                            <a href="index.php" class="nav-item nav-link">Home</a>
                            <a href="#" class="nav-item nav-link active">All Products</a>
                            <a href="cart.php" class="nav-item nav-link ">Cart</a>
                            <a href="<?= isset($_SESSION["user"]["id"]) ? "logout_action.php" : "login.php" ?>" class="nav-item nav-link"><?= isset($_SESSION["user"]["id"])? "logout" : "login" ?></a>
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

                              
                           <!-- <div class="nav-item dropdown d-block d-lg-none mb-3">
                                <a href="#" class="nav-link" data-bs-toggle="dropdown"><span class="dropdown-toggle">All
                                        Category</span></a>
                                <div class="dropdown-menu m-0">
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
                        </div>
                        <a href="" class="btn btn-secondary rounded-pill py-2 px-4 px-lg-3 mb-3 mb-md-3 mb-lg-0"><i
                                class="fa fa-mobile-alt me-2"></i>0795717995</a>
                    </div>
                </nav>
            </div>
        </div>
    </div>
    <!-- Navbar & Hero End -->

     


    <!-- Shop Page Start -->
    <div class="container-fluid shop py-5" id="shop-cards">
        <div class="container py-5">
            <div class="row g-4">
            
                <div class="col-lg-12 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="rounded mb-4 position-relative">
                        <img src="img/product-banner-3.jpg" class="img-fluid rounded w-100" style="height: 250px;"
                            alt="Image">
                        <div class="position-absolute rounded d-flex flex-column align-items-center justify-content-center text-center"
                            style="width: 100%; height: 250px; top: 0; left: 0; background: rgba(242, 139, 0, 0.3);">
                           <h2 class="text-white"> Find the device that fits your needs</h2>
                        </div>
                    </div>
                    <div class="row g-4 pb-4">
                        <div class="col-xl-7">
                              <div class="mb-3 d-flex gap-2 flex-wrap">
  <a class="btn btn-sm <?= $catId===0 ? "btn-primary" : "btn-outline-primary" ?>"
     href="?<?= $search!=="" ? "search=" . urlencode($search) : "" ?>">
     All
  </a>

  <?php foreach ($cats as $c): ?>
    <a class="btn btn-sm <?= $catId===(int)$c["id"] ? "btn-primary" : "btn-outline-primary" ?>"
       href="?cat=<?= (int)$c["id"] ?><?= $search!=="" ? "&search=" . urlencode($search) : "" ?>">
      <?= htmlspecialchars($c["name"]) ?>
    </a>
  <?php endforeach; ?>
</div>

                                <form method="get" action="">
                                    <div class="input-group w-100 mx-auto d-flex">
                                        <input type="search" name="search" class="form-control p-3"
                                               placeholder="Search By Keywords...."
                                               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                                        <button class="input-group-text p-3" type="submit">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </div>
                                </form>
                        </div>
                    
                    </div>
                    <div class="tab-content">
                        <div id="tab-5" class="tab-pane fade show p-0 active">
                            <div class="row g-4 product">
                                  <?php while ($row = $result->fetch_assoc()): ?>

                                     <?php
                              $price    = (float)$row['price'];
                              $discount = (int)$row['discount_percent'];
                              $final    = $discount > 0 ? $price - ($price * $discount / 100) : $price;
                                 ?>
                           
                               <div class="col-lg-3 col-md-4 col-sm-6">
                                   <div class="border rounded p-3 h-100 text-center">
                           
                                       <img src="admin/<?= htmlspecialchars($row['image']) ?>"
                                            class="img-fluid mb-3"
                                            alt="<?= htmlspecialchars($row['name']) ?>">
                           
                                       <h6 class="mb-1">
                                           <?= htmlspecialchars($row['name']) ?>
                                       </h6>
                           
                                       <p class="text-muted small mb-2">
                                           <?= htmlspecialchars($row['description']) ?>
                                       </p>
                           
                                       <?php if ($discount > 0): ?>
                                           <del class="text-muted">
                                               $<?= number_format($price, 2) ?>
                                           </del>
                                       <?php endif; ?>
                           
                                       <p class="fw-bold text-primary mb-3">
                                           $<?= number_format($final, 2) ?>
                                       </p>
                           
                                       <!-- Add To Cart button  -->
                                                <form action="add_to_cart.php" method="post">
                                                <input type="hidden" name="product_id" value="<?= (int)$row['id'] ?>">
                                                <input type="hidden" name="qty" value="1">
                                                <button type="submit" class="btn btn-sm btn-primary w-100">Add To Cart</button>
                                              </form>

                                       </div>
                                   </div>
                               
                                              <?php endwhile; ?>
                                                            </div>

                               
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Shop Page End -->
     </main >

  

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