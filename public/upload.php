<?php
include 'config.php';

function convertToBytes(string $value): int {
    $value = trim($value);
    if ($value === '') return 0;
    $last = strtolower($value[strlen($value)-1]);
    $num = (int) $value;
    switch ($last) {
        case 'g': $num *= 1024;
        case 'm': $num *= 1024;
        case 'k': $num *= 1024;
    }
    return $num;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];

    // Check if the file was uploaded without errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form.',
            UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
        ];
        die('Upload error: ' . ($errors[$file['error']] ?? 'Unknown error.'));
    }

    //limit file size
    $maxSize = convertToBytes(ini_get('upload_max_filesize'));
    $postMax = convertToBytes(ini_get('post_max_size'));
    if ($postMax > 0 && $postMax < $maxSize) {
        $maxSize = $postMax;
    }
    if ($file['size'] > $maxSize) {
        die('File is too large. Maximum allowed size is ' . ini_get('upload_max_filesize'));
    }
    $url = SERVER_URL . '/upload-to-server';

    // Build multipart form data
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
    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
        die('Failed to communicate with the Node server.');
    }

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
    exit;
}