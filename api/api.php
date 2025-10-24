<?php
// API with real database queries (mysqli)
include '../database/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$valid = ['Members','PressRelease','MediaOutlets','DistributionRecords','Events'];
$table = isset($_GET['table']) ? $_GET['table'] : null;

// Map tables to their primary keys
$pkMap = [
    'Members' => 'MemberID',
    'PressRelease' => 'ReleaseID',
    'MediaOutlets' => 'MediaID',
    'DistributionRecords' => 'DistributionID',
    'Events' => 'EventID'
];

if (!$table || !in_array($table, $valid)) {
    echo json_encode(['error' => 'Invalid or missing table parameter', 'valid' => $valid]);
    exit;
}

$pkField = $pkMap[$table];

// Get sorting parameters, with validation
$sortableColumns = [
    'Members' => ['MemberID', 'Name', 'Designation'],
    'PressRelease' => ['ReleaseID', 'Title', 'ReleaseDate'],
    'MediaOutlets' => ['MediaID', 'OutletName', 'ContactPerson'],
    'DistributionRecords' => ['DistributionID', 'DateSent', 'status'],
    'Events' => ['EventID', 'EventName', 'EventDate']
];
$nameColumns = [
    'Members' => 'Name',
    'PressRelease' => 'Title',
    'MediaOutlets' => 'OutletName',
    'Events' => 'EventName'
];

$sortBy = isset($_GET['sortBy']) && in_array($_GET['sortBy'], $sortableColumns[$table]) ? $_GET['sortBy'] : $pkField;
$sortOrder = isset($_GET['sortOrder']) && in_array(strtoupper($_GET['sortOrder']), ['ASC', 'DESC']) ? strtoupper($_GET['sortOrder']) : 'ASC';

// Get exclusion parameter
$exclude = isset($_GET['exclude']) ? $_GET['exclude'] : null;

// Ensure database connection exists
if (!isset($conn) || $conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed. Check your configuration.']);
    exit;
}

// Fetch data from database
$data = [];
$tableName = $conn->real_escape_string($table);
$sql = "SELECT * FROM `{$tableName}`";

// Add WHERE clause for exclusion if requested
if ($exclude && isset($nameColumns[$table])) {
    $nameCol = $nameColumns[$table];
    $escapedExclude = $conn->real_escape_string($exclude);
    $sql .= " WHERE `{$nameCol}` != '{$escapedExclude}'";
}

// Add ORDER BY clause
$sql .= " ORDER BY `{$sortBy}` {$sortOrder}";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Convert date/datetime fields for display and add backwards compatibility
        if ($table === 'PressRelease') {
            // Add 'date' alias for ReleaseDate
            if (isset($row['ReleaseDate'])) {
                $row['date'] = date('Y-m-d', strtotime($row['ReleaseDate']));
            }
            // Add backwards compatible field names for frontend
            if (isset($row['ReleaseID'])) $row['id'] = $row['ReleaseID'];
            if (isset($row['MemberID'])) $row['author_id'] = $row['MemberID'];
        } elseif ($table === 'DistributionRecords') {
            // Add 'date' alias for DateSent
            if (isset($row['DateSent'])) {
                $row['date'] = date('Y-m-d', strtotime($row['DateSent']));
            }
            // Add backwards compatible field names
            if (isset($row['DistributionID'])) $row['id'] = $row['DistributionID'];
            if (isset($row['ReleaseID'])) $row['release_id'] = $row['ReleaseID'];
            if (isset($row['MediaID'])) $row['media_outlet_id'] = $row['MediaID'];
        } elseif ($table === 'Events') {
            // Add 'date' alias for EventDate
            if (isset($row['EventDate'])) {
                $row['date'] = date('Y-m-d', strtotime($row['EventDate']));
            }
            // Add backwards compatible field names
            if (isset($row['EventID'])) $row['id'] = $row['EventID'];
            if (isset($row['RelatedReleaseID'])) $row['related_release_id'] = $row['RelatedReleaseID'];
        } elseif ($table === 'MediaOutlets') {
            // Add 'contact' alias for ContactInfo
            if (isset($row['ContactInfo'])) {
                $row['contact'] = $row['ContactInfo'];
            }
            // Add backwards compatible field names
            if (isset($row['MediaID'])) $row['id'] = $row['MediaID'];
            if (isset($row['OutletName'])) $row['name'] = $row['OutletName'];
            if (isset($row['MemberID'])) $row['member_id'] = $row['MemberID'];
            if (isset($row['RelatedReleaseID'])) $row['related_release_id'] = $row['RelatedReleaseID'];
        } elseif ($table === 'Members') {
            // Add backwards compatible field names
            if (isset($row['MemberID'])) $row['id'] = $row['MemberID'];
            if (isset($row['Designation'])) $row['role'] = $row['Designation'];
        }
        
        $data[] = $row;
    }
    $result->free();
} else {
    echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    exit;
}

echo json_encode(['table'=>$table,'count'=>count($data),'rows'=>$data]);

