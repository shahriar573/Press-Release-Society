<?php
/**
 * CRUD API for Press Release Council
 * Handles Create, Read, Update, Delete operations
 * 
 * Endpoints:
 * - POST   /api_crud.php?action=create&table=TableName
 * - PUT    /api_crud.php?action=update&table=TableName&id=123
 * - DELETE /api_crud.php?action=delete&table=TableName&id=123
 */

include '../database/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Check admin authentication
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized. Admin login required.']);
    exit;
}

// Ensure database connection
if (!isset($conn) || $conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed.']);
    exit;
}

// Valid tables (updated table names to match schema)
$validTables = ['Members', 'PressRelease', 'MediaOutlets', 'DistributionRecords', 'Events'];

// Get parameters
$action = $_GET['action'] ?? '';
$table = $_GET['table'] ?? '';
$id = $_GET['id'] ?? null;

// Get primary key name for each table
$pkMap = [
    'Members' => 'MemberID',
    'PressRelease' => 'ReleaseID',
    'MediaOutlets' => 'MediaID',
    'DistributionRecords' => 'DistributionID',
    'Events' => 'EventID'
];

$pkField = $pkMap[$table] ?? 'id';

// Validate table
if (!in_array($table, $validTables)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid table name', 'valid' => $validTables]);
    exit;
}

// Get request body for POST/PUT
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// If not JSON, try to get from POST
if (!$data) {
    $data = $_POST;
}

/**
 * CREATE operation
 */
if ($action === 'create') {
    if (empty($data)) {
        http_response_code(400);
        echo json_encode(['error' => 'No data provided']);
        exit;
    }

    // Build INSERT query based on table
    $fields = [];
    $values = [];
    $types = '';
    $params = [];

    // Define required/optional fields per table (updated to match new schema)
    switch ($table) {
        case 'Members':
            $allowedFields = ['Name', 'Designation', 'ContactInfo'];
            break;
        case 'PressRelease':
            $allowedFields = ['Title', 'Content', 'ReleaseDate', 'MemberID'];
            break;
        case 'MediaOutlets':
            $allowedFields = ['OutletName', 'ContactInfo', 'ContactPerson', 'MemberID', 'RelatedReleaseID'];
            break;
        case 'DistributionRecords':
            $allowedFields = ['ReleaseID', 'MediaID', 'DateSent', 'status'];
            break;
        case 'Events':
            $allowedFields = ['EventName', 'EventDate', 'Venue', 'RelatedReleaseID'];
            break;
        default:
            $allowedFields = [];
    }

    foreach ($data as $field => $value) {
        if (in_array($field, $allowedFields) && $value !== null && $value !== '') {
            $fields[] = "`{$field}`";
            $values[] = '?';
            $types .= 's'; // treat all as strings for simplicity
            $params[] = $value;
        }
    }

    if (empty($fields)) {
        http_response_code(400);
        echo json_encode(['error' => 'No valid fields provided']);
        exit;
    }

    $sql = "INSERT INTO `{$table}` (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ")";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Record created successfully',
            $pkField => $newId
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Insert failed: ' . $stmt->error]);
    }

    $stmt->close();
    exit;
}

/**
 * UPDATE operation
 */
if ($action === 'update') {
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID is required for update']);
        exit;
    }

    if (empty($data)) {
        http_response_code(400);
        echo json_encode(['error' => 'No data provided']);
        exit;
    }

    // Define updatable fields per table (updated to match new schema)
    switch ($table) {
        case 'Members':
            $allowedFields = ['Name', 'Designation', 'ContactInfo'];
            break;
        case 'PressRelease':
            $allowedFields = ['Title', 'Content', 'ReleaseDate', 'MemberID'];
            break;
        case 'MediaOutlets':
            $allowedFields = ['OutletName', 'ContactInfo', 'ContactPerson', 'MemberID', 'RelatedReleaseID'];
            break;
        case 'DistributionRecords':
            $allowedFields = ['ReleaseID', 'MediaID', 'DateSent', 'status'];
            break;
        case 'Events':
            $allowedFields = ['EventName', 'EventDate', 'Venue', 'RelatedReleaseID'];
            break;
        default:
            $allowedFields = [];
    }

    $sets = [];
    $types = '';
    $params = [];

    foreach ($data as $field => $value) {
        if ($field !== $pkField && in_array($field, $allowedFields)) {
            $sets[] = "`{$field}` = ?";
            $types .= 's';
            $params[] = $value;
        }
    }

    if (empty($sets)) {
        http_response_code(400);
        echo json_encode(['error' => 'No valid fields to update']);
        exit;
    }

    // Add ID to params
    $types .= 'i';
    $params[] = $id;

    $sql = "UPDATE `{$table}` SET " . implode(', ', $sets) . " WHERE `{$pkField}` = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Record updated successfully',
                'affected_rows' => $stmt->affected_rows
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Record not found or no changes made']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Update failed: ' . $stmt->error]);
    }

    $stmt->close();
    exit;
}

/**
 * DELETE operation
 */
if ($action === 'delete') {
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID is required for delete']);
        exit;
    }

    $sql = "DELETE FROM `{$table}` WHERE `{$pkField}` = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Record deleted successfully',
                'affected_rows' => $stmt->affected_rows
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Record not found']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Delete failed: ' . $stmt->error]);
    }

    $stmt->close();
    exit;
}

// Invalid action
http_response_code(400);
echo json_encode([
    'error' => 'Invalid action',
    'valid_actions' => ['create', 'update', 'delete']
]);
?>
