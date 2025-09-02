const http = require('http');
const { URL } = require('url');
const fs = require('fs');

const BASE = process.env.API_BASE || 'http://localhost:3000';

function get(path) {
    return new Promise((resolve, reject) => {
        const url = new URL(path, BASE);
        http.get(url, (res) => {
            let body = '';
            res.setEncoding('utf8');
            res.on('data', (chunk) => body += chunk);
            res.on('end', () => {
                try {
                    // Helper to remove BOM and everything before the first JSON bracket
                    function normalizeForJson(s) {
                        if (!s || typeof s !== 'string') return s;
                        // Remove BOM
                        s = s.replace(/^\uFEFF/, '');
                        s = s.trimStart();
                        const i1 = s.indexOf('{') === -1 ? Infinity : s.indexOf('{');
                        const i2 = s.indexOf('[') === -1 ? Infinity : s.indexOf('[');
                        const first = Math.min(i1, i2);
                        if (first !== Infinity && first > 0) s = s.slice(first);
                        return s;
                    }

                    const cleaned = normalizeForJson(body);
                    const json = JSON.parse(cleaned);
                    resolve({ status: res.statusCode, body: json });
                } catch (e) {
                    // If parse fails, return raw body but include first bytes for debugging
                    const firstChars = body ? Array.from(body.slice(0, 8)).map(c => c.charCodeAt(0).toString(16).toUpperCase()).join(' ') : '';
                    resolve({ status: res.statusCode, body: body, _debug_firstBytes: firstChars });
                }
            });
        }).on('error', reject);
    });
}

async function run() {
    const tests = [
        { id: 'TR-002', path: '/api/stats', expectFile: 'deliverables/data_stats.json' },
        { id: 'TR-003', path: '/api/empresas', expectFile: 'deliverables/data_empresas.json' },
        { id: 'TR-004', path: '/api/herramientas', expectFile: 'deliverables/data_herramientas.json' },
        { id: 'TR-005', path: '/api/inspecciones/get/4', expectFile: 'deliverables/data_inspecciones.json' },
        { id: 'TR-006', path: '/api/actividades', expectFile: 'deliverables/data_actividades.json' },
        { id: 'TR-007', path: '/api/usuarios/read', expectFile: 'deliverables/data_usuarios.json' }
    ];

    const results = [];
    for (const t of tests) {
        try {
            const res = await get(t.path);
            const expectedRaw = fs.readFileSync(t.expectFile, 'utf8');
            // Normalize expected (may contain BOM)
            const expectedClean = (function (s) { if (!s) return s; return (s.replace(/^\uFEFF/, '')).trimStart(); })(expectedRaw);
            const expected = JSON.parse(expectedClean);
            // Normalize wrappers: some exported files wrap arrays as { value: [...], Count }
            function unwrap(v) {
                if (v && typeof v === 'object' && v.hasOwnProperty('value') && Array.isArray(v.value)) return v.value;
                return v;
            }
            const actualNorm = unwrap(res.body);
            const expectedNorm = unwrap(expected);
            const pass = deepEqual(actualNorm, expectedNorm);
            results.push({ id: t.id, path: t.path, status: res.status, pass });
            console.log(`${t.id} ${t.path} -> HTTP ${res.status} -> ${pass ? 'PASS' : 'FAIL'}`);
            if (!pass) {
                const outPath = `tmp/${t.id.replace(/[^a-z0-9]/gi, '_')}_actual.json`;
                try { fs.mkdirSync('tmp', { recursive: true }); } catch (e) { }
                fs.writeFileSync(outPath, JSON.stringify(res.body, null, 2));
                console.log(`  Actual response saved to ${outPath}`);
            }
        } catch (err) {
            results.push({ id: t.id, path: t.path, error: String(err) });
            console.log(`${t.id} ${t.path} -> ERROR -> ${err.message || err}`);
        }
    }

    fs.writeFileSync('tmp/smoke_results.json', JSON.stringify(results, null, 2));
    console.log('\nSummary written to tmp/smoke_results.json');
}

function deepEqual(a, b) {
    try { return JSON.stringify(a) === JSON.stringify(b); } catch (e) { return false; }
}

run().catch((e) => { console.error('Fatal:', e); process.exit(2); });
