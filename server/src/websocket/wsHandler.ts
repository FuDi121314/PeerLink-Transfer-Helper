import { WebSocketServer, WebSocket } from 'ws';

interface Client {
  ws: WebSocket;
  id: string;
}

let clients: Client[] = [];

export function setupWebSocket(wss: WebSocketServer) {
  wss.on('connection', (ws) => {
    const id = `peer-${Date.now()}-${Math.random().toString(36).slice(2, 6)}`;
    clients.push({ ws, id });
    ws.send(JSON.stringify({ type: 'connected', id }));

    ws.on('message', (data) => {
      try {
        const msg = JSON.parse(data.toString());
        handleMessage(ws, msg);
      } catch {
        console.warn('Received non-JSON data');
      }
    });

    ws.on('close', () => {
      clients = clients.filter((c) => c.ws !== ws);
    });
  });
}

function handleMessage(_ws: WebSocket, msg: any) {
  switch (msg.type) {
    case 'file_relay':
      break;
    default:
      console.log('Unknown message type:', msg.type);
  }
}

export function broadcastMessage(msg: any) {
  clients.forEach((c) => {
    c.ws.send(JSON.stringify({ type: 'new_message', data: msg }));
  });
}

export function broadcastFile(fileBuffer: Buffer, fileName: string, recipientId: string): boolean {
  const target = clients.find((c) => c.id === recipientId);
  if (!target) return false;

  target.ws.send(JSON.stringify({
    type: 'file_transfer',
    fileName,
    data: fileBuffer.toString('base64')
  }));
  return true;
}
