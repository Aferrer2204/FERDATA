const express = require('express');
const router = express.Router();
const controller = require('../controllers/actividadesController');

router.get('/', controller.read);
router.post('/generate-daily', controller.generateDaily);
router.get('/:id', controller.get);
router.post('/', controller.create);
router.put('/:id', controller.update);
router.delete('/:id', controller.delete);

module.exports = router;