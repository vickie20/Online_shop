<?php
$host = 'localhost';
$dbname = 'luxury_home_decor';
$username = 'root'; // Change as needed
$password = ''; // Change as needed

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}
?>