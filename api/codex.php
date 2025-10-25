<?php
/**
 * Media Codex API - Advanced Press Release Fetching & Serialization
 * Uses stored procedures and functions for efficient data retrieval
 */

// Let's see the actual errors for now
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Centralized error handler
function send_json_error($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

// Helper function to execute stored procedures and get results
function execute_procedure($conn, $query) {
    if (!$conn->multi_query($query)) {
        send_json_error('Failed to execute stored procedure: ' . $conn->error);
    }
    
    // Fetch the first result set
    $data = [];
    if ($result = $conn->store_result()) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $result->free();
    }
    
    // Clear any remaining results
    while ($conn->more_results() && $conn->next_result()) {
        if ($res = $conn->store_result()) {
            $res->free();
        }
    }
    
    return $data;
}

try {
    require_once '../database/config.php';
} catch (Throwable $e) {
    send_json_error('Failed to load database configuration: ' . $e->getMessage(), 500);
}

// Check connection after including config
if ($conn->connect_error) {
    send_json_error('Database connection failed: ' . $conn->connect_error, 500);
}

// Get action parameter
$action = $_GET['action'] ?? 'weekly_codex';

try {
    $response = [];
    
    switch ($action) {
        case 'today':
            // Fetch today's press releases
            $releases = execute_procedure($conn, "CALL GetTodaysPressReleases()");
            $response = [
                'status' => 'success',
                'date' => date('Y-m-d'),
                'releases' => $releases
            ];
            break;
            
        case 'date_range':
            // Fetch releases for a date range
            $startDate = $conn->real_escape_string($_GET['start'] ?? date('Y-m-d', strtotime('-7 days')));
            $endDate = $conn->real_escape_string($_GET['end'] ?? date('Y-m-d'));
            
            $releases = execute_procedure($conn, "CALL GetPressReleasesByDateRange('$startDate', '$endDate')");
            
            $response = [
                'status' => 'success',
                'period' => ['start' => $startDate, 'end' => $endDate],
                'releases' => $releases
            ];
            break;
            
        case 'monthly_booklet':
            // Generate monthly media booklet
            $year = (int)($_GET['year'] ?? date('Y'));
            $month = (int)($_GET['month'] ?? date('m'));
            
            $releases = execute_procedure($conn, "CALL GenerateMonthlyBooklet($year, $month)");
            
            // Organize by week
            $booklet = [
                'status' => 'success',
                'booklet_info' => [
                    'title' => date('F Y', strtotime("$year-$month-01")),
                    'year' => $year,
                    'month' => $month,
                    'total_releases' => count($releases)
                ],
                'weeks' => []
            ];
            
            foreach ($releases as $release) {
                $week = $release['WeekNumber'];
                if (!isset($booklet['weeks'][$week])) {
                    $booklet['weeks'][$week] = [
                        'week_number' => $week,
                        'releases' => []
                    ];
                }
                $booklet['weeks'][$week]['releases'][] = $release;
            }
            
            $booklet['weeks'] = array_values($booklet['weeks']);
            $response = $booklet;
            break;
            
        case 'author_booklet':
            // Generate author-specific booklet
            $authorId = (int)($_GET['author_id'] ?? 1);
            
            $releases = execute_procedure($conn, "CALL GenerateAuthorBooklet($authorId)");
            
            // Get author info
            $authorQuery = $conn->query("SELECT Name, Designation, ContactInfo FROM Members WHERE MemberID = $authorId");
            $authorInfo = $authorQuery->fetch_assoc();
            
            $response = [
                'status' => 'success',
                'author' => $authorInfo,
                'releases' => $releases
            ];
            break;
            
        case 'dossier':
            // Get complete press release dossier
            $releaseId = (int)($_GET['release_id'] ?? 1);
            
            if (!$conn->query("CALL GetPressReleaseDossier($releaseId)")) {
                send_json_error('Failed to execute stored procedure: ' . $conn->error);
            }
            
            // Fetch main release info (first result set)
            $result1 = $conn->store_result();
            $mainInfo = $result1 ? $result1->fetch_assoc() : null;
            if ($result1) $result1->free();
            $conn->next_result();
            
            // Fetch distribution records (second result set)
            $result2 = $conn->store_result();
            $distributions = [];
            if ($result2) {
                while ($row = $result2->fetch_assoc()) {
                    $distributions[] = $row;
                }
                $result2->free();
            }
            $conn->next_result();
            
            // Fetch related events (third result set)
            $result3 = $conn->store_result();
            $events = [];
            if ($result3) {
                while ($row = $result3->fetch_assoc()) {
                    $events[] = $row;
                }
                $result3->free();
            }
            
            // Clear remaining results
            while ($conn->next_result()) {
                if ($res = $conn->store_result()) {
                    $res->free();
                }
            }
            
            $response = [
                'status' => 'success',
                'release' => $mainInfo,
                'distributions' => $distributions,
                'events' => $events,
                'impact_score' => calculateImpact($conn, $releaseId)
            ];
            break;
            
        case 'weekly_codex':
            // Generate weekly media codex
            $week = (int)($_GET['week'] ?? date('W'));
            $year = (int)($_GET['year'] ?? date('Y'));
            
            $entries = execute_procedure($conn, "CALL GenerateWeeklyCodex($week, $year)");
            
            $response = [
                'status' => 'success',
                'codex_info' => [
                    'title' => "Week $week Media Codex - $year",
                    'week' => $week,
                    'year' => $year,
                    'generated' => date('Y-m-d H:i:s')
                ],
                'entries' => $entries
            ];
            break;
            
        case 'thematic_codex':
            // Generate thematic codex
            $keyword = $_GET['keyword'] ?? '';
            
            if (empty($keyword)) {
                throw new Exception('Keyword parameter is required');
            }
            
            $keyword = $conn->real_escape_string($keyword);
            $entries = execute_procedure($conn, "CALL GenerateThematicCodex('$keyword')");

            // Manually calculate impact score and sort in PHP
            foreach ($entries as &$entry) {
                $entry['Relevance'] = calculateImpact($conn, $entry['ReleaseID']);
            }
            unset($entry); // Unset reference

            // Sort by Relevance DESC, then ReleaseDate DESC
            usort($entries, function($a, $b) {
                if ($a['Relevance'] == $b['Relevance']) {
                    return strtotime($b['ReleaseDate']) - strtotime($a['ReleaseDate']);
                }
                return $b['Relevance'] - $a['Relevance'];
            });
            
            $response = [
                'status' => 'success',
                'theme' => $keyword,
                'entries' => $entries
            ];
            break;
            
        case 'impact_scores':
            // Get all press releases with impact scores
            $query = "
                SELECT 
                    pr.ReleaseID,
                    pr.Title,
                    pr.ReleaseDate,
                    m.Name AS AuthorName,
                    CalculateImpactScore(pr.ReleaseID) AS ImpactScore
                FROM PressRelease pr
                LEFT JOIN Members m ON pr.MemberID = m.MemberID
                ORDER BY ImpactScore DESC
            ";
            $result = $conn->query($query);
            if (!$result) {
                send_json_error('Failed to execute query: ' . $conn->error);
            }
            
            $releases = [];
            while ($row = $result->fetch_assoc()) {
                $releases[] = $row;
            }
            
            $response = [
                'status' => 'success',
                'releases' => $releases
            ];
            break;
            
        case 'author_productivity':
            // Get author productivity ratings
            $query = "
                SELECT 
                    m.MemberID,
                    m.Name,
                    m.Designation,
                    GetAuthorProductivity(m.MemberID) AS ProductivityLevel,
                    COUNT(pr.ReleaseID) AS TotalReleases
                FROM Members m
                LEFT JOIN PressRelease pr ON m.MemberID = pr.MemberID
                GROUP BY m.MemberID, m.Name, m.Designation
                ORDER BY TotalReleases DESC
            ";
            $result = $conn->query($query);
            if (!$result) {
                send_json_error('Failed to execute query: ' . $conn->error);
            }
            
            $authors = [];
            while ($row = $result->fetch_assoc()) {
                $authors[] = $row;
            }
            
            $response = [
                'status' => 'success',
                'authors' => $authors
            ];
            break;
            
        case 'media_engagement':
            // Get media outlet engagement scores
            $outlets = execute_procedure($conn, "CALL UpdateMediaEngagement()");
            $response = [
                'status' => 'success',
                'outlets' => $outlets
            ];
            break;
            
        case 'archive_candidates':
            // Get releases ready for archiving
            $daysOld = (int)($_GET['days'] ?? 365);
            
            $releases = execute_procedure($conn, "CALL ArchiveOldReleases($daysOld)");
            
            $response = [
                'status' => 'success',
                'criteria' => "Older than $daysOld days",
                'releases' => $releases
            ];
            break;
            
        default:
            throw new Exception('Invalid action parameter');
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    send_json_error($e->getMessage());
}

function calculateImpact($conn, $releaseId) {
    $releaseId = (int)$releaseId;
    $result = $conn->query("SELECT CalculateImpactScore($releaseId) AS impact");
    if (!$result) return 0;
    $row = $result->fetch_assoc();
    return $row['impact'] ?? 0;
}

?>
