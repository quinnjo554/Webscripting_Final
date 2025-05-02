<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once 'backend/db_connection.php';

// The password for demo accounts
$password = 'password123';

echo "<h1>Password Reset Tool</h1>";

// Check if PHP has the password_hash function
if (!function_exists('password_hash')) {
    echo "<div style='color:red; margin: 20px 0;'>";
    echo "<strong>Error:</strong> Your PHP installation doesn't have the password_hash function. ";
    echo "You need PHP 5.5.0 or higher for the built-in password functions.";
    echo "</div>";
    exit;
}

// Generate a new hash
$new_hash = password_hash($password, PASSWORD_DEFAULT);
echo "<p>Generated new hash: <code>$new_hash</code></p>";

// Verify the new hash works
if (password_verify($password, $new_hash)) {
    echo "<p style='color:green'>✓ Verification successful with new hash</p>";
} else {
    echo "<p style='color:red'>✗ Verification failed with new hash - there might be a PHP configuration issue</p>";
    exit;
}

// Update the database with the new hash
try {
    // Update all demo accounts
    $query = "UPDATE users SET password = ? WHERE email IN ('instructor@example.com', 'student1@example.com', 'student2@example.com')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $new_hash);
    
    if ($stmt->execute()) {
        $affected = $stmt->affected_rows;
        echo "<p style='color:green'>✓ Successfully updated $affected accounts with the new password hash!</p>";
    } else {
        echo "<p style='color:red'>✗ Failed to update passwords: " . $stmt->error . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='login.php'>Return to login page</a> and try logging in with:</p>";
echo "<ul>";
echo "<li>Email: instructor@example.com</li>";
echo "<li>Password: password123</li>";
echo "</ul>";
?>
