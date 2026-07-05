import express from 'express';
import http from 'http';
import { WebSocketServer } from 'ws';
import dotenv from 'dotenv';
import fileRoutes from './routes/fileRoutes';
import messageRoutes from './routes/messageRoutes';
import { setupWebSocket } from './websocket/wsHandler';
import { ensureDirectory } from './utils/fileUtils';

dotenv.config();

const app = express();
const server = http.createServer(app);
const wss = new WebSocketServer({ server });

app.use(express.json());
app.use(express.urlencoded({ extended: true }));

app.use('/api/files', fileRoutes);
app.use('/api/messages', messageRoutes);

setupWebSocket(wss);

ensureDirectory('uploads');
ensureDirectory('messages');

const PORT = process.env.PORT || 3939;
server.listen(PORT, '0.0.0.0', () => {
  console.log(`Server running on port ${PORT}`);
});
