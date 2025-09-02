const db = require('../config/database');

const User = {
    create: (userData, callback) => {
        const { nombre, email, password } = userData;
        const query = 'INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)';
        db.query(query, [nombre, email, password], callback);
    },
    findByEmail: (email, callback) => {
        const query = 'SELECT * FROM usuarios WHERE email = ?';
        db.query(query, [email], callback);
    },
    findById: (id, callback) => {
        const query = 'SELECT id, nombre, email FROM usuarios WHERE id = ?';
        db.query(query, [id], callback);
    }
};

module.exports = User;