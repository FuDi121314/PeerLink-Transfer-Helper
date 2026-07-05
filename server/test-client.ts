import WebSocket from 'ws';
import fs from 'fs';
import path from 'path';

const args = process.argv.slice(2);
if (args.length < 2) {
  console.log('Usage: npx ts-node test-client.ts <ws://IP:3939> --upload <file> | --download');
  process.exit(1);
}
const [serverUrl, command, filePath] = args;
const ws = new WebSocket(serverUrl);

ws.on('open', () => {
  console.log('Connected');
  if (command === '--upload') {
    ws.send(JSON.stringify({ type: 'p2p-upload-request' }));
  } else {
    ws.send(JSON.stringify({ type: 'p2p-download-request' }));
  }
});

ws.on('message', (data, isBinary) => {
  if (isBinary) {
    const outPath = path.join(process.cwd(), 'downloaded_file');
    fs.writeFileSync(outPath, Buffer.from(data as ArrayBuffer));
    console.log('Downloaded to', outPath);
    ws.close();
  } else {
    const msg = JSON.parse(data.toString());
    if (msg.type === 'uploader-status' && msg.status === 'you-are-uploader') {
      const buf = fs.readFileSync(filePath!);
      ws.send(JSON.stringify({ type: 'p2p-file-start', filename: path.basename(filePath!), size: buf.length }));
      ws.send(buf);
      console.log('File sent.');
      ws.close();
    } else if (msg.type === 'uploader-status' && msg.status === 'queued') {
      console.log('Queued for upload...');
    } else if (msg.type === 'download-status' && msg.status === 'rejected') {
      console.log('No uploader. Rejected.');
      ws.close();
    }
  }
});
