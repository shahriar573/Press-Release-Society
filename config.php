<?php
session_start();

// Simple config file with credentials for demo purposes.
// Replace with real DB credentials and remove hard-coded password for production.

$CONFIG = [
    'admin_user' => 'admin',
    // password: admin123 (bcrypt hashed)
    'admin_pass_hash' => password_hash('admin123', PASSWORD_DEFAULT),
    'db_host' => 'localhost',
    'db_user' => 'root',
    'db_pass' => '',  // Default XAMPP has no password
    'db_name' => 'dataflow',
];

// Database connection (mysqli)
$conn = new mysqli(
    $CONFIG['db_host'],
    $CONFIG['db_user'],
    $CONFIG['db_pass'],
    $CONFIG['db_name']
);

if ($conn->connect_error) {
    die('DB Connection failed: ' . $conn->connect_error);
}

// Set charset to UTF8
$conn->set_charset('utf8mb4');

?>
