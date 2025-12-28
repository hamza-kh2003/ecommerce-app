<?php
require_once "Database.php";
session_start();

$conn = Database::getInstance();

$name = trim($_POST["username"] ?? "");
$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$password = $_POST["password"] ?? "";
$confirm = $_POST["confirmPassword"] ?? "";

$errors = [];

if ($name === "" || strlen($name) < 3) $errors[] = "Invalid username";
if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email";
if ($phone === "" || !preg_match('/^\+?\d{8,15}$/', $phone)) $errors[] = "Invalid phone";
if (!preg_match('/^(?=.*[A-Z])(?=.*\d).{6,}$/', $password)) $errors[] = "Invalid password";
if ($confirm !== $password) $errors[] = "Passwords do not match";

if (!$errors) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) $errors[] = "Email already exists";
    $stmt->close();
}

if ($errors) {
    $_SESSION["reg_errors"] = $errors;
    header("Location: register.php");
    exit;
}

$hashed = password_hash($password, PASSWORD_DEFAULT);
$role = "user";

$stmt = $conn->prepare(
    "INSERT INTO users (name, email, password, role, phone)
     VALUES (?, ?, ?, ?, ?)"
);
$stmt->bind_param("sssss", $name, $email, $hashed, $role, $phone);

if ($stmt->execute()) {
    $_SESSION["login_success"] = "Account created successfully";
    header("Location: login.php");
    exit;
}

$_SESSION["reg_errors"] = ["Registration failed"];
header("Location: register.php");
exit;
