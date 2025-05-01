<?php
// Start session
session_start();

// Include database connection
require_once 'db_connection.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get form data and sanitize
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password']; // Will be verified with password_verify, no need to sanitize
    
    // Basic validation
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required.";
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
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password correct, set up session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['full_name'];
                
                // Redirect based on role
                if ($user['role'] == 'instructor') {
                    header('Location: ../instructor_dashboard.php');
                } else {
                    header('Location: ../student_dashboard.php');
                }
                exit;
            } else {
                // Password incorrect
                $_SESSION['error'] = "Invalid email or password.";
                header('Location: ../login.php');
                exit;
            }
        } else {
            // User not found
            $_SESSION['error'] = "Invalid email or password.";
            header('Location: ../login.php');
            exit;
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header('Location: ../login.php');
        exit;
    }
    
} else {
    // If not a POST request, redirect to login page
    header('Location: ../login.php');
    exit;
}
?>
