import { Router } from 'express';

const router = Router();

router.get('/', (_req, res) => {
  res.json([{ username: 'anonymous', connectedAt: new Date().toISOString() }]);
});

export { router as clientRoutes };
