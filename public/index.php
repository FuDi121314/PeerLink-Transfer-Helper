<?php include 'config.php';
$bgDir = __DIR__ . '/../customize/background/';
$images = glob($bgDir . '*.png');
$bgImage = '';
$fixedBg = '../customize/background/background.png';
if (file_exists(__DIR__ . '/' . $fixedBg)) {
    $bgImage = $fixedBg;
} elseif (!empty($images)) {
    $bgImage = '../customize/background/' . basename($images[array_rand($images)]);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>P2P Transfer</title>
    <style>
        body { 
            background:#121212; 
            color:#e0e0e0; 
            font-family:sans-serif; 
            display:flex; 
            align-items:center; 
            justify-content:center; 
            min-height:100vh; 
            margin:0; 
            background-attachment: fixed;
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
        }
        <?php if($bgImage): ?>
        body { 
            background-image:url('<?=$bgImage?>'); 
            /* 
            for these two line you can directly use {background-size:cover; background-position:center; }
            or define $bg_xxx in config.php if you need
            */
            background-size: <?php echo $bg_size ?? 'cover'; ?>;
            background-position: <?php echo $bg_position ?? 'center'; ?>;
        }
        <?php endif; ?>
        .container { 
            background:rgba(0,0,0,0.7); 
            padding:40px; 
            border-radius:10px; 
            text-align:center; 
        }
        button { 
            background:#333; 
            color:#fff; 
            border:none; 
            padding:15px 30px; 
            margin:10px; 
            font-size:18px; 
            cursor:pointer; 
            border-radius:5px; 
        }
        button:hover { background:#555; }
        a { text-decoration:none; color:inherit; }
    </style>
</head>
<body>
<div class="container">
    <h1>PeerLink Transfer</h1>
    <a href="transfer.php"><button>Transfer File</button></a>
    <a href="messages.php"><button>Send Message</button></a>
</div>
</body>
</html>
