import { Router } from 'express';
import multer from 'multer';
import { uploadFile, getFiles, downloadFile } from '../controllers/fileController';

const router = Router();
const storage = multer.diskStorage({
  destination: (_req, _file, cb) => cb(null, 'uploads/'),
  filename: (_req, file, cb) => cb(null, `${Date.now()}-${file.originalname}`)
});
const upload = multer({ storage });

router.post('/upload', upload.single('file'), uploadFile);
router.get('/', getFiles);
router.get('/:id', downloadFile);

export default router;
