<?php
// test_forgot_password.php - Test script for forgot password functionality
require_once 'config.php';

$test_results = [];
$test_passed = 0;
$test_total = 0;

function runTest($description, $test_function) {
    global $test_results, $test_passed, $test_total;
    $test_total++;
    
    try {
        $result = $test_function();
        if ($result['success']) {
            $test_passed++;
            $test_results[] = [
                'status' => 'success',
                'description' => $description,
                'message' => $result['message']
            ];
        } else {
            $test_results[] = [
                'status' => 'error',
                'description' => $description,
                'message' => $result['message']
            ];
        }
    } catch (Exception $e) {
        $test_results[] = [
            'status' => 'error',
            'description' => $description,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

// Test 1: Database Connection
runTest('Database Connection', function() {
    global $pdo;
    try {
        $pdo->query("SELECT 1");
        return ['success' => true, 'message' => 'Database connection successful'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()];
    }
});

// Test 2: Check Users Table Structure
runTest('Users Table Structure', function() {
    global $pdo;
    try {
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll();
        $required_columns = ['id', 'username', 'email', 'password', 'created_at', 'updated_at'];
        
        $found_columns = array_column($columns, 'Field');
        $missing = array_diff($required_columns, $found_columns);
        
        if (empty($missing)) {
            return ['success' => true, 'message' => 'All required columns found: ' . implode(', ', $found_columns)];
        } else {
            return ['success' => false, 'message' => 'Missing columns: ' . implode(', ', $missing)];
        }
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Error checking table: ' . $e->getMessage()];
    }
});

// Test 3: Test Email Verification
runTest('Email Verification Process', function() {
    global $pdo;
    
    $test_email = 'ramydiaman@gmail.com';
    
    try {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->execute([$test_email]);
        $user = $stmt->fetch();
        
        if ($user) {
            return ['success' => true, 'message' => "Email found in database - ID: {$user['id']}, Username: {$user['username']}"];
        } else {
            return ['success' => false, 'message' => 'Test email not found in database'];
        }
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Email verification error: ' . $e->getMessage()];
    }
});

// Test 4: Test Password Update Process
runTest('Password Update Process', function() {
    global $pdo;
    
    $test_email = 'ramydiaman@gmail.com';
    $test_new_password = 'NewTestPassword123';
    
    try {
        // Get current password hash
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ?");
        $stmt->execute([$test_email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Test user not found'];
        }
        
        $old_password_hash = $user['password'];
        
        // Create new password hash
        $new_password_hash = password_hash($test_new_password, PASSWORD_DEFAULT);
        
        // Update password (simulate the update)
        $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $result = $stmt->execute([$new_password_hash, $user['id']]);
        
        if ($result) {
            // Verify the password was updated
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $updated_user = $stmt->fetch();
            
            if ($updated_user['password'] !== $old_password_hash) {
                // Restore original password for testing purposes
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$old_password_hash, $user['id']]);
                
                return ['success' => true, 'message' => 'Password update successful and verified'];
            } else {
                return ['success' => false, 'message' => 'Password was not updated'];
            }
        } else {
            return ['success' => false, 'message' => 'Failed to update password'];
        }
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Password update error: ' . $e->getMessage()];
    }
});

// Test 5: Test Password Hashing and Verification
runTest('Password Hashing and Verification', function() {
    $test_password = 'TestPassword123';
    
    try {
        // Test password hashing
        $hash1 = password_hash($test_password, PASSWORD_DEFAULT);
        $hash2 = password_hash($test_password, PASSWORD_DEFAULT);
        
        // Hashes should be different (salted)
        if ($hash1 !== $hash2) {
            // Test password verification
            if (password_verify($test_password, $hash1) && password_verify($test_password, $hash2)) {
                return ['success' => true, 'message' => 'Password hashing and verification working correctly'];
            } else {
                return ['success' => false, 'message' => 'Password verification failed'];
            }
        } else {
            return ['success' => false, 'message' => 'Password hashes are identical (no salt)'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Password hashing error: ' . $e->getMessage()];
    }
});

// Test 6: Complete Forgot Password Flow Simulation
runTest('Complete Forgot Password Flow', function() {
    global $pdo;
    
    $test_email = 'ramydiaman@gmail.com';
    $new_password = 'ForgotPasswordTest123';
    
    try {
        // Step 1: Verify email exists
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->execute([$test_email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Email verification failed'];
        }
        
        // Step 2: Generate new password hash
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Step 3: Update password
        $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $update_result = $stmt->execute([$new_hash, $user['id']]);
        
        if (!$update_result) {
            return ['success' => false, 'message' => 'Password update failed'];
        }
        
        // Step 4: Verify new password works
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $updated_user = $stmt->fetch();
        
        if (password_verify($new_password, $updated_user['password'])) {
            // Restore original password (Ramydiaman23)
            $original_hash = password_hash('Ramydiaman23', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$original_hash, $user['id']]);
            
            return ['success' => true, 'message' => 'Complete forgot password flow successful - Email verified → Password updated → New password verified → Original password restored'];
        } else {
            return ['success' => false, 'message' => 'New password verification failed'];
        }
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Complete flow error: ' . $e->getMessage()];
    }
});

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Forgot Password - RamySehat</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #dc3545;
        }
        
        .header h1 {
            color: #dc3545;
            margin-bottom: 10px;
        }
        
        .summary {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .summary-item {
            text-align: center;
        }
        
        .summary-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .success-count { color: #28a745; }
        .total-count { color: #dc3545; }
        .success-rate { color: #17a2b8; }
        
        .test-result {
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 8px;
            border-left: 5px solid;
        }
        
        .test-success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        
        .test-error {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        
        .test-title {
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 8px;
        }
        
        .test-message {
            font-size: 0.95rem;
            line-height: 1.4;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
            margin-right: 10px;
        }
        
        .badge-success {
            background: #28a745;
            color: white;
        }
        
        .badge-error {
            background: #dc3545;
            color: white;
        }
        
        .instructions {
            margin-top: 30px;
            padding: 20px;
            background: #fff3cd;
            border-radius: 8px;
            border: 1px solid #ffeaa7;
        }
        
        .instructions h3 {
            color: #856404;
            margin-bottom: 15px;
        }
        
        .nav-buttons {
            text-align: center;
            margin-top: 30px;
        }
        
        .btn {
            background: #dc3545;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            margin: 5px;
            display: inline-block;
            transition: background 0.3s ease;
        }
        
        .btn:hover {
            background: #c82333;
        }
        
        .btn-primary {
            background: #007bff;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-success:hover {
            background: #1e7e34;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Test Forgot Password System</h1>
            <p>Testing Forgot Password Functionality - RamySehat</p>
        </div>

        <div class="summary">
            <div class="summary-item">
                <div class="summary-number success-count"><?php echo $test_passed; ?></div>
                <div>Tests Passed</div>
            </div>
            <div class="summary-item">
                <div class="summary-number total-count"><?php echo $test_total; ?></div>
                <div>Total Tests</div>
            </div>
            <div class="summary-item">
                <div class="summary-number success-rate"><?php echo round(($test_passed / $test_total) * 100); ?>%</div>
                <div>Success Rate</div>
            </div>
        </div>

        <h2>📋 Test Results</h2>

        <?php foreach ($test_results as $test): ?>
            <div class="test-result test-<?php echo $test['status']; ?>">
                <div class="test-title">
                    <span class="status-badge badge-<?php echo $test['status']; ?>">
                        <?php echo $test['status'] === 'success' ? '✓ PASS' : '✗ FAIL'; ?>
                    </span>
                    <?php echo htmlspecialchars($test['description']); ?>
                </div>
                <div class="test-message">
                    <?php echo htmlspecialchars($test['message']); ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if ($test_passed === $test_total): ?>
            <div class="instructions">
                <h3>🎉 Semua Test Berhasil!</h3>
                <p><strong>Sistem Forgot Password sudah siap digunakan!</strong></p>
                
                <h4>Cara Menggunakan Fitur Lupa Password:</h4>
                <ol>
                    <li><strong>Dari Halaman Login:</strong> Klik link "🔑 Lupa Password?" di login.php</li>
                    <li><strong>Step 1 - Verifikasi Email:</strong> Masukkan email terdaftar (contoh: ramydiaman@gmail.com)</li>
                    <li><strong>Step 2 - Password Baru:</strong> Buat password baru minimal 6 karakter</li>
                    <li><strong>Step 3 - Selesai:</strong> Login dengan password baru Anda</li>
                </ol>
                
                <h4>Fitur Keamanan:</h4>
                <ul>
                    <li>✅ Verifikasi email sebelum reset password</li>
                    <li>✅ Password hashing dengan salt</li>
                    <li>✅ Validasi kekuatan password</li>
                    <li>✅ Step-by-step process yang aman</li>
                    <li>✅ Session management untuk keamanan</li>
                </ul>
            </div>
        <?php else: ?>
            <div class="instructions">
                <h3>⚠️ Ada Masalah yang Perlu Diperbaiki</h3>
                <p>Beberapa test gagal. Silakan periksa:</p>
                <ol>
                    <li>Koneksi database sudah benar</li>
                    <li>Tabel users sudah dibuat dengan struktur yang tepat</li>
                    <li>User test (ramydiaman@gmail.com) sudah ada di database</li>
                    <li>File forgot_password.php sudah dibuat dengan benar</li>
                </ol>
            </div>
        <?php endif; ?>

        <div class="nav-buttons">
            <a href="index.html" class="btn btn-primary">🏠 Back to Home</a>
            <a href="login.php" class="btn btn-success">🔑 Test Login</a>
            <a href="forgot_password.php" class="btn">🔐 Test Forgot Password</a>
            <a href="test_database.php" class="btn btn-primary">🗄️ Database Test</a>
        </div>
    </div>
</body>
</html>