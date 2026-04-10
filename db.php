<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "live_stock";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

$conn->set_charset("utf8");
?>
