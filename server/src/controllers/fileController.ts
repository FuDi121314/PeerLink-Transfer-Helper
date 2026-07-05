import { Request, Response } from 'express';
import fs from 'fs';
import path from 'path';
import { v4 as uuidv4 } from 'uuid';
import { broadcastFile } from '../websocket/wsHandler';

const uploadsDir = path.join(__dirname, '../../../uploads');

interface FileEntry {
  id: string;
  filename: string;
  originalName: string;
  size: number;
  uploadedAt: string;
  storeLocal: boolean;
}

const fileDB: FileEntry[] = [];

export const uploadFile = (req: Request, res: Response) => {
  if (!req.file) {
    return res.status(400).json({ error: 'No file uploaded' });
  }

  const { storeLocal = 'true', recipient } = req.query;
  const id = uuidv4();
  const entry: FileEntry = {
    id,
    filename: req.file.filename,
    originalName: req.file.originalname,
    size: req.file.size,
    uploadedAt: new Date().toISOString(),
    storeLocal: storeLocal === 'true'
  };

  if (entry.storeLocal) {
    fileDB.push(entry);
    return res.json({ id, message: 'File stored on server' });
  }

  const filePath = req.file.path;
  const fileBuffer = fs.readFileSync(filePath);
  const relayed = broadcastFile(fileBuffer, entry.originalName, recipient as string);
  fs.unlinkSync(filePath);

  if (relayed) {
    return res.json({ id, message: 'File relayed to peer' });
  }

  return res.status(404).json({ error: 'Recipient not connected' });
};

export const getFiles = (_req: Request, res: Response) => {
  res.json(fileDB);
};

export const downloadFile = (req: Request, res: Response) => {
  const { id } = req.params;
  const entry = fileDB.find((f) => f.id === id);
  if (!entry) return res.status(404).json({ error: 'File not found' });

  const filePath = path.join(uploadsDir, entry.filename);
  if (!fs.existsSync(filePath)) return res.status(404).json({ error: 'File missing' });

  return res.download(filePath, entry.originalName);
};
