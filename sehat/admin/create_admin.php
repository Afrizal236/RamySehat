<?php
require_once 'config.php';

// Password yang akan di-hash
$admin_password = 'admin123';
$admin_email = 'admin@ramysehat.com';
$admin_username = 'admin';

// Generate hash yang benar
$correct_hash = password_hash($admin_password, PASSWORD_DEFAULT);

echo "<h2>🔧 Create Admin User</h2>";
echo "<p><strong>Password:</strong> $admin_password</p>";
echo "<p><strong>Hash Generated:</strong> <br><code>$correct_hash</code></p>";

try {
    // Cek apakah kolom role sudah ada
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>❌ Kolom 'role' belum ada! Menambahkan...</p>";
        $pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'user' AFTER email");
        echo "<p style='color: green;'>✅ Kolom 'role' berhasil ditambahkan!</p>";
    } else {
        echo "<p style='color: green;'>✅ Kolom 'role' sudah ada</p>";
    }

    // Cek apakah admin sudah ada
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$admin_email]);
    $existing_admin = $stmt->fetch();

    if ($existing_admin) {
        echo "<p style='color: orange;'>⚠️ Admin sudah ada! Updating password...</p>";
        
        // Update admin yang sudah ada
        $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, role = 'admin' WHERE email = ?");
        $result = $stmt->execute([$admin_username, $correct_hash, $admin_email]);
        
        if ($result) {
            echo "<p style='color: green;'>✅ Admin password berhasil diupdate!</p>";
        } else {
            echo "<p style='color: red;'>❌ Gagal update admin!</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Admin belum ada. Creating new admin...</p>";
        
        // Insert admin baru
        $stmt = $pdo->prepare("INSERT INTO users (username, email, role, password) VALUES (?, ?, 'admin', ?)");
        $result = $stmt->execute([$admin_username, $admin_email, $correct_hash]);
        
        if ($result) {
            echo "<p style='color: green;'>✅ Admin berhasil dibuat!</p>";
        } else {
            echo "<p style='color: red;'>❌ Gagal membuat admin!</p>";
        }
    }

    // Test login dengan hash yang baru
    echo "<h3>🧪 Test Password Verification</h3>";
    if (password_verify($admin_password, $correct_hash)) {
        echo "<p style='color: green;'>✅ Password verification SUCCESS!</p>";
    } else {
        echo "<p style='color: red;'>❌ Password verification FAILED!</p>";
    }

    // Show final admin data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$admin_email]);
    $admin = $stmt->fetch();

    if ($admin) {
        echo "<h3>👤 Admin Data:</h3>";
        echo "<ul>";
        echo "<li><strong>ID:</strong> {$admin['id']}</li>";
        echo "<li><strong>Username:</strong> {$admin['username']}</li>";
        echo "<li><strong>Email:</strong> {$admin['email']}</li>";
        echo "<li><strong>Role:</strong> {$admin['role']}</li>";
        echo "<li><strong>Password Hash:</strong> <br><small>{$admin['password']}</small></li>";
        echo "</ul>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
code { background: #f8f9fa; padding: 5px; border-radius: 3px; font-size: 12px; word-break: break-all; }
</style>

<div style="background: #e7f3ff; padding: 20px; border-radius: 10px; margin: 20px 0;">
    <h3>🔑 Kredensial Admin yang Benar:</h3>
    <ul>
        <li><strong>Username:</strong> admin</li>
        <li><strong>Email:</strong> admin@ramysehat.com</li>
        <li><strong>Password:</strong> admin123</li>
    </ul>
</div>

<div style="text-align: center; margin: 30px 0;">
    <a href="login.php" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🧪 Test Login</a>
    <a href="index.html" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;">🏠 Home</a>
</div>