<?php
// generate_correct_hash.php
$password = 'Ramydiaman23';
$email = 'ramydiaman@gmail.com';
$username = 'ramydiaman123';

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

echo "<h2>Generate Hash Password yang Benar</h2>";
echo "<p><strong>Email:</strong> $email</p>";
echo "<p><strong>Username:</strong> $username</p>";
echo "<p><strong>Password:</strong> $password</p>";
echo "<p><strong>Hash yang Benar:</strong> $hashed_password</p>";

// Test verification
if (password_verify($password, $hashed_password)) {
    echo "<p style='color: green;'><strong>✓ Verification: SUCCESS</strong></p>";
} else {
    echo "<p style='color: red;'><strong>✗ Verification: FAILED</strong></p>";
}

echo "<h3>SQL Update Query:</h3>";
echo "<pre style='background: #f4f4f4; padding: 15px; border-radius: 5px;'>";
echo "UPDATE users SET password = '$hashed_password' WHERE email = '$email';";
echo "</pre>";
?>