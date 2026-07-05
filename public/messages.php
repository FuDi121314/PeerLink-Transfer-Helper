<?php include 'config.php';
$bgImage = '';
$fixedBg = '../customize/background/bg_messages.png';
if (file_exists(__DIR__ . '/' . $fixedBg)) {
    $bgImage = $fixedBg;
} else {
    $bgDir = __DIR__ . '/../customize/background/';
    $images = glob($bgDir . '*.png');
    if (!empty($images)) $bgImage = '../customize/background/' . basename($images[array_rand($images)]);
}
$messages = json_decode(file_get_contents(SERVER_URL . '/messages'), true);
$msgBgDir = __DIR__ . '/../customize/messageBG/';
$msgBgImages = glob($msgBgDir . '*.png');
$isAdmin = isset($_GET['admin']) && $_GET['admin'] === 'true';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Messages</title>
    <style>
        body { background:#121212; color:#e0e0e0; font-family:sans-serif; margin:0; padding:20px; }
        <?php if($bgImage): ?>body { background-image:url('<?=$bgImage?>'); background-size:cover; background-position:center; }<?php endif; ?>
        .container { background:rgba(0,0,0,0.7); padding:20px; border-radius:10px; max-width:600px; margin:auto; }
        .msg-box { margin:15px 0; padding:15px; border-radius:5px; color:#fff; text-shadow:0 1px 3px rgba(0,0,0,0.8); }
        .copy-btn, .delete-btn { background:rgba(255,255,255,0.2); border:none; color:#fff; padding:5px 10px; cursor:pointer; border-radius:3px; margin-top:5px; }
        .delete-btn { background:rgba(255,0,0,0.5); margin-left:10px; }
        textarea, input { background:#333; color:#fff; border:1px solid #555; padding:10px; width:100%; border-radius:5px; margin-bottom:10px; }
        button { background:#444; color:#fff; border:none; padding:10px 20px; cursor:pointer; border-radius:5px; }
        a { color:#aaa; }
    </style>
</head>
<body>
<div class="container">
    <h2>Messages</h2>
    <a href="index.php">Back</a> <?php if($isAdmin) echo '<span style="color:red">Admin</span>'; ?><hr>
    <?php foreach ($messages as $msg): ?>
        <?php $boxBg = $msgBgImages ? '../customize/messageBG/' . basename($msgBgImages[array_rand($msgBgImages)]) : ''; ?>
        <div class="msg-box" style="<?=$boxBg ? "background-image:url('$boxBg');background-size:cover;background-position:center;" : 'background:#333;' ?>">
            <p><?=htmlspecialchars($msg['text'])?></p>
            <button class="copy-btn" onclick="navigator.clipboard.writeText('<?=htmlspecialchars(addslashes($msg['text']))?>')">Copy</button>
            <?php if($isAdmin): ?>
                <a class="delete-btn" href="delete_message.php?id=<?=$msg['id']?>" onclick="return confirm('Delete?')">Delete</a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    <?php if(empty($messages)) echo '<p>No messages yet.</p>'; ?>
    <hr>
    <form action="send_message.php" method="post">
        <textarea name="message" rows="3" required></textarea>
        <button type="submit">Send</button>
    </form>
</div>
</body>
</html>
