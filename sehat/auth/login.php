<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi!';
    } else {
        try {
            // Cari user berdasarkan email
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'] ?? 'user'; // Default role user
                
                // Cek apakah user adalah admin
                if ($user['role'] === 'admin') {
                    $success = 'Login admin berhasil! Mengalihkan ke dashboard admin...';
                    echo "<script>setTimeout(function(){ window.location.href = 'view_contact_messages.php'; }, 2000);</script>";
                } else {
                    $success = 'Login berhasil! Mengalihkan ke dashboard...';
                    echo "<script>setTimeout(function(){ window.location.href = 'dashboard.php'; }, 2000);</script>";
                }
            } else {
                $error = 'Email atau password salah!';
            }
        } catch(PDOException $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RamySehat</title>
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
            padding: 20px 0;
            position: relative;
            overflow-x: hidden;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            position: relative;
            overflow: hidden;
            margin: 50px auto;
            min-height: auto;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #007bff, #0056b3);
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
            border: 3px solid #007bff;
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
            border-color: #007bff;
            background: white;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, #007bff, #0056b3);
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
            background: linear-gradient(45deg, #0056b3, #004494);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
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

        .links {
            text-align: center;
            margin-top: 20px;
        }

        .links a {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
            display: block;
            margin-bottom: 10px;
        }

        .links a:hover {
            color: #0056b3;
        }

        .forgot-password-link {
            color: #dc3545 !important;
            font-size: 0.9rem;
        }

        .forgot-password-link:hover {
            color: #c82333 !important;
        }

        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: rgba(0, 123, 255, 0.1);
            color: #007bff;
            padding: 10px 15px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .back-btn:hover {
            background: rgba(0, 123, 255, 0.2);
        }

        .demo-account {
            background: #e7f3ff;
            color: #0066cc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #b3d7ff;
            font-size: 0.9rem;
        }

        .admin-demo {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
            margin-top: 10px;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .login-container {
                margin: 20px auto;
                padding: 30px 20px;
                max-width: 90%;
            }
            
            .title {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 10px auto;
                padding: 25px 15px;
                max-width: 95%;
            }
            
            .title {
                font-size: 1.6rem;
            }
            
            .back-btn {
                position: absolute;
                top: 10px;
                left: 10px;
                padding: 8px 12px;
                font-size: 0.9rem;
            }
        }

        html, body {
            overflow-x: hidden;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <a href="index.html" class="back-btn">← Kembali</a>
    
    <div class="login-container">
        <div class="logo-container">
            <img src="https://github.com/Afrizal236/kesehatan/blob/main/halodoc3588.jpg?raw=true" alt="Logo RamySehat" class="logo">
            <h1 class="title">Login</h1>
            <p class="subtitle">Masuk ke akun RamySehat Anda</p>
        </div>

        <div class="demo-account">
            <strong>Akun User:</strong><br>
            Email: ramydiaman@gmail.com<br>
            Password: Ramydiaman23
        </div>

        <div class="demo-account admin-demo">
            <strong>Akun Admin:</strong><br>
            Email: admin@ramysehat.com<br>
            Password: admin123
        </div>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="loginForm">
            <div class="form-group">
                <label for="username">Username (Opsional)</label>
                <input type="text" id="username" name="username" 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn" id="submitBtn">Masuk</button>
        </form>

        <div class="links">
            <a href="forgot_password.php" class="forgot-password-link">🔑 Lupa Password?</a>
            <p>Belum punya akun? <a href="signup.php">Daftar di sini</a></p>
        </div>
    </div>

    <script>
        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;

            if (!email || !password) {
                e.preventDefault();
                alert('Email dan password harus diisi!');
                return;
            }

            if (!validateEmail(email)) {
                e.preventDefault();
                alert('Format email tidak valid!');
                return;
            }

            // Add loading state
            const btn = document.getElementById('submitBtn');
            btn.textContent = 'Memproses...';
            btn.disabled = true;
        });

        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // Auto-fill demo accounts
        document.addEventListener('DOMContentLoaded', function() {
            const demoAccount = document.querySelector('.demo-account');
            const adminDemo = document.querySelector('.admin-demo');
            
            if (demoAccount) {
                demoAccount.addEventListener('click', function() {
                    document.getElementById('username').value = 'ramydiaman123';
                    document.getElementById('email').value = 'ramydiaman@gmail.com';
                    document.getElementById('password').value = 'Ramydiaman23';
                });
                demoAccount.style.cursor = 'pointer';
                demoAccount.title = 'Klik untuk auto-fill';
            }

            if (adminDemo) {
                adminDemo.addEventListener('click', function() {
                    document.getElementById('username').value = 'admin';
                    document.getElementById('email').value = 'admin@ramysehat.com';
                    document.getElementById('password').value = 'admin123';
                });
                adminDemo.style.cursor = 'pointer';
                adminDemo.title = 'Klik untuk auto-fill admin';
            }
        });
    </script>
</body>
</html>