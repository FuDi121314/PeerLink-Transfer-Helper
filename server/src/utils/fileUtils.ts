import fs from 'fs';
import path from 'path';

export function ensureDirectory(dir: string) {
  const targetDir = path.join(__dirname, '../../../', dir);
  if (!fs.existsSync(targetDir)) {
    fs.mkdirSync(targetDir, { recursive: true });
  }
}
