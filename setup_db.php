<?php
/**
 * Database Setup Script
 * Run this once to create tables and populate sample data
 * 
 * Usage: Open in browser: http://localhost/press_release_society/setup_db.php
 * Or run via CLI: php setup_db.php
 */

// Database configuration (adjust for your XAMPP setup)
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';  // Default XAMPP has no password for root
$DB_NAME = 'dataflow';

echo "<!DOCTYPE html><html><head><title>Database Setup</title>";
echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:50px auto;padding:20px;}";
echo ".success{color:green;} .error{color:red;} .info{color:blue;} pre{background:#f5f5f5;padding:10px;}</style>";
echo "</head><body><h1>Press Release Council - Database Setup</h1>";

// Step 1: Connect to MySQL (without database selected)
echo "<h2>Step 1: Connecting to MySQL...</h2>";
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS);

if ($conn->connect_error) {
    echo "<p class='error'>❌ Connection failed: " . htmlspecialchars($conn->connect_error) . "</p>";
    echo "<p>Please check your database credentials in setup_db.php</p>";
    echo "</body></html>";
    exit;
}
echo "<p class='success'>✅ Connected to MySQL server</p>";

// Step 2: Create database if not exists
echo "<h2>Step 2: Creating database '{$DB_NAME}'...</h2>";
$sql = "CREATE DATABASE IF NOT EXISTS `{$DB_NAME}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql)) {
    echo "<p class='success'>✅ Database created or already exists</p>";
} else {
    echo "<p class='error'>❌ Error creating database: " . htmlspecialchars($conn->error) . "</p>";
    $conn->close();
    echo "</body></html>";
    exit;
}

// Step 3: Select the database
$conn->select_db($DB_NAME);
echo "<p class='success'>✅ Database '{$DB_NAME}' selected</p>";

// Step 4: Read and execute schema.sql
echo "<h2>Step 3: Running schema.sql...</h2>";
$schemaFile = __DIR__ . '/schema.sql';

if (!file_exists($schemaFile)) {
    echo "<p class='error'>❌ schema.sql not found at: " . htmlspecialchars($schemaFile) . "</p>";
    $conn->close();
    echo "</body></html>";
    exit;
}

$sql = file_get_contents($schemaFile);
if ($sql === false) {
    echo "<p class='error'>❌ Could not read schema.sql</p>";
    $conn->close();
    echo "</body></html>";
    exit;
}

// Execute multiple queries
$conn->multi_query($sql);

// Process all results
$queryCount = 0;
$errorCount = 0;
do {
    if ($result = $conn->store_result()) {
        $result->free();
    }
    if ($conn->error) {
        echo "<p class='error'>Query error: " . htmlspecialchars($conn->error) . "</p>";
        $errorCount++;
    }
    $queryCount++;
} while ($conn->more_results() && $conn->next_result());

echo "<p class='success'>✅ Schema executed ({$queryCount} statements processed)</p>";
if ($errorCount > 0) {
    echo "<p class='error'>⚠️ {$errorCount} errors encountered</p>";
}

// Step 5: Verify tables created
echo "<h2>Step 4: Verifying tables...</h2>";
$result = $conn->query("SHOW TABLES");
if ($result) {
    echo "<p class='success'>✅ Tables created:</p><ul>";
    while ($row = $result->fetch_array()) {
        echo "<li>" . htmlspecialchars($row[0]) . "</li>";
    }
    echo "</ul>";
    $result->free();
} else {
    echo "<p class='error'>❌ Could not verify tables</p>";
}

// Step 6: Show sample data counts
echo "<h2>Step 5: Sample data counts...</h2>";
$tables = ['Members', 'PressReleases', 'MediaOutlets', 'DistributionRecords', 'Events'];
echo "<ul>";
foreach ($tables as $table) {
    $result = $conn->query("SELECT COUNT(*) as cnt FROM `{$table}`");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<li><strong>{$table}:</strong> {$row['cnt']} records</li>";
        $result->free();
    }
}
echo "</ul>";

// Close connection
$conn->close();

echo "<hr>";
echo "<h2>✅ Setup Complete!</h2>";
echo "<p class='info'>Database Name: <strong>{$DB_NAME}</strong></p>";
echo "<p class='info'>Host: <strong>{$DB_HOST}</strong></p>";
echo "<p class='info'>User: <strong>{$DB_USER}</strong></p>";
echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Update <code>config.php</code> with these database credentials</li>";
echo "<li>Return to the <a href='index.php'>Press Release Dashboard</a></li>";
echo "<li>Delete or secure <code>setup_db.php</code> after setup</li>";
echo "</ol>";
echo "</body></html>";
?>
