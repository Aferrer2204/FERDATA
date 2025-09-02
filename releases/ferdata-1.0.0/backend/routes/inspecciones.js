const express = require('express');
const router = express.Router();
const controller = require('../controllers/inspeccionesController');

router.get(['/read', '/read.php', '/', '/get', '/get.php'], controller.read);
router.get(['/get/:id', '/get.php/:id'], controller.get);
router.get(['/get', '/get.php'], (req, res) => {
    const id = req.query.id;
    if (id) return controller.get({ params: { id } }, res);
    return controller.read(req, res);
});

router.post(['/create', '/create.php'], controller.create);
router.put(['/update/:id', '/update.php/:id'], controller.update);
router.post(['/update/:id', '/update.php/:id'], controller.update);
router.delete(['/delete/:id', '/delete.php/:id'], controller.remove);
router.post(['/delete/:id', '/delete.php/:id'], controller.remove);

module.exports = router;
