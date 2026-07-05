<?php include 'config.php';
if (isset($_GET['id'])) {
    $ctx = stream_context_create(['http' => ['method' => 'DELETE']]);
    file_get_contents(SERVER_URL . '/messages/' . intval($_GET['id']), false, $ctx);
}
header('Location: messages.php?admin=true');
