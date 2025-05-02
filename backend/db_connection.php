<?php
// Database connection parameters
// Create connection
define('DB_SERVER', 'rei.cs.ndsu.nodak.edu');
define('DB_USERNAME', 'quinn_johnson_1_371s25');
define('DB_PASSWORD', 'DReNLvR19N0!');
define('DB_NAME', 'quinn_johnson_1_db371s25');


$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Uncomment this line for debugging connection issues
// echo "Connected successfully";
?>
