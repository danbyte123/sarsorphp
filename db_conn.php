<?php
$servername = "localhost";
$username = "root";
$password = "123";
$dbname = "home_services";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully"; // Temporary line to test connection
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>