<?php include 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $ch = curl_init(SERVER_URL . '/upload-to-server');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ['file' => new CURLFile($_FILES['file']['tmp_name'], $_FILES['file']['type'], $_FILES['file']['name'])]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $resp = json_decode(curl_exec($ch), true);
    curl_close($ch);
    $hash = $resp['hash'] ?? 'ERROR';
    echo "<!DOCTYPE html><html><head><style>body{background:#121212;color:#e0e0e0;font-family:sans-serif;padding:20px;}</style></head><body>
          <h2>File Uploaded</h2><p>Download hash: <strong>$hash</strong></p><a href='transfer.php'>Back</a></body></html>";
} else header('Location: transfer.php');
