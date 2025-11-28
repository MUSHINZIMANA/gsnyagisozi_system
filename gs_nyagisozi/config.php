<?php
// config.php - Database Connection using PDO with best practices

// Database credentials - update with your settings
$host = 'localhost';
$dbname = 'gs_nyagisozi';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

// PDO options for error handling and security
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,           // Use exceptions for errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,      // Fetch results as associative arrays
    PDO::ATTR_EMULATE_PREPARES => false,                   // Disable emulated prepared statements for safety
];

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $pdo = new PDO($dsn, $username, $password, $options);
    // Uncomment to test connection
    // echo "Database connected successfully!";
} catch (PDOException $e) {
    // Output error message and stop script execution on failure
    die("Database connection failed: " . $e->getMessage());
}
?>
