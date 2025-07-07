<?php
// test_complete_system.php - Script untuk test sistem signup dan login
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

// Test 2: Table Structure
runTest('Users Table Structure', function() {
    global $pdo;
    try {
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll();
        $required_columns = ['id', 'username', 'email', 'password', 'created_at'];
        
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

// Test 3: Create Test User (Signup Process)
runTest('Signup Process Test', function() {
    global $pdo;
    
    $test_email = 'test_' . time() . '@example.com';
    $test_password = 'testpass123';
    
    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$test_email]);
        
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Test email already exists'];
        }
        
        // Generate username
        $emailParts = explode('@', $test_email);
        $baseUsername = $emailParts[0];
        $username = $baseUsername . rand(100, 999);
        
        // Make sure username is unique
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        while ($stmt->fetch()) {
            $username = $baseUsername . rand(100, 999);
            $stmt->execute([$username]);
        }
        
        // Hash password
        $hashed_password = password_hash($test_password, PASSWORD_DEFAULT);
        
        // Insert user
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $result = $stmt->execute([$username, $test_email, $hashed_password]);
        
        if ($result) {
            return ['success' => true, 'message' => "Test user created successfully - Email: $test_email, Username: $username"];
        } else {
            return ['success' => false, 'message' => 'Failed to create test user'];
        }
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Signup test error: ' . $e->getMessage()];
    }
});

// Test 4: Login Process Test
runTest('Login Process Test', function() {
    global $pdo;
    
    // Use existing user or create one
    $test_email = 'ramydiaman@gmail.com';
    $test_password = 'Ramydiaman23';
    
    try {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$test_email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Test user not found in database'];
        }
        
        // Verify password
        if (password_verify($test_password, $user['password'])) {
            return ['success' => true, 'message' => "Login test successful - User: {$user['username']}, Email: {$user['email']}"];
        } else {
            return ['success' => false, 'message' => 'Password verification failed'];
        }
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Login test error: ' . $e->getMessage()];
    }
});

// Test 5: Password Hash Security
runTest('Password Hash Security', function() {
    $test_password = 'securepassword123';
    $hash1 = password_hash($test_password, PASSWORD_DEFAULT);
    $hash2 = password_hash($test_password, PASSWORD_DEFAULT);
    
    // Hashes should be different (salted)
    if ($hash1 !== $hash2) {
        if (password_verify($test_password, $hash1) && password_verify($test_password, $hash2)) {
            return ['success' => true, 'message' => 'Password hashing working correctly with salt'];
        } else {
            return ['success' => false, 'message' => 'Password verification failed'];
        }
    } else {
        return ['success' => false, 'message' => 'Password hashes are identical (no salt)'];
    }
});

// Test 6: Complete Signup-Login Flow
runTest('Complete Signup-Login Flow', function() {
    global $pdo;
    
    $flow_email = 'flow_test_' . time() . '@example.com';
    $flow_password = 'flowtest123';
    
    try {
        // Step 1: Signup
        $emailParts = explode('@', $flow_email);
        $baseUsername = $emailParts[0];
        $username = $baseUsername . rand(100, 999);
        
        // Make username unique
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        while ($stmt->fetch()) {
            $username = $baseUsername . rand(100, 999);
            $stmt->execute([$username]);
        }
        
        $hashed_password = password_hash($flow_password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $signup_result = $stmt->execute([$username, $flow_email, $hashed_password]);
        
        if (!$signup_result) {
            return ['success' => false, 'message' => 'Signup phase failed'];
        }
        
        // Step 2: Login with same credentials
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$flow_email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Login phase failed - user not found'];
        }
        
        if (!password_verify($flow_password, $user['password'])) {
            return ['success' => false, 'message' => 'Login phase failed - password incorrect'];
        }
        
        return ['success' => true, 'message' => "Complete flow successful! Signup → Login works perfectly. User: $username"];
        
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Flow test error: ' . $e->getMessage()];
    }
});

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete System Test - RamySehat</title>
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
            border-bottom: 2px solid #007bff;
        }
        
        .header h1 {
            color: #007bff;
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
        .total-count { color: #007bff; }
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
            background: #e7f3ff;
            border-radius: 8px;
            border: 1px solid #b3d7ff;
        }
        
        .instructions h3 {
            color: #0066cc;
            margin-bottom: 15px;
        }
        
        .instructions ol {
            margin-left: 20px;
        }
        
        .instructions li {
            margin-bottom: 8px;
        }
        
        .nav-buttons {
            text-align: center;
            margin-top: 30px;
        }
        
        .btn {
            background: #007bff;
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
            <h1>🧪 Complete System Test</h1>
            <p>Testing Signup & Login Integration - RamySehat</p>
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
                <p><strong>Sistem Signup dan Login sudah bekerja dengan sempurna!</strong></p>
                
                <h4>Cara Menggunakan:</h4>
                <ol>
                    <li><strong>Buat Akun Baru:</strong> Buka signup.php, masukkan email dan password → Akun tersimpan di database</li>
                    <li><strong>Login:</strong> Buka login.php, gunakan email dan password yang sama → Berhasil masuk</li>
                    <li><strong>Atau via Modal:</strong> Di index.html, klik tombol Login → isi email dan password → langsung ke dashboard</li>
                </ol>
                
                <p><strong>Akun Test yang Sudah Ada:</strong></p>
                <ul>
                    <li>Email: <code>ramydiaman@gmail.com</code></li>
                    <li>Password: <code>Ramydiaman23</code></li>
                </ul>
            </div>
        <?php else: ?>
            <div class="instructions">
                <h3>⚠️ Ada Masalah yang Perlu Diperbaiki</h3>
                <p>Beberapa test gagal. Silakan periksa konfigurasi database dan file-file berikut:</p>
                <ol>
                    <li>Pastikan XAMPP/WAMP sudah berjalan</li>
                    <li>Database "kesehatan_db" sudah dibuat</li>
                    <li>Tabel "users" sudah dibuat dengan struktur yang benar</li>
                    <li>File config.php memiliki koneksi database yang benar</li>
                </ol>
            </div>
        <?php endif; ?>

        <div class="nav-buttons">
            <a href="index.html" class="btn">🏠 Back to Home</a>
            <a href="signup.php" class="btn btn-success">📝 Test Signup</a>
            <a href="login.php" class="btn btn-success">🔑 Test Login</a>
            <a href="test_database.php" class="btn">🗄️ Database Test</a>
        </div>
    </div>
</body>
</html>