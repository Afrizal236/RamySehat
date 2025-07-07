<?php
require_once 'config.php';

// Set header untuk JSON response
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get and sanitize input data
    $full_name = sanitizeInput($_POST['fullName'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $subject = sanitizeInput($_POST['subject'] ?? '');
    $message = sanitizeInput($_POST['message'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($full_name) || strlen($full_name) < 2) {
        $errors[] = 'Nama lengkap minimal 2 karakter';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid';
    }
    
    if (empty($phone) || strlen($phone) < 10) {
        $errors[] = 'Nomor telepon minimal 10 digit';
    }
    
    if (empty($subject) || strlen($subject) < 3) {
        $errors[] = 'Subjek minimal 3 karakter';
    }
    
    if (empty($message) || strlen($message) < 10) {
        $errors[] = 'Pesan minimal 10 karakter';
    }
    
    // If there are validation errors
    if (!empty($errors)) {
        echo json_encode([
            'success' => false, 
            'message' => implode(', ', $errors)
        ]);
        exit();
    }
    
    // Insert into database
    $stmt = $pdo->prepare("
        INSERT INTO contact_messages (full_name, email, phone, subject, message) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([$full_name, $email, $phone, $subject, $message]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Pesan Anda berhasil dikirim! Tim kami akan segera menghubungi Anda.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menyimpan pesan. Silakan coba lagi.'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan sistem. Silakan coba lagi nanti.'
    ]);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan. Silakan coba lagi.'
    ]);
}
?>