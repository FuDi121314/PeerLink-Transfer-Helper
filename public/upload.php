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

    // Check upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive.',
            UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive.',
            UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the upload.',
        ];
        die('Upload error: ' . ($errors[$file['error']] ?? 'Unknown error.'));
    }

    // Optional: also respect the actual PHP limit (just in case)
    $maxSize = convertToBytes(ini_get('upload_max_filesize'));
    $postMax = convertToBytes(ini_get('post_max_size'));
    if ($postMax > 0 && $postMax < $maxSize) $maxSize = $postMax;

    if ($file['size'] > $maxSize) {
        die('File is too large. Maximum allowed is ' . ini_get('upload_max_filesize'));
    }

    $url = SERVER_URL . '/upload-to-server';

    // Use cURL with a file handle to stream the upload
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);

    // Build the multipart body using CURLFile (streams the file)
    $postData = [
        'file' => new CURLFile($file['tmp_name'], $file['type'], $file['name'])
    ];
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5 minutes timeout

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        die('cURL error: ' . $error);
    }

    if ($httpCode !== 200) {
        die('Node server returned HTTP ' . $httpCode . ' - ' . $response);
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

