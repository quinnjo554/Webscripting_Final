<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function check_login() {
    if (!isset($_SESSION['user_id'])) {
        // User not logged in, redirect to login page
        header("Location: login.php");
        exit;
    }
}

function check_instructor() {
    check_login(); // First check if logged in
    
    if ($_SESSION['user_role'] !== 'instructor') {
        // User is not an instructor, redirect to appropriate page
        header("Location: unauthorized.php");
        exit;
    }
}

// Check if user is a student
function check_student() {
    check_login(); // First check if logged in
    
    if ($_SESSION['user_role'] !== 'student') {
        // User is not a student, redirect to appropriate page
        header("Location: unauthorized.php");
        exit;
    }
}
?>
