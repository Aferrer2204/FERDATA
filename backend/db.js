const mysql = require('mysql2');
const dotenv = require('dotenv');
dotenv.config();

const pool = mysql.createPool({
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'magnatesting_db',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

pool.getConnection((err, connection) => {
    if (err) {
        console.error('Error de conexión a MySQL (pool):', err.message);
    } else {
        console.log('Conexión exitosa a MySQL (pool)');
        connection.release();
    }
});

// Export pool and convenience .query for backward compatibility
// Export the promise wrapper of the pool for use with async/await code elsewhere.
// Create a promise wrapper of the pool for async/await usage.
const promisePool = pool.promise();

// Ensure the exported pool has a .promise() method that returns the promisePool.
// This preserves backward compatibility: code can call either db.query(...) or db.promise().execute(...).
pool.promise = () => promisePool;

module.exports = pool;