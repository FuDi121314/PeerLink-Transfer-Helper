export interface StoredFileMeta {
  filename: string;
  originalName: string;
  size: number;
  createdAt: string;
  stored: boolean;
  path: string;
}

export interface MessageRecord {
  id: string;
  sender: string;
  text: string;
  recipient?: string;
  timestamp: string;
}

export interface ClientEntry {
  username: string;
  connectedAt: string;
}
