import express from 'express';
import { createServer } from 'http';
import { createProxyMiddleware } from 'http-proxy-middleware';
import routes from './routes';
import { setupWebSocket } from './websocket';
import { PORT } from './config';

const app = express();
app.use(express.json());
app.use(routes);

// Proxy to the PHP server on port 8000
app.use(
  '/',
  createProxyMiddleware({
    target: 'http://localhost:8000',   // PHP built-in server
    changeOrigin: true,
    ws: false,                          // WebSocket handled separately
  })
);

const httpServer = createServer(app);
setupWebSocket(httpServer);

httpServer.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
  console.log(`Proxying non-API requests to http://localhost:8000`);
});
