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

// Check if any parameters are provided
if (empty($_GET)) {
    logMessage("No parameters provided.");
    echo "Nothing to see here.";
    exit();
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

// Determine format from the 'f' parameter or use default
$format = isset($_GET['f']) ? escapeshellarg($_GET['f']) : escapeshellarg('bv+ba/best');

// Prepare the yt-dlp command
$command = 'yt-dlp --get-url -f ' . $format . ' ' . $videoUrl . ' 2>&1'; // Redirect stderr to stdout
logMessage("Executing command: $command");

// Execute the command and capture the output URL and error message
$output = shell_exec($command);
$output = is_string($output) ? trim($output) : '';

// Check if an output URL was returned
if (empty($output)) {
    // Log the error and output the error message
    logMessage("Error: yt-dlp failed to fetch the URL for $videoUrl. Output:\n$output");
    
    // Display the error message to the user
    header("HTTP/1.1 500 Internal Server Error");
    echo "Error: Unable to fetch the download URL. <br> Details: " . nl2br(htmlspecialchars($output));
    exit();
}

// Log success and redirect to the output URL
logMessage("Success: Redirecting to $output.");
header("Location: " . $output);
exit();
