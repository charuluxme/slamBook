<?php
$serverName = "localhost";
$userName = "root";
$password = "";
$databaseName = "slamBook";

$conn = mysqli_connect($serverName, $userName, $password, $databaseName);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");