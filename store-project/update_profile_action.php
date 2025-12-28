<?php
require_once "Database.php";
session_start();

if (!isset($_SESSION["user"]["id"])) {
    header("Location: login.php");
    exit;
}

$conn = Database::getInstance();
$userId = (int)$_SESSION["user"]["id"];

$name = trim($_POST["name"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$email = trim($_POST["email"] ?? "");

$errors = [];

if ($name === "" || mb_strlen($name) < 3) $errors[] = "Invalid name";
if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email";
if ($phone !== "" && !preg_match('/^\+?\d{8,15}$/', $phone)) $errors[] = "Invalid phone";

if (!$errors) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1");
    $stmt->bind_param("si", $email, $userId);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) $errors[] = "Email already used";
    $stmt->close();
}

if ($errors) {
    $_SESSION["profile_errors"] = $errors;
    header("Location: myaccount.php");
    exit;
}

$stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ? LIMIT 1");
$stmt->bind_param("sssi", $name, $email, $phone, $userId);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
    $_SESSION["profile_errors"] = ["Update failed"];
    header("Location: myaccount.php");
    exit;
}

$_SESSION["user"]["name"] = $name;
$_SESSION["user"]["email"] = $email;

$_SESSION["profile_success"] = "Profile updated";
header("Location: myaccount.php");
exit;
