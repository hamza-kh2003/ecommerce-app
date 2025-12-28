<?php

class Database
{
    private static $instance = null;
    private static $conn = null;

    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $db   = "ecommerce_db";

   
    private function __construct()
    {
        self::$conn = new mysqli(
            $this->host,
            $this->user,
            $this->pass,
            $this->db
        );

        if (self::$conn->connect_error) {
            die("Database Connection Failed");
        }

        self::$conn->set_charset("utf8mb4");
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database(); 
        }

        return self::$conn;
    }
}
