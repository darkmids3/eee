<?php
// Database configuration for Laragon
$host = 'localhost';
$dbname = 'truck_management';
$username = 'root';
$password = 'mickoxcare';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session for authentication
session_start();

// Admin credentials (in production, hash passwords properly)
$admin_users = [
    'admin' => 'password123',
    'manager' => 'manager123'
];

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Require login for protected pages
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}
?>