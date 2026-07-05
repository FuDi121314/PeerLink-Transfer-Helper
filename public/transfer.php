<?php include 'config.php';
$wsUrl = str_replace('http', 'ws', SERVER_URL);
$bgImage = '';
$fixedBg = '../customize/background/bg_transfer.png';
if (file_exists(__DIR__ . '/' . $fixedBg)) {
    $bgImage = $fixedBg;
} else {
    $bgDir = __DIR__ . '/../customize/background/';
    $images = glob($bgDir . '*.png');
    if (!empty($images)) $bgImage = '../customize/background/' . basename($images[array_rand($images)]);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>File Transfer</title>
    <style>
        body { background:#121212; color:#e0e0e0; font-family:sans-serif; margin:0; padding:20px; }
        <?php if($bgImage): ?>body { background-image:url('<?=$bgImage?>'); background-size:cover; background-position:center; }<?php endif; ?>
        .container { background:rgba(0,0,0,0.8); padding:20px; border-radius:10px; max-width:600px; margin:auto; }
        button, input[type="file"], input[type="text"] { background:#333; color:#fff; border:1px solid #555; padding:10px; margin:10px 0; border-radius:5px; }
        button { cursor:pointer; }
        .hidden { display:none; }
        .status { margin-top:10px; padding:10px; background:#222; border-radius:5px; }
        a { color:#aaa; text-decoration:none; }
    </style>
</head>
<body>
<div class="container">
    <h2>File Transfer</h2>
    <a href="index.php">Back</a><hr>
    <label><input type="radio" name="mode" value="p2p" checked> P2P</label>
    <label><input type="radio" name="mode" value="server"> To Server</label>

    <div id="p2pSection">
        <button id="p2pUploadBtn">P2P Upload</button>
        <button id="p2pDownloadBtn">P2P Download</button>
        <div id="p2pUploadUI" class="hidden">
            <input type="file" id="p2pFileInput"><br>
            <button id="p2pSendFileBtn">Send File</button>
        </div>
        <div id="p2pDownloadUI" class="hidden">
            <button id="p2pStartDownloadBtn">Start Download</button>
        </div>
        <div id="p2pStatus" class="status"></div>
    </div>

    <div id="serverSection" class="hidden">
        <h3>To Server</h3>
        <h4>Upload</h4>
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <input type="file" name="file" required>
            <button type="submit">Upload</button>
        </form>
        <h4>Download</h4>
        <form action="download.php" method="get">
            <input type="text" name="hash" placeholder="6-digit hash" required pattern="[A-Z0-9]{6}">
            <button type="submit">Download</button>
        </form>
    </div>
</div>

<script>
const wsUrl = '<?=$wsUrl?>';
let ws, receivedFileName = 'downloaded_file';

function connectWs() {
    if (ws && ws.readyState === WebSocket.OPEN) return;
    ws = new WebSocket(wsUrl);
    ws.binaryType = 'arraybuffer';
    ws.onmessage = e => {
        if (e.data instanceof ArrayBuffer) {
            const blob = new Blob([e.data]);
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = receivedFileName;
            a.click();
            document.getElementById('p2pStatus').textContent = 'File downloaded.';
        } else {
            const m = JSON.parse(e.data);
            if (m.type === 'uploader-status' && m.status === 'you-are-uploader') {
                document.getElementById('p2pStatus').textContent = 'You are uploader. Choose file.';
                document.getElementById('p2pUploadUI').classList.remove('hidden');
            } else if (m.type === 'uploader-status' && m.status === 'queued') {
                document.getElementById('p2pStatus').textContent = 'Queued...';
            } else if (m.type === 'download-status' && m.status === 'rejected') {
                document.getElementById('p2pStatus').textContent = 'No uploader. Rejected.';
            } else if (m.type === 'p2p-file-start') {
                receivedFileName = m.filename;
            }
        }
    };
}

document.getElementById('p2pUploadBtn').onclick = () => {
    connectWs();
    ws.onopen = () => ws.send(JSON.stringify({type:'p2p-upload-request'}));
};
document.getElementById('p2pDownloadBtn').onclick = () => {
    connectWs();
    ws.onopen = () => {
        ws.send(JSON.stringify({type:'p2p-download-request'}));
        document.getElementById('p2pDownloadUI').classList.remove('hidden');
    };
};
document.getElementById('p2pSendFileBtn').onclick = () => {
    const file = document.getElementById('p2pFileInput').files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = () => {
        ws.send(JSON.stringify({type:'p2p-file-start', filename:file.name, size:file.size}));
        ws.send(reader.result);
        document.getElementById('p2pStatus').textContent = 'File sent.';
    };
    reader.readAsArrayBuffer(file);
};

document.querySelectorAll('input[name=mode]').forEach(r => r.onchange = () => {
    const p2p = document.getElementById('p2pSection');
    const srv = document.getElementById('serverSection');
    if (r.value === 'p2p') { p2p.classList.remove('hidden'); srv.classList.add('hidden'); }
    else { p2p.classList.add('hidden'); srv.classList.remove('hidden'); }
});
window.onload = connectWs;
</script>
</body>
</html>
