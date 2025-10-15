<?php
// API with real database queries (mysqli)
include 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$valid = ['Members','PressReleases','MediaOutlets','DistributionRecords','Events'];
$table = isset($_GET['table']) ? $_GET['table'] : null;

if (!$table || !in_array($table, $valid)) {
    echo json_encode(['error' => 'Invalid or missing table parameter', 'valid' => $valid]);
    exit;
}

// Ensure database connection exists
if (!isset($conn) || $conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed. Please run setup_db.php first.']);
    exit;
}

// Fetch data from database
$data = [];
$tableName = $conn->real_escape_string($table);
$sql = "SELECT * FROM `{$tableName}` ORDER BY id ASC";
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Convert date/datetime fields for display
        if ($table === 'PressReleases') {
            // Show only date for 'date' field (backwards compatible with old API structure)
            if (isset($row['published_at'])) {
                $row['date'] = date('Y-m-d', strtotime($row['published_at']));
            }
        } elseif ($table === 'DistributionRecords') {
            // Show date for 'date' field
            if (isset($row['sent_at'])) {
                $row['date'] = date('Y-m-d', strtotime($row['sent_at']));
            }
        } elseif ($table === 'Events') {
            // Show date for 'date' field
            if (isset($row['event_date'])) {
                $row['date'] = date('Y-m-d', strtotime($row['event_date']));
            }
        } elseif ($table === 'MediaOutlets') {
            // Show contact field (email or contact_person)
            if (isset($row['email'])) {
                $row['contact'] = $row['email'];
            }
        }
        
        $data[] = $row;
    }
    $result->free();
} else {
    echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    exit;
}

echo json_encode(['table'=>$table,'count'=>count($data),'rows'=>$data]);

