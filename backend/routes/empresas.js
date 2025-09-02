const express = require('express');
const router = express.Router();
const controller = require('../controllers/empresasController');

// Helper para soportar endpoints con o sin sufijo .php
const normalize = (path) => path.replace(/\.php$/i, '');

// Lista /read, /read.php, /get.php or root
router.get(['/read', '/read.php', '/', '/get', '/get.php'], controller.read);
// Obtener por id: /get.php?id=xx or /get/:id
router.get(['/get', '/get.php'], (req, res) => {
    // permitir query ?id=xx
    const id = req.query.id;
    if (id) return controller.get({ params: { id } }, res);
    return res.status(400).json({ message: 'Falta id' });
});

router.get(['/get/:id', '/get.php/:id'], controller.get);

// Crear
router.post(['/create', '/create.php'], controller.create);

// Actualizar
router.put(['/update/:id', '/update.php/:id'], controller.update);
router.post(['/update/:id', '/update.php/:id'], controller.update); // algunos clientes usan POST

// Eliminar
router.delete(['/delete/:id', '/delete.php/:id'], controller.remove);
router.post(['/delete/:id', '/delete.php/:id'], controller.remove); // soporte por POST

module.exports = router;