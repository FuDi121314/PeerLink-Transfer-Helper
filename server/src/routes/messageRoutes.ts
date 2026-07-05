import express from 'express';
import { appendMessage, readMessages } from '../storage/messageStorage';
import { StoredMessage } from '../types';
import { broadcast } from '../websocket/wsHandler';

export const messageRoutes = express.Router();

messageRoutes.post('/', (req, res) => {
  const { sender, text, recipient } = req.body;
  if (!sender || !text) {
    return res.status(400).json({ error: 'sender and text are required' });
  }

  const message: StoredMessage = {
    sender,
    text,
    recipient,
    timestamp: new Date().toISOString(),
  };

  appendMessage(message);
  broadcast({ type: 'message', sender, text, recipient }, recipient);

  return res.status(201).json({ message });
});

messageRoutes.get('/', (_req, res) => {
  const messages = readMessages();
  res.json({ messages });
});
