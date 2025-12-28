<?php

session_start();

if (!isset($_SESSION["user"]["id"])||$_SESSION["user"]["role"]!=="admin") {
    header("Location:../index.php");
    exit;
}



require_once "../Database.php";
$conn = Database::getInstance();

if(isset($_GET['id']) && isset($_GET['status'])){
    $id = $_GET['id'];
    $status = $_GET['status'];

    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();

    header("Location: orders_list.php");
    exit;
}
