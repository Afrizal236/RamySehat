<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Contact System - RamySehat</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; text-decoration: none; display: inline-block; margin: 5px; }
        .btn:hover { background: #0056b3; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test Contact System</h1>
        
        <?php
        try {
            // Test 1: Check if contact_messages table exists
            $stmt = $pdo->query("SHOW TABLES LIKE 'contact_messages'");
            if ($stmt->rowCount() > 0) {
                echo '<div class="success">✓ Table "contact_messages" exists</div>';
                
                // Test 2: Show table structure
                echo '<h3>📋 Table Structure:</h3>';
                $stmt = $pdo->query("DESCRIBE contact_messages");
                echo '<table>';
                echo '<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>';
                while ($row = $stmt->fetch()) {
                    echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
                }
                echo '</table>';
                
                // Test 3: Count messages
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages");
                $count = $stmt->fetch()['count'];
                echo "<div class='success'>📊 Total messages in database: <strong>$count</strong></div>";
                
                // Test 4: Insert test message
                if (isset($_GET['test_insert'])) {
                    $stmt = $pdo->prepare("INSERT INTO contact_messages (full_name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
                    $result = $stmt->execute([
                        'Test User ' . date('H:i:s'), 
                        'test' . time() . '@example.com', 
                        '08123456789', 
                        'Test Subject - ' . date('Y-m-d H:i:s'), 
                        'This is a test message created at ' . date('Y-m-d H:i:s') . '. This message can be safely deleted.'
                    ]);
                    
                    if ($result) {
                        echo '<div class="success">✓ Test message inserted successfully!</div>';
                        echo '<script>setTimeout(function(){ window.location.href = "test_contact_system.php"; }, 2000);</script>';
                    } else {
                        echo '<div class="error">✗ Failed to insert test message</div>';
                    }
                }
                
                // Test 5: Show recent messages
                if ($count > 0) {
                    echo '<h3>📧 Recent Messages (Last 5):</h3>';
                    $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5");
                    echo '<table>';
                    echo '<tr><th>ID</th><th>Name</th><th>Email</th><th>Subject</th><th>Status</th><th>Date</th></tr>';
                    while ($row = $stmt->fetch()) {
                        echo '<tr>';
                        echo '<td>#' . $row['id'] . '</td>';
                        echo '<td>' . htmlspecialchars($row['full_name']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                        echo '<td>' . htmlspecialchars(substr($row['subject'], 0, 30)) . '...</td>';
                        echo '<td><span style="background: ' . ($row['status'] == 'unread' ? '#dc3545' : '#28a745') . '; color: white; padding: 3px 8px; border-radius: 10px; font-size: 0.8rem;">' . $row['status'] . '</span></td>';
                        echo '<td>' . date('d/m/Y H:i', strtotime($row['created_at'])) . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                }
                
            } else {
                echo '<div class="error">✗ Table "contact_messages" does NOT exist</div>';
                echo '<div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px;">Please run the SQL commands first to create the table!</div>';
            }
            
            // Test 6: Check if users table still exists
            $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
            if ($stmt->rowCount() > 0) {
                echo '<div class="success">✓ Table "users" still exists (not affected)</div>';
            } else {
                echo '<div class="error">✗ Table "users" missing!</div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="error">❌ Error: ' . $e->getMessage() . '</div>';
        }
        ?>
        
        <h3>🎯 Actions:</h3>
        <a href="?test_insert=1" class="btn">➕ Insert Test Message</a>
        <a href="contact.html" class="btn">📝 Test Contact Form</a>
        <a href="view_contact_messages.php" class="btn">👀 View Messages (Admin)</a>
        <a href="index.html" class="btn">🏠 Back to Home</a>
        
        <div style="background: #e7f3ff; color: #0066cc; padding: 20px; border-radius: 8px; margin-top: 30px;">
            <h4>📝 How to Test:</h4>
            <ol>
                <li>Run the SQL commands to create the contact_messages table</li>
                <li>Click "Insert Test Message" to add sample data</li>
                <li>Visit <a href="contact.html" style="color: #0066cc;">contact.html</a> and fill out the form</li>
                <li>Check <a href="view_contact_messages.php" style="color: #0066cc;">view_contact_messages.php</a> to see messages (Password: ramysehat123)</li>
            </ol>
        </div>
    </div>
</body>
</html>