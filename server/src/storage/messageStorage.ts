import fs from 'fs';
import { MESSAGE_FILE } from '../config';
import { StoredMessage } from '../types';

export function appendMessage(message: StoredMessage): void {
  const line = JSON.stringify(message) + '\n';
  fs.appendFileSync(MESSAGE_FILE, line, 'utf8');
}

export function readMessages(): StoredMessage[] {
  if (!fs.existsSync(MESSAGE_FILE)) {
    return [];
  }

  const contents = fs.readFileSync(MESSAGE_FILE, 'utf8').trim();
  if (!contents) {
    return [];
  }

  return contents.split('\n').map((line) => JSON.parse(line) as StoredMessage);
}
