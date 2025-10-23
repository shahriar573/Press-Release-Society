<?php
// Unified Search API with scoring and simple relational joins
include 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Inputs
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$scope = isset($_GET['scope']) ? $_GET['scope'] : 'all'; // all | Members | PressReleases | MediaOutlets | Events | DistributionRecords
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

// Build queries per scope
$scopes = $scope === 'all' ? ['Members','PressReleases','MediaOutlets','Events','DistributionRecords'] : [$scope];

foreach ($scopes as $s) {
    switch ($s) {
        case 'Members': {
            $sql = "SELECT id, 'Members' AS type, name AS title, CONCAT(IFNULL(role,''), IF(IFNULL(role,'')!='',' · ',''), IFNULL(email,'')) AS snippet
                    FROM Members
                    WHERE name LIKE ? OR role LIKE ? OR email LIKE ?
                    ORDER BY id ASC
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
        case 'PressReleases': {
            $visibility = $isAdmin ? "1=1" : "status='Published'";
            $sql = "SELECT pr.id, 'PressReleases' AS type, pr.title AS title,
                           CONCAT(DATE_FORMAT(pr.published_at,'%Y-%m-%d'), ' · ', IFNULL(m.name,'Unknown')) AS snippet
                    FROM PressReleases pr
                    LEFT JOIN Members m ON m.id = pr.author_id
                    WHERE ($visibility) AND (pr.title LIKE ? OR pr.summary LIKE ? OR pr.content LIKE ? OR m.name LIKE ?)
                    ORDER BY pr.published_at DESC, pr.id DESC
                    LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssii', $like, $like, $like, $like, $limit, $offset);
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
            $sql = "SELECT id, 'MediaOutlets' AS type, name AS title,
                           CONCAT(IFNULL(contact_person,''), IF(IFNULL(contact_person,'')!='',' · ',''), IFNULL(email,'')) AS snippet
                    FROM MediaOutlets
                    WHERE name LIKE ? OR contact_person LIKE ? OR email LIKE ? OR outlet_type LIKE ?
                    ORDER BY id ASC
                    LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssii', $like, $like, $like, $like, $limit, $offset);
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
            $sql = "SELECT e.id, 'Events' AS type, e.title AS title,
                           CONCAT(DATE_FORMAT(e.event_date,'%Y-%m-%d'), ' · ', IFNULL(e.location,'')) AS snippet
                    FROM Events e
                    LEFT JOIN PressReleases pr ON pr.id = e.related_release_id
                    WHERE e.title LIKE ? OR e.description LIKE ? OR e.location LIKE ? OR pr.title LIKE ?
                    ORDER BY e.event_date DESC, e.id DESC
                    LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssii', $like, $like, $like, $like, $limit, $offset);
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
            $sql = "SELECT d.id, 'DistributionRecords' AS type,
                           CONCAT('Release #', d.release_id) AS title,
                           CONCAT(DATE_FORMAT(d.sent_at,'%Y-%m-%d'), ' · ', IFNULL(d.sent_to,'')) AS snippet
                    FROM DistributionRecords d
                    LEFT JOIN PressReleases pr ON pr.id = d.release_id
                    LEFT JOIN MediaOutlets mo ON mo.id = d.media_outlet_id
                    WHERE d.sent_to LIKE ? OR pr.title LIKE ? OR mo.name LIKE ?
                    ORDER BY d.sent_at DESC, d.id DESC
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
