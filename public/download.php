<?php
include 'config.php';

// Validate the hash
if (!isset($_GET['hash']) || !preg_match('/^[A-Z0-9]{6}$/', strtoupper($_GET['hash']))) {
    header('Location: transfer.php');
    exit;
}

$hash = strtoupper($_GET['hash']);
$url = SERVER_URL . '/download-from-server/' . $hash;

// Fetch the file from the Node server, ignoring HTTP errors so we can inspect headers
$context = stream_context_create(['http' => ['method' => 'GET', 'ignore_errors' => true]]);
$response = @file_get_contents($url, false, $context);

if ($response === false) {
    header('Location: transfer.php');
    exit;
}

// Get response headers (PHP 8.2+)
$responseHeaders = http_get_last_response_headers();

// Extract Content-Type and Content-Disposition from the Node server's response
$contentType = 'application/octet-stream';
$contentDisposition = null;

if ($responseHeaders) {
    foreach ($responseHeaders as $header) {
        $headerLower = strtolower($header);
        if (strpos($headerLower, 'content-type:') === 0) {
            $contentType = trim(substr($header, strlen('content-type:')));
        } elseif (strpos($headerLower, 'content-disposition:') === 0) {
            $contentDisposition = trim(substr($header, strlen('content-disposition:')));
        }
    }
}

// If no Content-Disposition header, the file was not found – redirect back
if (empty($contentDisposition)) {
    header('Location: transfer.php');
    exit;
}

// Send the correct headers and file content
header('Content-Type: ' . $contentType);
header('Content-Disposition: ' . $contentDisposition);
echo $response;