<?php
// Start output buffering
ob_start();

// Enable detailed error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set the log file path
$logFile = __DIR__ . '/yt-dlp.log';
$phpErrorLog = __DIR__ . '/php-error.log';
ini_set('log_errors', 1);
ini_set('error_log', $phpErrorLog);

// Logging function
function logMessage($message) {
    global $logFile;
    $timestamp = date("Y-m-d H:i:s");
    $logEntry = "[$timestamp] $message" . PHP_EOL;
    $file = fopen($logFile, 'a');
    if ($file) {
        flock($file, LOCK_EX); // Exclusive lock to avoid race conditions
        fwrite($file, $logEntry);
        flock($file, LOCK_UN); // Release lock
        fclose($file);
    }
}

// Validate the input URL
function validateUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

// Check if any parameters are provided
if (empty($_GET)) {
    logMessage("No parameters provided.");
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(["error" => "No parameters provided."]);
    ob_end_flush();
    exit();
}

// Check if a 'url' parameter is provided
if (!isset($_GET['url']) || !validateUrl($_GET['url'])) {
    logMessage("Error: Missing or invalid 'url' parameter.");
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(["error" => "Invalid or missing 'url' parameter."]);
    ob_end_flush();
    exit();
}

// Get the video URL from the 'url' query parameter
$videoUrl = escapeshellarg($_GET['url']);

// Determine format from the 'f' parameter or use default
$format = isset($_GET['f']) ? escapeshellarg($_GET['f']) : escapeshellarg('bv+ba/best');

// Prepare the yt-dlp command
$command = 'yt-dlp --get-url -f ' . $format . ' ' . $videoUrl . ' 2>&1';
logMessage("Executing command: $command");

// Execute the command and capture the output and exit status
$output = shell_exec($command);
$exitStatus = shell_exec("echo $?");

// Log the exit status and raw output for debugging purposes
logMessage("yt-dlp exit status: $exitStatus");
logMessage("Raw yt-dlp output: $output");

// Handle potential command execution failure
if ($output === null || $exitStatus != 0) {
    logMessage("Error: Command execution failed or returned non-zero status.");
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(["error" => "Command execution failed.", "details" => $output]);
    ob_end_flush();
    exit();
}

// Trim the output and check if it is empty
$output = is_string($output) ? trim($output) : '';

// Check if an output URL was returned
if (empty($output)) {
    logMessage("Error: yt-dlp failed to fetch the URL for $videoUrl. Output:\n$output");
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode([
        "error" => "Unable to fetch the download URL.",
        "details" => nl2br(htmlspecialchars($output))
    ]);
    ob_end_flush();
    exit();
}

// Validate the output URL before redirecting
if (!filter_var($output, FILTER_VALIDATE_URL)) {
    logMessage("Error: Invalid output URL returned: $output");
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode([
        "error" => "Invalid output URL returned.",
        "raw_output" => $output
    ]);
    ob_end_flush();
    exit();
}

// Log success and redirect to the output URL
logMessage("Success: Redirecting to $output.");
ob_end_clean(); // Clear the output buffer before sending the redirect header

// Redirect to the output URL
header("Location: " . $output);
exit();
