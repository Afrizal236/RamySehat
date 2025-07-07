<?php
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: index.html');
    exit();
}

// Mark message as read if requested
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
    $stmt->execute([$_GET['mark_read']]);
    header('Location: view_contact_messages.php');
    exit();
}

// Delete message if requested
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: view_contact_messages.php');
    exit();
}

// Get all messages
$stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll();

// Count by status
$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM contact_messages GROUP BY status");
$statusCounts = [];
while ($row = $stmt->fetch()) {
    $statusCounts[$row['status']] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Contact Messages</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .header h1 {
            color: #007bff;
            margin: 0 0 15px 0;
            font-size: 2.5rem;
        }
        .admin-info {
            background: #e7f3ff;
            color: #0066cc;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #b3d7ff;
        }
        .stats {
            display: flex;
            gap: 20px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        .stat-card {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            flex: 1;
            min-width: 150px;
        }
        .stat-card.unread { background: linear-gradient(45deg, #dc3545, #c82333); }
        .stat-card.read { background: linear-gradient(45deg, #28a745, #20c997); }
        .stat-card.replied { background: linear-gradient(45deg, #6c757d, #5a6268); }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            display: block;
        }
        .message-card {
            background: white;
            margin-bottom: 25px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .message-card:hover {
            transform: translateY(-5px);
        }
        .message-header {
            background: #007bff;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .message-body {
            padding: 25px;
        }
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-unread { background: #dc3545; color: white; }
        .status-read { background: #28a745; color: white; }
        .status-replied { background: #6c757d; color: white; }
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        .btn:hover { 
            background: #0056b3; 
            transform: translateY(-2px);
        }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .logout-btn { background: #dc3545; }
        .logout-btn:hover { background: #c82333; }
        .message-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .detail-group {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #007bff;
        }
        .detail-group label {
            font-weight: bold;
            color: #007bff;
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        .detail-group span {
            color: #333;
            font-size: 1.1rem;
        }
        .detail-group a {
            color: #007bff;
            text-decoration: none;
        }
        .detail-group a:hover {
            text-decoration: underline;
        }
        .message-text {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #007bff;
            margin-top: 20px;
        }
        .message-text label {
            font-weight: bold;
            color: #007bff;
            display: block;
            margin-bottom: 10px;
            font-size: 1rem;
            text-transform: uppercase;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        @media (max-width: 768px) {
            .message-details { grid-template-columns: 1fr; }
            .message-header { flex-direction: column; text-align: center; }
            .stats { flex-direction: column; }
            .header h1 { font-size: 2rem; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔧 Admin Dashboard</h1>
        <div class="admin-info">
            <strong>👤 Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</strong><br>
            <small>Email: <?php echo htmlspecialchars($_SESSION['email']); ?> | Role: Admin</small>
        </div>
        <p style="color: #666; font-size: 1.1rem;">Kelola pesan dari form contact website RamySehat</p>
        
        <div class="stats">
            <div class="stat-card">
                <span class="stat-number"><?php echo count($messages); ?></span>
                <span>Total Messages</span>
            </div>
            <div class="stat-card unread">
                <span class="stat-number"><?php echo $statusCounts['unread'] ?? 0; ?></span>
                <span>Unread</span>
            </div>
            <div class="stat-card read">
                <span class="stat-number"><?php echo $statusCounts['read'] ?? 0; ?></span>
                <span>Read</span>
            </div>
            <div class="stat-card replied">
                <span class="stat-number"><?php echo $statusCounts['replied'] ?? 0; ?></span>
                <span>Replied</span>
            </div>
        </div>
        
        <div style="text-align: right; margin-top: 20px;">
            <a href="index.html" class="btn">🏠 Home</a>
            <a href="dashboard.php" class="btn">👤 User Dashboard</a>
            <a href="?logout=1" class="btn logout-btn">🚪 Logout</a>
        </div>
    </div>

    <?php if (empty($messages)): ?>
        <div class="message-card">
            <div class="message-body empty-state">
                <h3>📭 Belum ada pesan masuk</h3>
                <p>Pesan dari form contact akan muncul di sini.</p>
                <a href="contact.html" class="btn" style="margin-top: 20px;">Test Contact Form</a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($messages as $msg): ?>
            <div class="message-card">
                <div class="message-header">
                    <div>
                        <h3 style="margin: 0; font-size: 1.3rem;"><?php echo htmlspecialchars($msg['subject']); ?></h3>
                        <small style="opacity: 0.9;">📅 <?php echo date('d M Y, H:i', strtotime($msg['created_at'])); ?> • ID: #<?php echo $msg['id']; ?></small>
                    </div>
                    <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                        <span class="status-badge status-<?php echo $msg['status']; ?>">
                            <?php echo ucfirst($msg['status']); ?>
                        </span>
                        <div>
                            <?php if ($msg['status'] === 'unread'): ?>
                                <a href="?mark_read=<?php echo $msg['id']; ?>" class="btn btn-success">✓ Mark as Read</a>
                            <?php endif; ?>
                            <a href="?delete=<?php echo $msg['id']; ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus pesan ini?')">🗑️ Delete</a>
                        </div>
                    </div>
                </div>
                <div class="message-body">
                    <div class="message-details">
                        <div class="detail-group">
                            <label>👤 Nama Lengkap</label>
                            <span><?php echo htmlspecialchars($msg['full_name']); ?></span>
                        </div>
                        <div class="detail-group">
                            <label>📧 Email</label>
                            <span><a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>"><?php echo htmlspecialchars($msg['email']); ?></a></span>
                        </div>
                        <div class="detail-group">
                            <label>📞 Telepon</label>
                            <span><a href="tel:<?php echo htmlspecialchars($msg['phone']); ?>"><?php echo htmlspecialchars($msg['phone']); ?></a></span>
                        </div>
                        <div class="detail-group">
                            <label>⏰ Status</label>
                            <span class="status-badge status-<?php echo $msg['status']; ?>" style="padding: 5px 10px;">
                                <?php echo ucfirst($msg['status']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="message-text">
                        <label>💬 Pesan</label>
                        <p style="margin: 0; line-height: 1.6; color: #333;"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>