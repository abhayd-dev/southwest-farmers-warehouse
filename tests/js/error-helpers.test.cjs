// Runs the real serverErrorMessage() / jsonOrThrow() from layouts/app.blade.php against
// simulated server responses (npm run test:js). No browser or packages needed.
const fs = require('fs');
const src = fs.readFileSync(require('path').join(__dirname, '../../resources/views/layouts/app.blade.php'), 'utf8');
const start = src.indexOf('window.serverErrorMessage = function');
const end = src.indexOf('$.fn.dataTable.ext.errMode');
const window = {}; const $ = { fn: { dataTable: { ext: {} } } };
new Function('window', src.slice(start, end))(window);
const { serverErrorMessage: sem, jsonOrThrow } = window; global.serverErrorMessage = sem;
const res = (status, body, isJson = true) => ({ ok: status >= 200 && status < 300, status, json: () => isJson ? Promise.resolve(body) : Promise.reject(new SyntaxError('Unexpected token <')) });
let fails = 0; const check = (name, got, want) => { const ok = got === want; fails += !ok; console.log((ok ? 'PASS ' : 'FAIL ') + name + (ok ? '' : `\n   got:  ${got}\n   want: ${want}`)); };
(async () => {
  check('jQuery xhr 500 json', sem({ status: 500, responseJSON: { success: false, message: 'Error: SQLSTATE[23505] (QueryException at app/X.php:1)' } }, 'Update failed'), 'Error: SQLSTATE[23505] (QueryException at app/X.php:1)');
  check('jQuery xhr 422 validation', sem({ status: 422, responseJSON: { message: 'The given data was invalid.', errors: { name: ['The name field is required.'] } } }), 'The name field is required.');
  check('jQuery xhr html 500 page', sem({ status: 500, statusText: 'Internal Server Error' }, 'Update failed'), 'Update failed (HTTP 500 Internal Server Error)');
  check('jQuery network down', sem({ status: 0 }, 'x'), 'Could not reach the server (network error or timeout).');
  for (const [name, r, want] of [
    ['fetch 500 json', res(500, { success: false, message: 'Error: disk full' }), 'Error: disk full'],
    ['fetch 200 success:false', res(200, { success: false, message: 'Cannot delete: in use' }), 'Cannot delete: in use'],
    ['fetch 422 errors', res(422, { errors: { status: ['Invalid status'] } }), 'Invalid status'],
    ['fetch html 500', res(500, null, false), 'Request failed (HTTP 500)'],
  ]) {
    try { await jsonOrThrow(r); check(name, 'resolved', 'rejected'); } catch (e) { check(name, sem(e, 'fallback'), want); }
  }
  check('fetch 200 ok resolves', JSON.stringify(await jsonOrThrow(res(200, { success: true, message: 'ok' }))), '{"success":true,"message":"ok"}');
  check('fetch network error', sem(new TypeError('Failed to fetch'), 'Network error.'), 'Network error. (Failed to fetch)');
  console.log(fails ? `${fails} FAILED` : 'All assertions passed.'); process.exit(fails ? 1 : 0);
})();
