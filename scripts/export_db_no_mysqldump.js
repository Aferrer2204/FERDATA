const db = require('../backend/db');
const fs = require('fs');
const path = require('path');

const OUT_DIR = path.join(__dirname, '..', 'releases', 'ferdata-1.0.0', 'db_exports');
if (!fs.existsSync(OUT_DIR)) fs.mkdirSync(OUT_DIR, { recursive: true });

async function exportTable(table) {
  try {
    const [rows] = await db.promise().execute(`SELECT * FROM ${table}`);
    fs.writeFileSync(path.join(OUT_DIR, `${table}.json`), JSON.stringify(rows, null, 2), 'utf8');
    console.log(`Exported ${table} rows=${rows.length}`);
  } catch (err) {
    console.error(`Error exporting ${table}:`, err.message || err);
  }
}

(async () => {
  const tables = ['empresas','herramientas','inspecciones','actividades','usuarios','reportes'];
  for (const t of tables) await exportTable(t);
  // export CREATE TABLE via SHOW CREATE TABLE
  for (const t of tables) {
    try {
      const [rows] = await db.promise().execute(`SHOW CREATE TABLE ${t}`);
      const sql = rows && rows[0] ? (rows[0]['Create Table'] || rows[0]['Create View'] || JSON.stringify(rows[0])) : '';
      fs.writeFileSync(path.join(OUT_DIR, `${t}_schema.sql`), sql, 'utf8');
      console.log(`Exported schema for ${t}`);
    } catch (err) {
      console.error(`Error exporting schema ${t}:`, err.message || err);
    }
  }
  console.log('DONE');
  process.exit(0);
})();
