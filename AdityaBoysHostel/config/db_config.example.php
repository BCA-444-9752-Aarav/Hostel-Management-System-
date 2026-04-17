<?php
// Database Configuration Template
// Copy this file to db_config.php and update with your credentials

$host = 'localhost';
$username = 'your_db_username';
$password = 'your_db_password';
$database = 'aditya_hostel';

// Base URL - Update with your domain
define('BASE_URL', 'https://yourdomain.com/AdityaBoysHostel/');
define('CLEAN_URL', 'https://yourdomain.com/AdityaBoysHostel/');

// Upload Path - Update if needed
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/AdityaBoysHostel/uploads/');

// Email Configuration (Optional)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your_email@gmail.com');
define('SMTP_PASSWORD', 'your_email_password');

// Security Settings
define('ENCRYPTION_KEY', 'your_32_character_encryption_key_here');

// Error Reporting - Set to 0 for production
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Kolkata');
?>
