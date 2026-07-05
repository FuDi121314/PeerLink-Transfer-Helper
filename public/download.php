<?php include 'config.php';
if (isset($_GET['hash']) && preg_match('/^[A-Z0-9]{6}$/', strtoupper($_GET['hash']))) {
    $url = SERVER_URL . '/download-from-server/' . strtoupper($_GET['hash']);
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="downloaded_file"');
    readfile($url);
    exit;
}
header('Location: transfer.php');
