<?php

session_start();

if (!isset($_SESSION["user"]["id"])||$_SESSION["user"]["role"]!=="admin") {
    header("Location:../index.php");
    exit;
}


require_once "../Database.php";
$conn = Database::getInstance();


$categories = $conn->query("SELECT * FROM categories");

  
$success = false;

if (isset($_POST['save'])) {


    $name        = $_POST['name'];
    $description = $_POST['description'];
    $price       = $_POST['price'];
    $discount    = $_POST['discount'];
    $category_id = $_POST['category_id'];


    $imageName = $_FILES['image']['name'];
    $imageTmp  = $_FILES['image']['tmp_name'];


    $imagePath = $imageName;
    move_uploaded_file($imageTmp, $imagePath);

    
    $sql = "INSERT INTO products 
            (category_id, name, description, price, discount_percent, image)
            VALUES 
            ('$category_id', '$name', '$description', '$price', '$discount', '$imageName')";


    if ($conn->query($sql)) {
        $success = true;
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
    <link href="../css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="../css/style.css" rel="stylesheet">
    <link href="admin.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

    <div class="container-fluid nav-bar p-0">
        <div class="row gx-0 bg-primary px-5 align-items-center">
            <div class="col-lg-3 d-none d-lg-block">
                <nav class="navbar navbar-light position-relative" style="width: 250px;">
                    <button class="navbar-toggler border-0 fs-4 w-100 px-0 text-start" type="button"
                        data-bs-toggle="collapse" data-bs-target="#allCat"> 
                 <h1 class="display-5 text-secondary m-0" ><i class="fas fa-shopping-bag text-white me-2" ></i>Digiline</h1>
                    </button>

                </nav>
            </div>
            <div class="col-12 col-lg-9">
                <nav class="navbar navbar-expand-lg navbar-light bg-primary ">
                    <a href="" class="navbar-brand d-block d-lg-none">
                        <h1 class="display-5 text-secondary m-0"><i
                                class="fas fa-shopping-bag text-white me-2"></i>Digiline</h1>

                    </a>
                    <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarCollapse">
                        <span class="fa fa-bars fa-1x"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarCollapse">
                        <div class="navbar-nav ms-auto py-0">
                            <a href="../index.php" class="nav-item nav-link">Home</a>
                            <a href="../cart.php" class="nav-item nav-link active">Cart</a>
                            <a href="<?= isset($_SESSION["user"]["id"]) ? "../logout_action.php" : "../login.php" ?>" class="nav-item nav-link"><?= isset($_SESSION["user"]["id"])? "logout" : "login" ?></a>

                                <?php if (isset($_SESSION["user"]["id"])): ?>
                                  <a href="../myaccount.php" class="nav-item nav-link">My Account</a>
                              <?php endif; ?>

                              <?php if (isset($_SESSION["user"]["id"]) && $_SESSION["user"]["role"] === "admin"): ?>
                              <a href="./admin//creat.php" class="nav-item nav-link">Admin Panel</a>
                              <?php endif; ?>
                        </div>
                        <a href="" class="btn btn-secondary rounded-pill py-2 px-4 px-lg-3 mb-3 mb-md-3 mb-lg-0"><i
                                class="fa fa-mobile-alt me-2"></i>0795717995</a>
                    </div>
                </nav>
            </div>
        </div>
    </div>
    <!-- Navbar & Hero End -->
     <!-- side bar -->
      <div class="admin-wrapper">
    <!-- Sidebar -->
    <div class="sidebar">
        <h3 class="sidebar-title">Admin Panel</h3>

        <ul class="sidebar-menu">
            <li><a href="products_list.php">Products</a></li>
            <li><a href="create.php">Add Product</a></li>
            <li><a href="Order-details.php">Order Details</a></li>
            <li><a href="orders_list.php">Orders</a></li>
            <li><a href="user_list.php">Users</a></li>
            <li class="logout"><a href="../logout_action.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2>Add New Product</h2>

<form method="POST" enctype="multipart/form-data">

    <label>Product Name</label><br>
    <input type="text" name="name" required><br><br>

    <label>Description</label><br>
    <textarea name="description"></textarea><br><br>

    <label>Price</label><br>
    <input type="number" name="price" required><br><br>

    <label>Discount (%)</label><br>
    <input type="number" name="discount" value="0"><br><br>

    <label>Category</label><br>
    <select name="category_id" required>
        <option value="">-- Select Category --</option>

        <?php
        while ($row = $categories->fetch_assoc()) {
            echo "<option value='{$row['id']}'>{$row['name']}</option>";
        }
        ?>
    </select><br><br>

    <label>Product Image</label><br>
    <input type="file" name="image" required><br><br>

    <button type="submit" name="save">Save Product</button>

</form>
<?php if ($success): ?>
<script>
Swal.fire({
    title: "Success!",
    text: "Product added successfully",
    icon: "success",
    confirmButtonText: "OK"
}).then((result) => {
    if (result.isConfirmed) {
        window.location.href = "products_list.php";
    }
});
</script>
<?php endif; ?>
    </div>
    </div>






