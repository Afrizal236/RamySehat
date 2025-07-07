<?php
// config.php - Konfigurasi Database yang Diperbaiki
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kesehatan_db";

try {
    // Create connection with proper charset
    $pdo = new PDO(
        "mysql:host=$servername;dbname=$dbname;charset=utf8mb4", 
        $username, 
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
} catch(PDOException $e) {
    die("
    <div style='background: #f8d7da; color: #721c24; padding: 20px; border-radius: 8px; margin: 20px; border: 1px solid #f5c6cb;'>
        <h3>Database Connection Error</h3>
        <p>Tidak dapat terhubung ke database. Error: " . $e->getMessage() . "</p>
    </div>
    ");
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
?>