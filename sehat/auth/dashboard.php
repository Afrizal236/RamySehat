<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
$email = $_SESSION['email'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - RamySehat</title>
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
            padding: 20px 0; /* Ubah dari display flex ke padding */
            position: relative;
            overflow-x: hidden; /* Hanya hide horizontal scroll */
        }

        .dashboard-container {
            background: white;
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 500px;
            overflow: hidden;
            position: relative;
            margin: 50px auto; /* Center dengan margin */
            min-height: auto; /* Allow natural height */
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dashboard-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .dashboard-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0.1) 75%, transparent 75%);
            background-size: 20px 20px;
            opacity: 0.3;
        }

        .welcome-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
            margin: 0 auto 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            position: relative;
            z-index: 1;
        }

        .dashboard-title {
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            position: relative;
            z-index: 1;
        }

        .dashboard-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
            position: relative;
            z-index: 1;
        }

        .dashboard-user-info {
            padding: 30px;
            background: white;
        }

        .info-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            margin-bottom: 15px;
            background: #f8f9fa;
            border-radius: 15px;
            border-left: 4px solid #007bff;
            transition: all 0.3s ease;
        }

        .info-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .info-card:last-child {
            margin-bottom: 0;
        }

        .info-label {
            font-weight: 600;
            color: #333;
            font-size: 1rem;
        }

        .info-value {
            color: #666;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .status-active {
            color: #28a745 !important;
            font-weight: 600 !important;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .status-active::before {
            content: '✓';
            background: #28a745;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .dashboard-actions {
            padding: 0 30px 30px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .dashboard-btn {
            padding: 15px 25px;
            border: none;
            border-radius: 15px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }

        .dashboard-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .dashboard-btn:hover::before {
            left: 100%;
        }

        .dashboard-btn-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
        }

        .dashboard-btn-primary:hover {
            background: linear-gradient(45deg, #0056b3, #004494);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
        }

        .dashboard-btn-service {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
        }

        .dashboard-btn-service:hover {
            background: linear-gradient(45deg, #218838, #1e7e34);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }

        .dashboard-btn-logout {
            background: linear-gradient(45deg, #dc3545, #c82333);
            color: white;
        }

        .dashboard-btn-logout:hover {
            background: linear-gradient(45deg, #c82333, #a71e2a);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
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

        /* Mobile Responsive */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .dashboard-container {
                margin: 20px auto;
                max-width: 90%;
            }
            
            .dashboard-header {
                padding: 30px 20px;
            }
            
            .dashboard-title {
                font-size: 1.8rem;
            }
            
            .dashboard-subtitle {
                font-size: 1rem;
            }
            
            .dashboard-user-info {
                padding: 20px;
            }
            
            .dashboard-actions {
                padding: 0 20px 20px;
            }

            .info-card {
                padding: 12px 15px;
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
                text-align: left;
            }

            .info-label {
                font-size: 0.9rem;
            }

            .info-value {
                font-size: 0.85rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 5px;
            }
            
            .dashboard-container {
                margin: 10px auto;
                max-width: 95%;
            }

            .welcome-avatar {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
            
            .dashboard-title {
                font-size: 1.6rem;
            }
            
            .dashboard-subtitle {
                font-size: 0.95rem;
            }

            .dashboard-btn {
                padding: 12px 20px;
                font-size: 0.9rem;
            }

            .info-card {
                padding: 10px 12px;
                margin-bottom: 10px;
            }

            .back-btn {
                position: absolute;
                top: 10px;
                left: 10px;
                padding: 8px 12px;
                font-size: 0.9rem;
            }
        }

        /* Floating particles animation */
        .dashboard-header::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(255,255,255,0.1) 2px, transparent 2px),
                radial-gradient(circle at 80% 70%, rgba(255,255,255,0.1) 2px, transparent 2px),
                radial-gradient(circle at 40% 80%, rgba(255,255,255,0.1) 1px, transparent 1px),
                radial-gradient(circle at 90% 20%, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 100px 100px, 150px 150px, 80px 80px, 120px 120px;
            animation: float 20s ease-in-out infinite;
            opacity: 0.6;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            25% {
                transform: translateY(-10px) rotate(90deg);
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
            }
            75% {
                transform: translateY(-10px) rotate(270deg);
            }
        }

        /* Ensure scrollability - YANG PALING PENTING */
        html, body {
            overflow-x: hidden;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <a href="index.html" class="back-btn">← Kembali</a>
    
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="welcome-avatar"><?php echo strtoupper(substr($username, 0, 1)); ?></div>
            <h1 class="dashboard-title">Selamat Datang!</h1>
            <p class="dashboard-subtitle">Anda berhasil masuk ke RamySehat</p>
        </div>
        
        <div class="dashboard-user-info">
            <div class="info-card">
                <div class="info-label">Username:</div>
                <div class="info-value"><?php echo htmlspecialchars($username); ?></div>
            </div>
            <div class="info-card">
                <div class="info-label">Email:</div>
                <div class="info-value"><?php echo htmlspecialchars($email); ?></div>
            </div>
            <div class="info-card">
                <div class="info-label">Status:</div>
                <div class="info-value status-active">Aktif</div>
            </div>
        </div>

        <div class="dashboard-actions">
            <a href="index.html" class="dashboard-btn dashboard-btn-primary">
                <span>🏠</span> Kembali ke Home
            </a>
            <a href="bot.html" class="dashboard-btn dashboard-btn-service">
                <span>🩺</span> Mulai Konsultasi
            </a>
            <a href="logout.php" class="dashboard-btn dashboard-btn-logout">
                <span>🚪</span> Logout
            </a>
        </div>
    </div>
</body>
</html>