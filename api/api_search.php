<?php
// Unified Search API with scoring and simple relational joins
include '../database/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Inputs
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$scope = isset($_GET['scope']) ? $_GET['scope'] : 'all'; // all | Members | PressRelease | MediaOutlets | Events | DistributionRecords
$limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 25;
$offset = isset($_GET['offset']) ? max(0, intval($_GET['offset'])) : 0;

if ($q === '') {
    echo json_encode(['error' => 'Missing query parameter q']);
    exit;
}

if (!isset($conn) || $conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Session-aware filter example: if not admin, hide Draft press releases
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];

// Escape like pattern
$like = '%' . $q . '%';

$results = [];
$total = 0;

// Helper to push results with a simple score
function push_result(&$results, $row, $baseScore, $matchWeight = 1) {
    $row['score'] = $baseScore * $matchWeight;
    $results[] = $row;
}

// Build queries per scope (updated to use new schema field names)
$scopes = $scope === 'all' ? ['Members','PressRelease','MediaOutlets','Events','DistributionRecords'] : [$scope];

foreach ($scopes as $s) {
    switch ($s) {
        case 'Members': {
            $sql = "SELECT MemberID AS id, 'Members' AS type, Name AS title, 
                           CONCAT(IFNULL(Designation,''), IF(IFNULL(Designation,'')!='',' · ',''), IFNULL(ContactInfo,'')) AS snippet
                    FROM Members
                    WHERE Name LIKE ? OR Designation LIKE ? OR ContactInfo LIKE ?
                    ORDER BY MemberID ASC
                    LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssii', $like, $like, $like, $limit, $offset);
            if ($stmt->execute() && ($res = $stmt->get_result())) {
                while ($r = $res->fetch_assoc()) {
                    push_result($results, $r, 5);
                }
                $res->free();
            }
            $stmt->close();
            break;
        }
        case 'PressRelease': {
            $sql = "SELECT pr.ReleaseID AS id, 'PressRelease' AS type, pr.Title AS title,
                           CONCAT(DATE_FORMAT(pr.ReleaseDate,'%Y-%m-%d'), ' · ', IFNULL(m.Name,'Unknown')) AS snippet
                    FROM PressRelease pr
                    LEFT JOIN Members m ON m.MemberID = pr.MemberID
                    WHERE pr.Title LIKE ? OR pr.Content LIKE ? OR m.Name LIKE ?
                    ORDER BY pr.ReleaseDate DESC, pr.ReleaseID DESC
                    LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssii', $like, $like, $like, $limit, $offset);
            if ($stmt->execute() && ($res = $stmt->get_result())) {
                while ($r = $res->fetch_assoc()) {
                    push_result($results, $r, 10);
                }
                $res->free();
            }
            $stmt->close();
            break;
        }
        case 'MediaOutlets': {
            $sql = "SELECT MediaID AS id, 'MediaOutlets' AS type, OutletName AS title,
                           CONCAT(IFNULL(ContactPerson,''), IF(IFNULL(ContactPerson,'')!='',' · ',''), IFNULL(ContactInfo,'')) AS snippet
                    FROM MediaOutlets
                    WHERE OutletName LIKE ? OR ContactPerson LIKE ? OR ContactInfo LIKE ?
                    ORDER BY MediaID ASC
                    LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssii', $like, $like, $like, $limit, $offset);
            if ($stmt->execute() && ($res = $stmt->get_result())) {
                while ($r = $res->fetch_assoc()) {
                    push_result($results, $r, 6);
                }
                $res->free();
            }
            $stmt->close();
            break;
        }
        case 'Events': {
            $sql = "SELECT e.EventID AS id, 'Events' AS type, e.EventName AS title,
                           CONCAT(DATE_FORMAT(e.EventDate,'%Y-%m-%d'), ' · ', IFNULL(e.Venue,'')) AS snippet
                    FROM Events e
                    LEFT JOIN PressRelease pr ON pr.ReleaseID = e.RelatedReleaseID
                    WHERE e.EventName LIKE ? OR e.Venue LIKE ? OR pr.Title LIKE ?
                    ORDER BY e.EventDate DESC, e.EventID DESC
                    LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssii', $like, $like, $like, $limit, $offset);
            if ($stmt->execute() && ($res = $stmt->get_result())) {
                while ($r = $res->fetch_assoc()) {
                    push_result($results, $r, 7);
                }
                $res->free();
            }
            $stmt->close();
            break;
        }
        case 'DistributionRecords': {
            $sql = "SELECT d.DistributionID AS id, 'DistributionRecords' AS type,
                           CONCAT('Release #', d.ReleaseID) AS title,
                           CONCAT(DATE_FORMAT(d.DateSent,'%Y-%m-%d'), ' · ', IFNULL(d.status,'')) AS snippet
                    FROM DistributionRecords d
                    LEFT JOIN PressRelease pr ON pr.ReleaseID = d.ReleaseID
                    LEFT JOIN MediaOutlets mo ON mo.MediaID = d.MediaID
                    WHERE d.status LIKE ? OR pr.Title LIKE ? OR mo.OutletName LIKE ?
                    ORDER BY d.DateSent DESC, d.DistributionID DESC
                    LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssii', $like, $like, $like, $limit, $offset);
            if ($stmt->execute() && ($res = $stmt->get_result())) {
                while ($r = $res->fetch_assoc()) {
                    push_result($results, $r, 4);
                }
                $res->free();
            }
            $stmt->close();
            break;
        }
        default:
            // ignore unknown scope
            break;
    }
}

// Simple scoring boost: prioritize exact title matches
foreach ($results as &$r) {
    if (isset($r['title']) && strcasecmp($r['title'], $q) === 0) {
        $r['score'] += 5;
    }
}
unset($r);

// Sort by score desc, then id desc
usort($results, function($a, $b) {
    if ($a['score'] == $b['score']) {
        return ($b['id'] <=> $a['id']);
    }
    return ($b['score'] <=> $a['score']);
});

$total = count($results);
$results = array_slice($results, 0, $limit); // slice final combined results

echo json_encode([
    'q' => $q,
    'scope' => $scope,
    'count' => count($results),
    'total' => $total,
    'rows' => $results
]);
