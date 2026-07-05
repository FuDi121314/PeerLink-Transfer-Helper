import fs from 'fs';
import path from 'path';
import { UPLOADS_DIR, MESSAGES_DIR, MESSAGES_FILE } from './config';

[UPLOADS_DIR, MESSAGES_DIR].forEach(dir => {
  if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
});
if (!fs.existsSync(MESSAGES_FILE)) fs.writeFileSync(MESSAGES_FILE, '[]');

export interface Message {
  id: number;
  text: string;
  timestamp: number;
}

export function readMessages(): Message[] {
  return JSON.parse(fs.readFileSync(MESSAGES_FILE, 'utf-8'));
}

export function writeMessages(messages: Message[]): void {
  fs.writeFileSync(MESSAGES_FILE, JSON.stringify(messages, null, 2));
}

export function getFilePathByHash(hash: string): string | null {
  const files = fs.readdirSync(UPLOADS_DIR);
  const match = files.find(f => f.startsWith(hash));
  return match ? path.join(UPLOADS_DIR, match) : null;
}

export function getAllFiles(): string[] {
  return fs.readdirSync(UPLOADS_DIR);
}
