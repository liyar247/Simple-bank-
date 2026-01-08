<?php
session_start();

$host = 'localhost';
$dbname = 'bank_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to generate account number
function generateAccountNumber() {
    return 'BNK' . date('Ymd') . rand(1000, 9999);
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if employee is logged in
function isEmployeeLoggedIn() {
    return isset($_SESSION['employee_id']);
}
?>