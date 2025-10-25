<?php
// Test database connection
header('Content-Type: application/json');

try {
    require_once 'database/config.php';
    
    // Test basic query
    $result = $conn->query("SELECT DATABASE() as current_db");
    $db = $result->fetch_assoc();
    
    // Test if stored procedures exist
    $procs = $conn->query("SHOW PROCEDURE STATUS WHERE Db = 'press_release_db'");
    $procedures = [];
    while ($row = $procs->fetch_assoc()) {
        $procedures[] = $row['Name'];
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Database connection successful',
        'current_database' => $db['current_db'],
        'stored_procedures' => $procedures,
        'connection_info' => [
            'host' => $CONFIG['db_host'],
            'user' => $CONFIG['db_user'],
            'database' => $CONFIG['db_name']
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
