const fs = require('fs');
const path = require('path');

const RELEASE_DIR = path.join(__dirname, '..', 'releases', 'ferdata-1.0.0');
const DB_EXPORTS = path.join(RELEASE_DIR, 'db_exports');
const OUT_FILE = path.join(RELEASE_DIR, 'ferdata-db-dump.sql');

function escapeSql(val) {
    if (val === null || typeof val === 'undefined') return 'NULL';
    if (typeof val === 'number' || typeof val === 'boolean') return val.toString();
    // dates may be in ISO string format; keep as string
    const s = String(val).replace(/'/g, "''");
    return `'${s}'`;
}

function toIdentifierList(cols) {
    return cols.map(c => `\`${c}\``).join(', ');
}

(async () => {
    try {
        if (!fs.existsSync(DB_EXPORTS)) {
            console.error('db_exports directory not found:', DB_EXPORTS);
            process.exit(2);
        }

        const files = fs.readdirSync(DB_EXPORTS).filter(f => f.endsWith('.json'));
        const out = [];
        out.push('-- FERDATA DB dump generated from exported JSON files');
        out.push(`-- Generated: ${new Date().toISOString()}`);
        out.push('\n');

        for (const file of files) {
            const table = path.basename(file, '.json');
            const schemaFile = path.join(DB_EXPORTS, `${table}_schema.sql`);
            out.push(`-- ----------------------------------------------------`);
            out.push(`-- Table: ${table}`);
            out.push(`-- ----------------------------------------------------\n`);

            if (fs.existsSync(schemaFile)) {
                const schema = fs.readFileSync(schemaFile, 'utf8');
                // ensure it ends with semicolon
                if (!/;\s*$/.test(schema)) out.push(schema + ';'); else out.push(schema);
            } else {
                out.push(`-- schema file not found for ${table}`);
            }

            // read json
            const data = JSON.parse(fs.readFileSync(path.join(DB_EXPORTS, file), 'utf8'));
            // accept either { value: [...] , Count: n } or direct array
            const rows = Array.isArray(data) ? data : (Array.isArray(data.value) ? data.value : []);
            if (!rows.length) {
                out.push(`-- No rows exported for ${table}\n`);
                continue;
            }

            // Build INSERTs in batches of 1000
            const cols = Object.keys(rows[0]);
            const colList = toIdentifierList(cols);
            for (let i = 0; i < rows.length; i += 1000) {
                const batch = rows.slice(i, i + 1000);
                const values = batch.map(r => '(' + cols.map(c => escapeSql(r[c])).join(', ') + ')').join(',\n');
                out.push(`INSERT INTO \`${table}\` (${colList}) VALUES\n${values};\n`);
            }
        }

        fs.writeFileSync(OUT_FILE, out.join('\n'), 'utf8');
        console.log('OK', OUT_FILE);
        process.exit(0);
    } catch (err) {
        console.error('ERR', err && err.message ? err.message : err);
        process.exit(2);
    }
})();
