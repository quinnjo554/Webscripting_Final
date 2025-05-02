<?php
// Start session
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'debug.log'); // This will create a log file in the same directory
error_reporting(E_ALL);
session_start();

// Include database connection
require_once 'db_connection.php';

// Debug mode - set to true to see detailed errors
$debug = true;

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get form data and sanitize
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password']; // Will be verified with password_verify, no need to sanitize
    
    if ($debug) {
        error_log("Login attempt - Email: $email");
    }
    
    // Basic validation
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required.";
        if ($debug) error_log("Login failed - Empty fields");
        header('Location: ../login.php');
        exit;
    }
    
    try {
        // Query to find the user
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Check if user exists
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if ($debug) {
                error_log("User found - User ID: " . $user['user_id'] . ", Role: " . $user['role']);
                error_log("Stored Password Hash: " . $user['password']);
                error_log("Entered Password: " . $password);
            }
            
            // Verify password
            $is_password_correct = password_verify($password, $user['password']);
            if ($debug) {
                error_log("Password verification result: " . ($is_password_correct ? "Success" : "Failure"));
                error_log("hash type: " . gettype($user['password']));
                error_log("password type: " . gettype($password));
            }
            if (password_verify($password, $user['password'])) {
                if ($debug) error_log("Password verified successfully");
                
                // Password correct, set up session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['full_name'];
                
                if ($debug) {
                    error_log("Session variables set: user_id=" . $_SESSION['user_id'] . 
                             ", username=" . $_SESSION['username'] . 
                             ", user_role=" . $_SESSION['user_role'] . 
                             ", user_name=" . $_SESSION['user_name']);
                }
                
                // Redirect based on role
                if ($user['role'] == 'instructor') {
                    if ($debug) error_log("Redirecting to instructor dashboard");
                    header('Location: ../instructor_dashboard.php');
                } else {
                    if ($debug) error_log("Redirecting to student dashboard");
                    header('Location: ../student_dashboard.php');
                }
                exit;
            } else {
                // Password incorrect
                if ($debug) error_log("Password verification failed");
                $_SESSION['error'] = "Invalid email or password.";
                header('Location: ../login.php');
                exit;
            }
        } else {
            // User not found
            if ($debug) error_log("User not found with email: $email");
            $_SESSION['error'] = "Invalid email or password.";
            header('Location: ../login.php');
            exit;
        }
        
    } catch (Exception $e) {
        if ($debug) error_log("Database error: " . $e->getMessage());
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header('Location: ../login.php');
        exit;
    }
    
} else {
    // If not a POST request, redirect to login page
    if ($debug) error_log("Not a POST request, redirecting to login page");
    header('Location: ../login.php');
    exit;
}
?>
