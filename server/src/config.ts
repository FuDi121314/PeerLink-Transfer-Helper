import path from 'path';

export const PORT = 3939;
export const ROOT = path.resolve(__dirname, '..', '..');
export const UPLOADS_DIR = path.join(ROOT, 'uploads');
export const MESSAGES_DIR = path.join(ROOT, 'messages');
export const MESSAGES_FILE = path.join(MESSAGES_DIR, 'messages.json');
