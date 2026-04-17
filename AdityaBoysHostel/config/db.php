<?php
// Improved Database Configuration with Reconnection
// Load configuration from separate file if available
if (file_exists(__DIR__ . '/db_config.php')) {
    include __DIR__ . '/db_config.php';
} else {
    // Default development settings (for local development only)
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'aditya_hostel';
    
    // Base URL - Update with your actual domain
    if (!defined('BASE_URL')) {
        define('BASE_URL', 'http://localhost/AdityaBoysHostel/');
    }
    if (!defined('CLEAN_URL')) {
        define('CLEAN_URL', 'http://localhost/AdityaBoysHostel/');
    }
    if (!defined('UPLOAD_PATH')) {
        define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/AdityaBoysHostel/uploads/');
    }
}

// Start session
session_start();

// Function to create database connection
function createConnection() {
    global $host, $username, $password, $database;
    
    try {
        $conn = new mysqli($host, $username, $password);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Create database if not exists
        $conn->query("CREATE DATABASE IF NOT EXISTS $database");
        $conn->select_db($database);
        
        // Set charset to utf8mb4
        $conn->set_charset("utf8mb4");
        
        // Set timeouts to prevent "gone away" errors
        $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
        $conn->options(MYSQLI_READ_DEFAULT_GROUP, "max_allowed_packet=16M");
        
        return $conn;
        
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        throw $e;
    }
}

// Create initial connection
$conn = createConnection();

// Function to reconnect if needed
function reconnectDatabase() {
    global $conn;
    try {
        if ($conn->ping()) {
            return $conn; // Connection is still alive
        }
    } catch (Exception $e) {
        // Connection is dead, create new one
    }
    
    $conn = createConnection();
    return $conn;
}

// Constants are now defined in the configuration section above

// Create uploads directory if not exists
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0777, true);
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Kolkata');
?>