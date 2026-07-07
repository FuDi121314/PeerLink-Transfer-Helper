<?php include 'config.php';
$wsUrl = str_replace(['http://', 'https://'], ['ws://', 'wss://'], SERVER_URL);
$bgImage = '';
$fixedBg = '../customize/background/background.png';
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
    <meta charset="utf-8">
    <title>File Transfer</title>
    <style>
        body { 
            background:#121212; 
            color:#e0e0e0; 
            font-family:sans-serif; 
            margin:0; 
            padding:20px; 
            background-attachment: fixed;
            background-size: cover;
            background-position: center;
        }
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
document.addEventListener('DOMContentLoaded', () => {
    const wsUrl = '<?= $wsUrl ?>';
    let ws;
    let receivedFileName = 'downloaded_file';
    const statusDiv = document.getElementById('p2pStatus');

    function connectWs() {
        if (ws && ws.readyState === WebSocket.OPEN) return;
        ws = new WebSocket(wsUrl);
        ws.binaryType = 'arraybuffer';
        ws.onopen = () => {
            statusDiv.textContent = 'WebSocket connected.';
        };
        ws.onmessage = (e) => {
            if (e.data instanceof ArrayBuffer) {
                const blob = new Blob([e.data]);
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = receivedFileName;
                a.click();
                statusDiv.textContent = 'File downloaded.';
            } else {
                try {
                    const m = JSON.parse(e.data);
                    handleWsMessage(m);
                } catch(err) {
                    console.error('Invalid JSON:', e.data);
                }
            }
        };
        ws.onerror = (err) => {
            statusDiv.textContent = 'WebSocket error. Check server.';
            console.error(err);
        };
        ws.onclose = () => {
            statusDiv.textContent = 'WebSocket disconnected.';
        };
    }

    function handleWsMessage(m) {
        switch(m.type) {
            case 'uploader-status':
                if (m.status === 'you-are-uploader') {
                    document.getElementById('p2pUploadUI').classList.remove('hidden');
                    statusDiv.textContent = 'You are uploader. Choose a file.';
                } else if (m.status === 'queued') {
                    statusDiv.textContent = 'Waiting in queue for uploader slot...';
                }
                break;
            case 'download-status':
                if (m.status === 'rejected') {
                    alert('No active uploader. Download rejected.');
                    statusDiv.textContent = 'No uploader available.';
                } else if (m.status === 'waiting-for-uploader') {
                    statusDiv.textContent = 'Waiting for uploader to send file...';
                }
                break;
            case 'p2p-file-start':
                receivedFileName = m.filename;
                statusDiv.textContent = `Receiving file: ${m.filename} (${m.size} bytes)`;
                break;
            default:
                console.log('Unhandled message:', m);
        }
    }

    // Radio toggle
    document.querySelectorAll('input[name="mode"]').forEach(r => {
        r.addEventListener('change', () => {
            const p2p = document.getElementById('p2pSection');
            const srv = document.getElementById('serverSection');
            if (r.value === 'p2p') {
                p2p.classList.remove('hidden');
                srv.classList.add('hidden');
            } else {
                p2p.classList.add('hidden');
                srv.classList.remove('hidden');
            }
        });
    });

    // P2P Upload request
    document.getElementById('p2pUploadBtn').addEventListener('click', () => {
        connectWs();
        ws.onopen = () => {
            ws.send(JSON.stringify({type:'p2p-upload-request'}));
        };
    });

    // Send file button
    document.getElementById('p2pSendFileBtn').addEventListener('click', () => {
        const fileInput = document.getElementById('p2pFileInput');
        const file = fileInput.files[0];
        if (!file) {
            alert('Please select a file first.');
            return;
        }
        if (!ws || ws.readyState !== WebSocket.OPEN) {
            alert('WebSocket not connected. Try uploading again.');
            return;
        }
        const reader = new FileReader();
        reader.onload = () => {
            ws.send(JSON.stringify({type:'p2p-file-start', filename:file.name, size:file.size}));
            ws.send(reader.result);
            statusDiv.textContent = 'File sent.';
            document.getElementById('p2pUploadUI').classList.add('hidden');
        };
        reader.readAsArrayBuffer(file);
    });

    // P2P Download request
    document.getElementById('p2pDownloadBtn').addEventListener('click', () => {
        connectWs();
        ws.onopen = () => {
            ws.send(JSON.stringify({type:'p2p-download-request'}));
            document.getElementById('p2pDownloadUI').classList.remove('hidden');
        };
    });

    // Start download button (if needed, mostly automatic)
    document.getElementById('p2pStartDownloadBtn').addEventListener('click', () => {
        if (ws && ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify({type:'p2p-download-request'}));
        } else {
            alert('WebSocket not connected. Click "P2P Download" first.');
        }
    });
});
</script>
</body>
</html>