import { existsSync, mkdirSync } from 'fs';
import path from 'path';

export const ROOT_DIR = path.resolve(__dirname, '..', '..');
export const PORT = Number(process.env.PORT || 3939);
export const HOST = process.env.HOST || '0.0.0.0';
export const UPLOAD_DIR = path.join(ROOT_DIR, 'uploads');
export const MESSAGE_DIR = path.join(ROOT_DIR, 'messages');
export const TEMP_DIR = path.join(ROOT_DIR, 'temp');
export const SERVER_BASE = process.env.SERVER_BASE || `http://127.0.0.1:${PORT}`;

for (const dir of [UPLOAD_DIR, MESSAGE_DIR, TEMP_DIR]) {
  if (!existsSync(dir)) {
    mkdirSync(dir, { recursive: true });
  }
}
