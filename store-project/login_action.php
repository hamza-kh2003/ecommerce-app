<?php
require_once "Database.php";
session_start();

$conn = Database::getInstance();

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

$errors = [];

if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email";
if ($password === "") $errors[] = "Password required";

if ($errors) {
    $_SESSION["login_errors"] = $errors;
    header("Location: login.php");
    exit;
}

$stmt = $conn->prepare(
    "SELECT id, name, email, password, role, phone
     FROM users WHERE email = ? LIMIT 1"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user["password"])) {
    $_SESSION["login_errors"] = ["Wrong email or password"];
    header("Location: login.php");
    exit;
}

$_SESSION["user"] = [
    "id" => $user["id"],
    "name" => $user["name"],
    "email" => $user["email"],
    "role" => $user["role"]
];


$userId = (int)$_SESSION["user"]["id"];
$sessionCart = $_SESSION["cart"] ?? [];

if ($sessionCart) {
    $stmt = $conn->prepare("
        INSERT INTO cart (user_id, product_id, quantity)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
    ");

    foreach ($sessionCart as $pid => $qty) {
        $pid = (int)$pid;
        $qty = (int)$qty;
        if ($pid > 0 && $qty > 0) {
            $stmt->bind_param("iii", $userId, $pid, $qty);
            $stmt->execute();
        }
    }
    $stmt->close();
    unset($_SESSION["cart"]);
}




header("Location:index.php");
exit;
