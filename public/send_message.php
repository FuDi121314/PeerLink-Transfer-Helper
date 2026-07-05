<?php include 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $ctx = stream_context_create(['http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode(['message' => $_POST['message']])
    ]]);
    file_get_contents(SERVER_URL . '/messages', false, $ctx);
}
header('Location: messages.php');
