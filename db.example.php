<?php
$servername = "localhost";
$username = "perehvat";
$password = "password";
$db = "perehvat";
$history_password = "password used for requesting history and settings";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$db;charset=utf8mb4", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
catch(PDOException $e)
    {
    echo "Connection failed: " . $e->getMessage();
    }


?>
