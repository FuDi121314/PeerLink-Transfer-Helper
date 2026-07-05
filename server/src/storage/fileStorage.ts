import { promises as fs } from 'fs';
import path from 'path';
import type { Express } from 'express';
import { SERVER_BASE, TEMP_DIR, UPLOAD_DIR } from '../config';
import { StoredFileMeta } from '../types';

function makeFileName(originalName: string): string {
  const ext = path.extname(originalName);
  const baseName = path.basename(originalName, ext).replace(/[^a-z0-9.-]+/gi, '_');
  return `${baseName}-${Date.now()}-${Math.random().toString(36).slice(2, 8)}${ext}`;
}

export async function savePermanentFile(file: Express.Multer.File): Promise<StoredFileMeta> {
  const fileName = makeFileName(file.originalname || 'upload');
  const destination = path.join(UPLOAD_DIR, fileName);
  await fs.rename(file.path, destination);
  const stat = await fs.stat(destination);
  return {
    filename: fileName,
    originalName: file.originalname || fileName,
    size: stat.size,
    createdAt: new Date().toISOString(),
    stored: true,
    path: destination,
  };
}

export async function saveTemporaryFile(file: Express.Multer.File): Promise<StoredFileMeta> {
  const fileName = makeFileName(file.originalname || 'temp-upload');
  const destination = path.join(TEMP_DIR, fileName);
  await fs.rename(file.path, destination);
  const stat = await fs.stat(destination);
  return {
    filename: fileName,
    originalName: file.originalname || fileName,
    size: stat.size,
    createdAt: new Date().toISOString(),
    stored: false,
    path: destination,
  };
}

export async function listStoredFiles(): Promise<StoredFileMeta[]> {
  const entries = await fs.readdir(UPLOAD_DIR, { withFileTypes: true });
  const files = await Promise.all(
    entries
      .filter((entry) => entry.isFile())
      .map(async (entry) => {
        const filePath = path.join(UPLOAD_DIR, entry.name);
        const stat = await fs.stat(filePath);
        return {
          filename: entry.name,
          originalName: entry.name,
          size: stat.size,
          createdAt: stat.mtime.toISOString(),
          stored: true,
          path: filePath,
        } satisfies StoredFileMeta;
      }),
  );
  return files.sort((left, right) => right.createdAt.localeCompare(left.createdAt));
}

export function getDownloadUrl(filename: string): string {
  return `${SERVER_BASE}/api/files/download/${encodeURIComponent(filename)}`;
}

export async function removeTemporaryFile(filename: string): Promise<void> {
  const target = path.join(TEMP_DIR, filename);
  await fs.rm(target, { force: true });
}
