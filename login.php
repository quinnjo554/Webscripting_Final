<?php if (!empty($error_msg)): ?>
<div class="alert alert-danger" role="alert">
    <?php echo $error_msg; ?>
    
    <!-- Debug info -->
    <?php if (isset($_SESSION['debug_info'])): ?>
    <div class="mt-2 small">
        <strong>Debug Info:</strong> <?php echo $_SESSION['debug_info']; ?>
    </div>
    <?php unset($_SESSION['debug_info']); endif; ?>

</div>
<?php endif; ?>
<?php
// Start session
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect based on role
    if ($_SESSION['user_role'] == 'instructor') {
        header('Location: instructor_dashboard.php');
    } else {
        header('Location: student_dashboard.php');
    }
    exit;
}

// Get any error messages
$error_msg = isset($_SESSION['error']) ? $_SESSION['error'] : '';
// Clear the error message after displaying it
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Scheduling System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/login.css"/>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-form-section">
                <h1 class="login-title">Login</h1>
                <p class="login-subtitle">Welcome to the Scheduling System</p>
                
                <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_msg; ?>
                </div>
                <?php endif; ?>
                
                <form id="loginForm" action="backend/login_process.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="you@example.com" required>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label for="password" class="form-label">Password</label>
                        </div>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                    <button type="submit" class="btn btn-login">LOGIN</button>
                </form>
                
                <div class="text-center mt-3">
                    <p class="text-muted small">For demo purposes:</p>
                    <p class="text-muted small">Instructor: instructor@example.com / password123</p>
                    <p class="text-muted small">Student: student1@example.com / password123</p>
                </div>
            </div>
            
            <div class="login-image-section">
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>
