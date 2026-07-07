<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $url = SERVER_URL . '/upload-to-server';

    // Build multipart form data manually for stream context
    $boundary = '--------------------------' . microtime(true);
    $content = '';

    // Add file part
    $content .= "--$boundary\r\n";
    $content .= 'Content-Disposition: form-data; name="file"; filename="' . $file['name'] . "\"\r\n";
    $content .= 'Content-Type: ' . $file['type'] . "\r\n\r\n";
    $content .= file_get_contents($file['tmp_name']) . "\r\n";

    // Final boundary
    $content .= "--$boundary--\r\n";

    $opts = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: multipart/form-data; boundary=$boundary\r\n" .
                         "Content-Length: " . strlen($content),
            'content' => $content,
        ]
    ];
    $context = stream_context_create($opts);
    $response = file_get_contents($url, false, $context);

    $resp = json_decode($response, true);
    $hash = $resp['hash'] ?? 'ERROR';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Upload Result</title>
    <style>
        body { background: #121212; color: #e0e0e0; font-family: sans-serif; padding: 20px; }
        a { color: #aaa; }
    </style>
</head>
<body>
    <h2>File Uploaded</h2>
    <p>Download hash: <strong><?= htmlspecialchars($hash) ?></strong></p>
    <a href="transfer.php">Back</a>
</body>
</html>
<?php
} else {
    header('Location: transfer.php');
}