<?php
// index.php

// Set the log file path
$logFile = __DIR__ . '/yt-dlp.log';

// Logging function
function logMessage($message) {
    global $logFile;
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

// Check if a 'url' parameter is provided
if (!isset($_GET['url'])) {
    logMessage("Error: Missing 'url' parameter.");
    header("HTTP/1.1 400 Bad Request");
    echo "Error: Please provide a 'url' parameter.";
    exit();
}

// Get the video URL from the 'url' query parameter
$videoUrl = escapeshellarg($_GET['url']);

// Collect all other query parameters as flags
$flags = [];
foreach ($_GET as $key => $value) {
    if ($key !== 'url') { // Skip the 'url' parameter
        $flags[] = escapeshellarg("--$key");
        $flags[] = escapeshellarg($value);
    }
}

// Prepare the yt-dlp command
$command = 'yt-dlp --get-url ' . implode(' ', $flags) . ' ' . $videoUrl;
logMessage("Executing command: $command");

// Execute the command and capture the output URL
$output = shell_exec($command);
$outputUrl = trim($output);

// Check if an output URL was returned
if (empty($outputUrl)) {
    logMessage("Error: yt-dlp failed to fetch the URL for $videoUrl.");
    header("HTTP/1.1 500 Internal Server Error");
    echo "Error: Unable to fetch the download URL.";
    exit();
}

// Log success and redirect to the output URL
logMessage("Success: Redirecting to $outputUrl.");
header("Location: " . $outputUrl);
exit();
