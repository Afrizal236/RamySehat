<?php
// check_session.php - API untuk mengecek status login user
require_once 'config.php';

// Set header untuk JSON response
header('Content-Type: application/json');

// Cek apakah user sudah login
if (isset($_SESSION['user_id']) && isset($_SESSION['username']) && isset($_SESSION['email'])) {
    // User sudah login
    echo json_encode([
        'logged_in' => true,
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email' => $_SESSION['email'],
        'role' => $_SESSION['role'] ?? 'user'
    ]);
} else {
    // User belum login
    echo json_encode([
        'logged_in' => false,
        'username' => 'Guest',
        'email' => null,
        'role' => 'guest'
    ]);
}
?>