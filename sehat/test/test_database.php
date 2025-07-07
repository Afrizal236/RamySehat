<?php
// test_database.php - File untuk test koneksi database dan tabel
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Database - RamySehat</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
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
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border: 1px solid #f5c6cb;
        }
        .info {
            background: #e7f3ff;
            color: #0066cc;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border: 1px solid #b3d7ff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Test Database Connection</h1>
        
        <?php
        // Test 1: Basic PHP Info
        echo '<div class="info"><strong>PHP Version:</strong> ' . PHP_VERSION . '</div>';
        
        // Test 2: Check if MySQLi or PDO is available
        if (extension_loaded('pdo_mysql')) {
            echo '<div class="success">✓ PDO MySQL extension is loaded</div>';
        } else {
            echo '<div class="error">✗ PDO MySQL extension is NOT loaded</div>';
        }
        
        // Test 3: Database connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "kesehatan_db";
        
        try {
            $pdo = new PDO("mysql:host=$servername", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo '<div class="success">✓ Successfully connected to MySQL server</div>';
            
            // Test 4: Check if database exists
            $stmt = $pdo->query("SHOW DATABASES LIKE 'kesehatan_db'");
            if ($stmt->rowCount() > 0) {
                echo '<div class="success">✓ Database "kesehatan_db" exists</div>';
                
                // Connect to the specific database
                $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Test 5: Check if table exists
                $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
                if ($stmt->rowCount() > 0) {
                    echo '<div class="success">✓ Table "users" exists</div>';
                    
                    // Test 6: Show table structure
                    echo '<h3>Table Structure:</h3>';
                    $stmt = $pdo->query("DESCRIBE users");
                    echo '<table>';
                    echo '<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>';
                    while ($row = $stmt->fetch()) {
                        echo '<tr>';
                        echo '<td>' . $row['Field'] . '</td>';
                        echo '<td>' . $row['Type'] . '</td>';
                        echo '<td>' . $row['Null'] . '</td>';
                        echo '<td>' . $row['Key'] . '</td>';
                        echo '<td>' . $row['Default'] . '</td>';
                        echo '<td>' . $row['Extra'] . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                    
                    // Test 7: Count users
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
                    $count = $stmt->fetch()['count'];
                    echo '<div class="info">Total users in database: ' . $count . '</div>';
                    
                    // Test 8: Show existing users (without passwords)
                    if ($count > 0) {
                        echo '<h3>Existing Users:</h3>';
                        $stmt = $pdo->query("SELECT id, username, email, created_at FROM users ORDER BY id");
                        echo '<table>';
                        echo '<tr><th>ID</th><th>Username</th><th>Email</th><th>Created At</th></tr>';
                        while ($row = $stmt->fetch()) {
                            echo '<tr>';
                            echo '<td>' . $row['id'] . '</td>';
                            echo '<td>' . $row['username'] . '</td>';
                            echo '<td>' . $row['email'] . '</td>';
                            echo '<td>' . $row['created_at'] . '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                    }
                    
                } else {
                    echo '<div class="error">✗ Table "users" does NOT exist</div>';
                    echo '<div class="info">You need to run the SQL commands to create the table.</div>';
                }
                
            } else {
                echo '<div class="error">✗ Database "kesehatan_db" does NOT exist</div>';
                echo '<div class="info">You need to create the database first.</div>';
            }
            
        } catch(PDOException $e) {
            echo '<div class="error">✗ Connection failed: ' . $e->getMessage() . '</div>';
        }
        ?>
        
        <h3>Next Steps:</h3>
        <div class="info">
            <p>If you see any errors above, follow these steps:</p>
            <ol>
                <li>Make sure XAMPP/WAMP is running</li>
                <li>Open phpMyAdmin (usually http://localhost/phpmyadmin)</li>
                <li>Create a new database called "kesehatan_db"</li>
                <li>Run the SQL commands from the database_setup.sql file</li>
                <li>Refresh this page to test again</li>
            </ol>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="signup.php" class="btn">Go to Sign Up</a>
            <a href="login.php" class="btn">Go to Login</a>
            <a href="index.html" class="btn">Back to Home</a>
        </div>
    </div>
</body>
</html>