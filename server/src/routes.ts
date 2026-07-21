import { Router, Request, Response } from 'express';
import multer from 'multer';
import path from 'path';
import { generateHash } from './hash';
import { readMessages, writeMessages, getFilePathByHash, Message } from './storage';
import { UPLOADS_DIR } from './config';

const router = Router();

const storage = multer.diskStorage({
  destination: UPLOADS_DIR,
  filename: (req, file, cb) => {
    const hash = generateHash();
    const ext = path.extname(file.originalname);
    cb(null, hash + ext);
  }
});
const upload = multer({ storage });

router.post('/upload-to-server', upload.single('file'), (req: Request, res: Response) => {
  if (!req.file) return res.status(400).json({ error: 'No file' });
  const hash = path.basename(req.file.filename, path.extname(req.file.filename));
  res.json({ hash });
});

router.get('/download-from-server/:hash', (req: Request, res: Response) => {
  const hash = req.params.hash;
  const filePath = getFilePathByHash(hash);
  if (!filePath) return res.status(404).send('File not found');
  res.download(filePath);
});

router.get('/messages', (req: Request, res: Response) => {
  res.json(readMessages());
});

router.post('/messages', (req: Request, res: Response) => {
  const { message } = req.body;
  if (!message || typeof message !== 'string') {
    return res.status(400).json({ error: 'Message required' });
  }
  const messages = readMessages();
  const newMsg: Message = {
    id: messages.length ? Math.max(...messages.map(m => m.id)) + 1 : 1,
    text: message,
    timestamp: Date.now()
  };
  messages.push(newMsg);
  writeMessages(messages);
  res.json({ success: true, id: newMsg.id });
});

router.delete('/messages/:id', (req: Request, res: Response) => {
  const id = parseInt(req.params.id);
  const messages = readMessages();
  const index = messages.findIndex(m => m.id === id);
  if (index === -1) return res.status(404).json({ error: 'Not found' });
  messages.splice(index, 1);
  writeMessages(messages);
  res.json({ success: true });
});

/// 403 

export default router;
