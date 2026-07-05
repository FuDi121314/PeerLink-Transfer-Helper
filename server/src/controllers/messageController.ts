import { Request, Response } from 'express';
import fs from 'fs';
import path from 'path';
import { v4 as uuidv4 } from 'uuid';
import { broadcastMessage } from '../websocket/wsHandler';

const messagesFile = path.join(__dirname, '../../../messages/messages.json');

if (!fs.existsSync(messagesFile)) {
  fs.writeFileSync(messagesFile, JSON.stringify([], null, 2));
}

export const sendMessage = (req: Request, res: Response) => {
  const { content, sender = 'Anonymous' } = req.body;
  if (!content) return res.status(400).json({ error: 'Missing content' });

  const msg = {
    id: uuidv4(),
    sender,
    content,
    timestamp: new Date().toISOString()
  };

  const messages = JSON.parse(fs.readFileSync(messagesFile, 'utf-8'));
  messages.push(msg);
  fs.writeFileSync(messagesFile, JSON.stringify(messages, null, 2));

  broadcastMessage(msg);

  return res.json(msg);
};

export const getMessages = (_req: Request, res: Response) => {
  const messages = JSON.parse(fs.readFileSync(messagesFile, 'utf-8'));
  return res.json(messages);
};
