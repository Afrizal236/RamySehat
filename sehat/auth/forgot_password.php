<?php
require_once 'config.php';

$error = '';
$success = '';
$step = 1; // Step 1: Input email, Step 2: Input new password

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['step']) && $_POST['step'] == '1') {
        // Step 1: Verify email
        $email = sanitizeInput($_POST['email']);
        
        if (empty($email)) {
            $error = 'Email harus diisi!';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid!';
        } else {
            try {
                // Check if email exists in database
                $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if ($user) {
                    // Email found, proceed to step 2
                    $_SESSION['reset_email'] = $email;
                    $_SESSION['reset_user_id'] = $user['id'];
                    $_SESSION['reset_username'] = $user['username'];
                    $step = 2;
                    $success = 'Email ditemukan! Silakan masukkan password baru Anda.';
                } else {
                    $error = 'Email tidak terdaftar dalam sistem kami!';
                }
            } catch(PDOException $e) {
                $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['step']) && $_POST['step'] == '2') {
        // Step 2: Update password
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($new_password) || empty($confirm_password)) {
            $error = 'Password baru dan konfirmasi password harus diisi!';
            $step = 2;
        } elseif (strlen($new_password) < 6) {
            $error = 'Password minimal 6 karakter!';
            $step = 2;
        } elseif ($new_password !== $confirm_password) {
            $error = 'Password baru dan konfirmasi password tidak cocok!';
            $step = 2;
        } else {
            try {
                // Update password in database
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $result = $stmt->execute([$hashed_password, $_SESSION['reset_user_id']]);
                
                if ($result) {
                    // Clear reset session data
                    unset($_SESSION['reset_email']);
                    unset($_SESSION['reset_user_id']);
                    unset($_SESSION['reset_username']);
                    
                    $success = 'Password berhasil diperbarui! Anda sekarang dapat login dengan password baru.';
                    $step = 3; // Success step
                    
                    // Auto redirect to login after 3 seconds
                    echo "<script>setTimeout(function(){ window.location.href = 'login.php'; }, 3000);</script>";
                } else {
                    $error = 'Gagal memperbarui password!';
                    $step = 2;
                }
            } catch(PDOException $e) {
                $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
                $step = 2;
            }
        }
    }
} else {
    // Check if user is in middle of reset process
    if (isset($_SESSION['reset_email'])) {
        $step = 2;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - RamySehat</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .forgot-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
            position: relative;
            overflow: hidden;
        }

        .forgot-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #dc3545, #c82333);
        }

        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 15px;
            object-fit: cover;
            border: 3px solid #dc3545;
        }

        .title {
            font-size: 2rem;
            color: #333;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .subtitle {
            color: #666;
            font-size: 1rem;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            gap: 20px;
        }

        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e9ecef;
            color: #6c757d;
            font-weight: bold;
            position: relative;
        }

        .step.active {
            background: #dc3545;
            color: white;
        }

        .step.completed {
            background: #28a745;
            color: white;
        }

        .step::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 100%;
            width: 20px;
            height: 2px;
            background: #e9ecef;
            transform: translateY(-50%);
        }

        .step:last-child::after {
            display: none;
        }

        .step.completed::after {
            background: #28a745;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus {
            outline: none;
            border-color: #dc3545;
            background: white;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, #dc3545, #c82333);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .btn:hover {
            background: linear-gradient(45deg, #c82333, #a71e2a);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-secondary {
            background: linear-gradient(45deg, #6c757d, #545b62);
        }

        .btn-secondary:hover {
            background: linear-gradient(45deg, #545b62, #495057);
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        .info {
            background: #e7f3ff;
            color: #0066cc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #b3d7ff;
        }

        .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .user-info .email {
            color: #007bff;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .user-info .username {
            color: #6c757d;
            margin-top: 5px;
        }

        .links {
            text-align: center;
            margin-top: 20px;
        }

        .links a {
            color: #dc3545;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .links a:hover {
            color: #c82333;
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            padding: 10px 15px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: rgba(220, 53, 69, 0.2);
        }

        .password-strength {
            font-size: 0.8rem;
            margin-top: 5px;
            padding: 5px 10px;
            border-radius: 5px;
            transition: all 0.3s ease;
            display: none;
        }

        .weak {
            background: #f8d7da;
            color: #721c24;
        }

        .medium {
            background: #fff3cd;
            color: #856404;
        }

        .strong {
            background: #d4edda;
            color: #155724;
        }

        @media (max-width: 480px) {
            .forgot-container {
                margin: 20px;
                padding: 30px 20px;
            }
            
            .title {
                font-size: 1.8rem;
            }

            .step-indicator {
                gap: 15px;
            }

            .step {
                width: 35px;
                height: 35px;
            }

            .step::after {
                width: 15px;
            }
        }
    </style>
</head>
<body>
    <a href="login.php" class="back-btn">← Kembali ke Login</a>
    
    <div class="forgot-container">
        <div class="logo-container">
            <img src="https://github.com/Afrizal236/kesehatan/blob/main/halodoc3588.jpg?raw=true" alt="Logo RamySehat" class="logo">
            <h1 class="title">Lupa Password</h1>
            <p class="subtitle">
                <?php if ($step == 1): ?>
                    Masukkan email Anda untuk reset password
                <?php elseif ($step == 2): ?>
                    Buat password baru untuk akun Anda
                <?php else: ?>
                    Password berhasil diperbarui!
                <?php endif; ?>
            </p>
        </div>

        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step <?php echo ($step >= 1) ? 'active' : ''; ?> <?php echo ($step > 1) ? 'completed' : ''; ?>">1</div>
            <div class="step <?php echo ($step >= 2) ? 'active' : ''; ?> <?php echo ($step > 2) ? 'completed' : ''; ?>">2</div>
            <div class="step <?php echo ($step >= 3) ? 'active' : ''; ?>">✓</div>
        </div>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($step == 1): ?>
            <!-- Step 1: Input Email -->
            <div class="info">
                <strong>Instruksi:</strong><br>
                Masukkan alamat email yang terdaftar di akun RamySehat Anda. Kami akan memverifikasi email tersebut sebelum mengizinkan Anda membuat password baru.
            </div>

            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="emailForm">
                <input type="hidden" name="step" value="1">
                
                <div class="form-group">
                    <label for="email">Email Terdaftar</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           placeholder="contoh@email.com">
                </div>

                <button type="submit" class="btn" id="submitBtn">Verifikasi Email</button>
            </form>

        <?php elseif ($step == 2): ?>
            <!-- Step 2: Input New Password -->
            <div class="user-info">
                <div class="email"><?php echo htmlspecialchars($_SESSION['reset_email']); ?></div>
                <div class="username">Username: <?php echo htmlspecialchars($_SESSION['reset_username']); ?></div>
            </div>

            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="passwordForm">
                <input type="hidden" name="step" value="2">
                
                <div class="form-group">
                    <label for="new_password">Password Baru</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6"
                           placeholder="Minimal 6 karakter">
                    <div id="passwordStrength" class="password-strength"></div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password Baru</label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           placeholder="Ulangi password baru">
                </div>

                <button type="submit" class="btn" id="submitBtn">Perbarui Password</button>
                <button type="button" class="btn btn-secondary" onclick="resetProcess()">Mulai Ulang</button>
            </form>

        <?php else: ?>
            <!-- Step 3: Success -->
            <div class="info">
                <strong>Berhasil!</strong><br>
                Password Anda telah berhasil diperbarui. Anda akan diarahkan ke halaman login dalam beberapa detik.
            </div>

            <a href="login.php" class="btn">Login Sekarang</a>
        <?php endif; ?>

        <div class="links">
            <p>Ingat password? <a href="login.php">Kembali ke Login</a></p>
        </div>
    </div>

    <script>
        // Form validation for email step
        <?php if ($step == 1): ?>
        document.getElementById('emailForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();

            if (!email) {
                e.preventDefault();
                alert('Email harus diisi!');
                return;
            }

            if (!validateEmail(email)) {
                e.preventDefault();
                alert('Format email tidak valid!');
                return;
            }

            // Add loading state
            const btn = document.getElementById('submitBtn');
            btn.textContent = 'Memverifikasi...';
            btn.disabled = true;
        });
        <?php endif; ?>

        // Form validation for password step
        <?php if ($step == 2): ?>
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (!newPassword || !confirmPassword) {
                e.preventDefault();
                alert('Password baru dan konfirmasi password harus diisi!');
                return;
            }

            if (newPassword.length < 6) {
                e.preventDefault();
                alert('Password minimal 6 karakter!');
                return;
            }

            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Password baru dan konfirmasi password tidak cocok!');
                return;
            }

            // Add loading state
            const btn = document.getElementById('submitBtn');
            btn.textContent = 'Memperbarui...';
            btn.disabled = true;
        });

        // Password strength checker
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthDiv.style.display = 'none';
                return;
            }
            
            strengthDiv.style.display = 'block';
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            strengthDiv.className = 'password-strength';
            
            if (strength < 3) {
                strengthDiv.classList.add('weak');
                strengthDiv.textContent = 'Password lemah';
            } else if (strength < 4) {
                strengthDiv.classList.add('medium');
                strengthDiv.textContent = 'Password sedang';
            } else {
                strengthDiv.classList.add('strong');
                strengthDiv.textContent = 'Password kuat';
            }
        });

        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.style.borderColor = '#dc3545';
            } else {
                this.style.borderColor = '#28a745';
            }
        });

        // Reset process function
        function resetProcess() {
            if (confirm('Apakah Anda yakin ingin memulai ulang proses reset password?')) {
                window.location.href = 'forgot_password.php?reset=1';
            }
        }
        <?php endif; ?>

        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // Clear session if reset parameter is present
        <?php if (isset($_GET['reset'])): ?>
        <?php 
        unset($_SESSION['reset_email']); 
        unset($_SESSION['reset_user_id']); 
        unset($_SESSION['reset_username']); 
        ?>
        window.location.href = 'forgot_password.php';
        <?php endif; ?>
    </script>
</body>
</html>