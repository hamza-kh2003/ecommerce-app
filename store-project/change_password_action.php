<?php
require_once "Database.php";
session_start();

if (!isset($_SESSION["user"]["id"])) {
    header("Location: login.php");
    exit;
}

$conn = Database::getInstance();
$userId = (int)$_SESSION["user"]["id"];

$old = $_POST["old_password"] ?? "";
$new = $_POST["new_password"] ?? "";
$confirm = $_POST["confirm_password"] ?? "";

$errors = [];

if ($old === "") $errors[] = "Old password required";
if (!preg_match('/^(?=.*[A-Z])(?=.*\d).{6,}$/', $new)) $errors[] = "Invalid new password";
if ($confirm !== $new) $errors[] = "Passwords do not match";

if ($errors) {
    $_SESSION["pass_errors"] = $errors;
    header("Location: myaccount.php");
    exit;
}

$stmt = $conn->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row || !password_verify($old, $row["password"])) {
    $_SESSION["pass_errors"] = ["Old password is wrong"];
    header("Location: myaccount.php");
    exit;
}

$hashed = password_hash($new, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ? LIMIT 1");
$stmt->bind_param("si", $hashed, $userId);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
    $_SESSION["pass_errors"] = ["Password update failed"];
    header("Location: myaccount.php");
    exit;
}

$_SESSION["pass_success"] = "Password changed";
header("Location: myaccount.php");
exit;
