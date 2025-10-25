<?php
/**
 * API for demonstrating JOIN operations.
 * Focuses on a RIGHT JOIN between Members and PressReleases.
 * 
 * This endpoint returns all members and nests their associated press releases.
 * This structure "cleverly hides" non-matching context by presenting an empty
 * array for members without press releases, rather than showing NULL values.
 */

include '../database/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Check for database connection
if (!isset($conn) || $conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed. Check your configuration.']);
    exit;
}

// The query uses a LEFT JOIN on Members to achieve the same result as a RIGHT JOIN on PressRelease.
// This ensures all members are returned, regardless of whether they have authored a release.
$sql = "
    SELECT 
        m.MemberID, 
        m.Name, 
        m.ContactInfo, 
        m.Designation,
        p.ReleaseID, 
        p.Title, 
        p.ReleaseDate
    FROM 
        Members m
    LEFT JOIN 
        PressRelease p ON m.MemberID = p.MemberID
    ORDER BY 
        m.Name, p.ReleaseDate DESC;
";

$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'SQL query failed: ' . $conn->error]);
    exit;
}

// Process the results to group press releases by member
$members = [];
while ($row = $result->fetch_assoc()) {
    $memberID = $row['MemberID'];

    // If we haven't seen this member yet, add them to the array
    if (!isset($members[$memberID])) {
        $members[$memberID] = [
            'MemberID' => $memberID,
            'Name' => $row['Name'],
            'ContactInfo' => $row['ContactInfo'],
            'Designation' => $row['Designation'],
            'PressReleases' => [] // Initialize the press releases array
        ];
    }

    // If there is a press release associated with this row, add it to the member's list
    if ($row['ReleaseID'] !== null) {
        $members[$memberID]['PressReleases'][] = [
            'ReleaseID' => $row['ReleaseID'],
            'Title' => $row['Title'],
            'ReleaseDate' => $row['ReleaseDate']
        ];
    }
}

// Close the database connection
$conn->close();

// Return the structured data, re-indexed as a simple array
echo json_encode(array_values($members));

?>
