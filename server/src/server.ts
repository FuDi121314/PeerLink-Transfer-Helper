import express from 'express';
import { createServer } from 'http';
import routes from './routes';
import { setupWebSocket } from './websocket';
import { PORT } from './config';

const app = express();
app.use(express.json());
app.use(routes);

const httpServer = createServer(app);
setupWebSocket(httpServer);

httpServer.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
});
