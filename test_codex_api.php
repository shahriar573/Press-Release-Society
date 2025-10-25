<?php
// Direct test of the codex API endpoint
// This will show us exactly what the API returns

$url = 'http://localhost/press_release_society/api/codex.php?action=weekly_codex&week=43&year=2025';

$context = stream_context_create([
    'http' => [
        'ignore_errors' => true
    ]
]);

$response = file_get_contents($url, false, $context);
$headers = $http_response_header;

echo "<h2>Response Headers:</h2>";
echo "<pre>";
print_r($headers);
echo "</pre>";

echo "<h2>Response Body:</h2>";
echo "<pre>";
echo htmlspecialchars($response);
echo "</pre>";

echo "<h2>JSON Validation:</h2>";
$json = json_decode($response, true);
if ($json === null) {
    echo "<p style='color: red;'>JSON parsing failed: " . json_last_error_msg() . "</p>";
    echo "<p>First 500 characters of response:</p>";
    echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>";
} else {
    echo "<p style='color: green;'>Valid JSON!</p>";
    echo "<pre>" . print_r($json, true) . "</pre>";
}
?>
