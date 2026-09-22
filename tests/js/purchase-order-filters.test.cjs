'use strict';

/**
 * Regression test for the department/category/subcategory filter logic on
 * the Purchase Order create screen (client feedback 9/21, Warehouse items 3-4).
 *
 * Run with: node tests/js/purchase-order-filters.test.js
 * No dependencies -- this repo has no JS test runner set up (Vite build only),
 * so this is a plain Node script using only built-ins.
 *
 * It loads the ACTUAL <script> block out of the real Blade view (not a copy),
 * so it fails the moment that logic changes underneath it, with a minimal
 * jQuery/DOM shim faithful to real semantics: $el.attr(name) returns
 * `undefined` when an attribute was never set (vs '' when deliberately set
 * empty), and DOM attributes/values are always strings.
 *
 * Root cause this guards: restoreRowFilters() ran on every product-dropdown
 * open, including for a row that had never had a product picked (and so had
 * never had rememberRowFilters() called for it). Treating "never remembered"
 * the same as "remembered as empty" wiped out whatever department/category/
 * subcategory the user had just picked the instant they opened the product
 * list -- which also meant the product list itself came out unfiltered,
 * because filterProducts() immediately re-read the just-wiped filters.
 */

const fs = require('fs');
const path = require('path');
const assert = require('node:assert/strict');

const VIEW_PATH = path.join(__dirname, '..', '..', 'resources/views/warehouse/purchase-orders/create.blade.php');

function loadFilterFunctions() {
    const src = fs.readFileSync(VIEW_PATH, 'utf8');
    const scriptMatch = src.match(/<script>([\s\S]*?)<\/script>/);
    if (!scriptMatch) {
        throw new Error(`Could not find a <script> block in ${VIEW_PATH} -- has the view been restructured?`);
    }
    let js = scriptMatch[1];

    // Strip the Blade-only lines this harness doesn't need to execute:
    // the @json() fixtures (fed in as globals below instead) and everything
    // from the DOM/Select2 wiring onward (addRow, event listeners, the
    // page-load auto-add-a-row call) -- untestable without a real browser
    // and irrelevant to the pure filter-state logic under test here.
    js = js.replace(/const products = @json\(\$products\);/, '');
    js = js.replace(/const allCategories = @json\(\$categories\);/, '');
    js = js.replace(/const allSubcategories = @json\(\$subcategories\);/, '');

    const cutMarker = '// Initial build';
    const cutIdx = js.indexOf(cutMarker);
    if (cutIdx === -1) {
        throw new Error(`Expected to find "${cutMarker}" in the script -- has the view been restructured?`);
    }
    js = js.slice(0, cutIdx);

    // --- Minimal jQuery-like + DOM mock ---
    class FakeEl {
        constructor() {
            this.attrs = {};
            this._val = '';
        }
    }
    const elements = new Map();
    const el = (selectorOrId) => {
        const id = selectorOrId.startsWith('#') ? selectorOrId.slice(1) : selectorOrId;
        if (!elements.has(id)) elements.set(id, new FakeEl());
        return elements.get(id);
    };

    function $(selector) {
        const e = el(selector);
        return {
            attr(name, value) {
                if (value === undefined) {
                    return Object.prototype.hasOwnProperty.call(e.attrs, name) ? e.attrs[name] : undefined;
                }
                e.attrs[name] = String(value); // real DOM attributes are always strings
                return this;
            },
            val(value) {
                if (value === undefined) return e._val;
                e._val = value === null ? '' : String(value);
                return this;
            },
            html(value) {
                if (value === undefined) return e.html_ || '';
                e.html_ = value;
                return this;
            },
            find() {
                // Only .find('.product-select').val() is used by the code under
                // test, to read which product is currently chosen in this row.
                return { val: () => (e.selectedProductId !== undefined ? String(e.selectedProductId) : '') };
            },
            each() { return this; },
            trigger() { return this; },
        };
    }

    const sandbox = { $, window: {}, document: {} };
    sandbox.window = sandbox; // window.foo = ... lands on the same object as bare `foo`
    sandbox.document.getElementById = (id) => {
        const e = el(id);
        const obj = { addEventListener: () => {} };
        Object.defineProperty(obj, 'value', {
            get: () => e._val,
            set: (v) => { e._val = String(v ?? ''); },
        });
        return obj;
    };

    // Fixture data: two products in one department/category/subcategory
    // (FROZEN/MEAT/CHICKEN in the client's own scenario) and one elsewhere.
    sandbox.products = [
        { id: 1, department_id: 10, category_id: 20, subcategory_id: 30, product_name: 'Frozen Chicken Breast', barcode: 'F1', cost_price: 5 },
        { id: 2, department_id: 10, category_id: 20, subcategory_id: 30, product_name: 'Frozen Chicken Thigh', barcode: 'F2', cost_price: 4 },
        { id: 3, department_id: 99, category_id: 88, subcategory_id: 77, product_name: 'Canned Beans', barcode: 'C1', cost_price: 2 },
    ];
    sandbox.allCategories = [{ id: 20, name: 'Meat' }, { id: 88, name: 'Grocery' }];
    sandbox.allSubcategories = [{ id: 30, name: 'Chicken', category_id: 20 }, { id: 77, name: 'Canned', category_id: 88 }];

    js += `
        return {
            rebuildCategoryOptions, rebuildSubcategoryOptions, rememberRowFilters,
            restoreRowFilters: window.restoreRowFilters, filterProducts, categoryIdsForDepartment,
        };
    `;

    const factory = new Function('$', 'window', 'document', 'products', 'allCategories', 'allSubcategories', js);
    const fns = factory(sandbox.$, sandbox.window, sandbox.document, sandbox.products, sandbox.allCategories, sandbox.allSubcategories);

    return { fns, $, el };
}

function run() {
    let failures = 0;
    const test = (name, fn) => {
        try {
            fn();
            console.log(`  PASS  ${name}`);
        } catch (e) {
            failures++;
            console.log(`  FAIL  ${name}`);
            console.log(`        ${e.message}`);
        }
    };

    console.log('Item 3: filters picked before a row has a product must survive opening its product dropdown');
    {
        const { fns, $, el } = loadFilterFunctions();
        $('#filterDepartment').val('10'); // FROZEN
        $('#filterCategory').val('20');   // MEAT
        $('#filterSubcategory').val('30'); // CHICKEN

        // Simulates select2:opening firing for a row that has never had a
        // product picked (so rememberRowFilters() was never called for it).
        fns.restoreRowFilters(0);

        test('Department stays FROZEN (was resetting to "All Departments")', () => {
            assert.equal($('#filterDepartment').val(), '10');
        });
        test('Category stays MEAT (was resetting to "All Categories")', () => {
            assert.equal($('#filterCategory').val(), '20');
        });
        test('Subcategory stays CHICKEN (was resetting to "All Subcategories")', () => {
            assert.equal($('#filterSubcategory').val(), '30');
        });
    }

    console.log('Item 4: an earlier row\'s product-specific filters must not leak onto a later row, or get overwritten by it');
    {
        const { fns, $, el } = loadFilterFunctions();

        // Row 0: a product picked with NO filters active -- rememberRowFilters()
        // falls back to that product's own department/category/subcategory.
        $('#filterDepartment').val('');
        $('#filterCategory').val('');
        $('#filterSubcategory').val('');
        el('row-0').selectedProductId = 3; // "Canned Beans", department 99
        fns.rememberRowFilters(0);

        test("row 0 remembers product #3's own department/category/subcategory", () => {
            assert.equal(el('row-0').attrs['data-dept'], '99');
            assert.equal(el('row-0').attrs['data-cat'], '88');
            assert.equal(el('row-0').attrs['data-subcat'], '77');
        });

        // Row 1: user picks FROZEN/MEAT/CHICKEN, then opens row 1's (never
        // remembered) product dropdown.
        $('#filterDepartment').val('10');
        $('#filterCategory').val('20');
        $('#filterSubcategory').val('30');
        fns.restoreRowFilters(1);

        test('opening row 1\'s empty dropdown does not wipe the FROZEN filter just picked for it', () => {
            assert.equal($('#filterDepartment').val(), '10');
        });

        // User clicks back into row 0, which DOES have a remembered filter set.
        fns.restoreRowFilters(0);

        test("clicking back into row 0 restores ITS product's department, not row 1's leftover FROZEN", () => {
            assert.equal($('#filterDepartment').val(), '99');
        });
        test("clicking back into row 0 restores ITS product's category", () => {
            assert.equal($('#filterCategory').val(), '88');
        });
        test("clicking back into row 0 restores ITS product's subcategory", () => {
            assert.equal($('#filterSubcategory').val(), '77');
        });
    }

    console.log('');
    if (failures > 0) {
        console.log(`${failures} assertion(s) failed.`);
        process.exit(1);
    }
    console.log('All assertions passed.');
}

run();
