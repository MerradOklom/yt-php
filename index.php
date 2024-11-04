<?php
// Start output buffering at the very beginning to avoid any accidental output
ob_start();

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
    ob_end_flush(); // Flush the output buffer and send the output
    exit();
}

// Check if a 'url' parameter is provided
if (!isset($_GET['url'])) {
    logMessage("Error: Missing 'url' parameter.");
    header("HTTP/1.1 400 Bad Request");
    echo "Error: Please provide a 'url' parameter.";
    ob_end_flush(); // Flush the output buffer and send the output
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

// Trim the output and check if it is empty
$output = is_string($output) ? trim($output) : '';

// Check if an output URL was returned
if (empty($output)) {
    // Log the error and output the error message
    logMessage("Error: yt-dlp failed to fetch the URL for $videoUrl. Output:\n$output");
    
    // Display the error message to the user
    header("HTTP/1.1 500 Internal Server Error");
    echo "Error: Unable to fetch the download URL. <br> Details: " . nl2br(htmlspecialchars($output));
    ob_end_flush(); // Flush the output buffer and send the output
    exit();
}

// Log success and redirect to the output URL
logMessage("Success: Redirecting to $output.");

// Clear the output buffer before sending the redirect header
ob_end_clean();

// Redirect to the output URL
header("Location: " . $output);
exit();
