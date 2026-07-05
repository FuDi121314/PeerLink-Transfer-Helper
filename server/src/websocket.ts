import { WebSocketServer, WebSocket } from 'ws';
import { Server } from 'http';

let p2pUploader: WebSocket | null = null;
const p2pQueue: WebSocket[] = [];
const p2pDownloaders = new Set<WebSocket>();
let p2pFileBuffer: Buffer | null = null;
let p2pFileName = '';
let p2pFileSize = 0;

function cleanupUploader(): void {
  p2pUploader = null;
  p2pFileBuffer = null;
  p2pFileName = '';
  p2pFileSize = 0;
  p2pDownloaders.clear();
  const next = p2pQueue.shift();
  if (next) {
    p2pUploader = next;
    next.send(JSON.stringify({ type: 'uploader-status', status: 'you-are-uploader' }));
  }
}

export function setupWebSocket(server: Server): void {
  const wss = new WebSocketServer({ server });

  wss.on('connection', (ws: WebSocket) => {
    console.log('WebSocket connected');

    ws.on('message', (data, isBinary) => {
      if (isBinary) {
        if (ws === p2pUploader) {
          p2pFileBuffer = Buffer.from(data as ArrayBuffer);
          console.log(`Received file ${p2pFileBuffer.length} bytes`);
          p2pDownloaders.forEach(d => {
            if (d.readyState === WebSocket.OPEN) d.send(data);
          });
        }
        return;
      }

      let msg: any;
      try {
        msg = JSON.parse(data.toString());
      } catch {
        return;
      }

      switch (msg.type) {
        case 'p2p-upload-request':
          if (!p2pUploader) {
            p2pUploader = ws;
            ws.send(JSON.stringify({ type: 'uploader-status', status: 'you-are-uploader' }));
          } else {
            p2pQueue.push(ws);
            ws.send(JSON.stringify({ type: 'uploader-status', status: 'queued' }));
          }
          break;

        case 'p2p-file-start':
          if (ws === p2pUploader) {
            p2pFileName = msg.filename;
            p2pFileSize = msg.size;
            p2pFileBuffer = null;
            p2pDownloaders.forEach(d => {
              if (d.readyState === WebSocket.OPEN) {
                d.send(JSON.stringify({ type: 'p2p-file-start', filename: p2pFileName, size: p2pFileSize }));
              }
            });
          }
          break;

        case 'p2p-download-request':
          if (p2pUploader && p2pUploader.readyState === WebSocket.OPEN) {
            p2pDownloaders.add(ws);
            if (p2pFileBuffer) {
              ws.send(JSON.stringify({ type: 'p2p-file-start', filename: p2pFileName, size: p2pFileBuffer.length }));
              ws.send(p2pFileBuffer);
            } else {
              ws.send(JSON.stringify({ type: 'download-status', status: 'waiting-for-uploader' }));
            }
          } else {
            ws.send(JSON.stringify({ type: 'download-status', status: 'rejected', reason: 'No active uploader' }));
          }
          break;

        case 'p2p-end-upload':
          if (ws === p2pUploader) cleanupUploader();
          break;
      }
    });

    ws.on('close', () => {
      if (ws === p2pUploader) {
        cleanupUploader();
      } else {
        p2pQueue.splice(p2pQueue.indexOf(ws), 1);
        p2pDownloaders.delete(ws);
      }
    });
  });
}
