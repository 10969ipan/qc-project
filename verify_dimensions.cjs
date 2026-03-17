const fs = require('fs');
const jsdom = require('jsdom');
const { JSDOM } = jsdom;
const path = require('path');

const htmlContent = `
<!DOCTYPE html>
<html>
<head></head>
<body>
    <form id="checksheetForm">
        <select id="itemSelect">
            <option value="1" data-part-number="123-ABC"></option>
        </select>
        <input type="text" class="dimension-input" id="dim1" name="dimensions[1][1]" value="">
        <input type="text" class="dimension-input" id="dim2" name="dimensions[1][2]" value="">
        <input type="text" class="dimension-input" id="dim3" name="dimensions[1][3]" value="">
        
        <input type="hidden" name="sampling_qty" value="5">
        <input type="hidden" name="total_ng" id="total_ng" value="0">
        <input type="hidden" name="total_ok" value="0">
        <select id="judgmentSelect"><option value="OK">OK</option><option value="NG">NG</option></select>
        <span id="judgmentBadge"></span>
        <div id="nextProsesContainer"></div>
        <select id="nextProses"></select>
        <button id="saveBtn"></button>
        <span id="acc_val"></span>
        <span id="rej_val"></span>
        <div id="aql_info"></div>
        <div id="weightCavContainer"></div>
        <div id="defectContainer"></div>
        <button id="addDefectBtn"></button>
        <canvas id="standardPdfCanvas"></canvas>
        <canvas id="similarPdfCanvas"></canvas>
        <canvas id="the-canvas"></canvas>
    </form>
</body>
</html>
`;

function runTests(filePath, fileType) {
    return new Promise((resolve) => {
        const dom = new JSDOM(htmlContent, { runScripts: "outside-only" });
        const window = dom.window;
        const document = window.document;
        
        // Mock getContext for canvas
        window.HTMLCanvasElement.prototype.getContext = () => ({
            clearRect: () => {},
            fillRect: () => {},
            fillText: () => {}
        });

        // Mock jQuery
        const $ = function(selector) {
            if (typeof selector === 'function') {
                return selector(); // document ready
            }
            if (selector === document) {
                 return {
                     on: (event, sel, handler) => {
                        window.addEventListener(event, (e) => {
                             if(e.target.matches && e.target.matches(sel)) {
                                 let mockEvent = { target: e.target, preventDefault: () => {} };
                                 if (handler) handler.call(e.target, mockEvent);
                             }
                        });
                     }
                 };
            }
            let el;
            if (typeof selector === 'string') {
                if (selector.startsWith('<')) {
                    const template = document.createElement('div');
                    template.innerHTML = selector;
                    el = Array.from(template.children);
                } else {
                    el = Array.from(document.querySelectorAll(selector));
                }
            } else if (selector instanceof window.HTMLElement || selector === window) {
                el = [selector];
            } else if (Array.isArray(selector)) {
                el = selector;
            }

            const wrap = function(elements) {
                if (!elements) elements = [];
                const res = {
                    length: elements.length,
                    each: function(fn) {
                        elements.forEach((e, i) => fn.call(e, i, e));
                        return this;
                    },
                    val: function(v) {
                        if (v === undefined) return elements[0] ? elements[0].value || '' : '';
                        elements.forEach(e => e.value = v);
                        return this;
                    },
                    attr: function(a, v) {
                         if (v === undefined) return elements[0] ? elements[0].getAttribute(a) : null;
                         elements.forEach(e => e.setAttribute(a, v));
                         return this;
                    },
                    addClass: function(c) {
                        elements.forEach(e => { c.split(' ').forEach(cls => e.classList.add(cls)); });
                        return this;
                    },
                    removeClass: function(c) {
                        elements.forEach(e => { c.split(' ').forEach(cls => e.classList.remove(cls)); });
                        return this;
                    },
                    hasClass: function(c) {
                        return elements.some(e => e.classList.contains(c));
                    },
                    find: function(s) {
                        let inner = [];
                        if (s.includes(':selected')) {
                             let cleanSelector = s.replace(':selected', '');
                             elements.forEach(e => inner.push(...Array.from(e.querySelectorAll(cleanSelector)).filter(el => el.selected || el.checked)));
                        } else {
                             elements.forEach(e => inner.push(...e.querySelectorAll(s)));
                        }
                        return wrap(inner);
                    },
                    trigger: function(ev) {
                        elements.forEach(e => e.dispatchEvent(new window.Event(ev, { bubbles: true })));
                        return this;
                    },
                    on: function(ev, handler) {
                        if (typeof ev === 'string' && ev.includes(',')) {
                            ev.split(',').forEach(e => this.on(e.trim(), handler));
                            return this;
                        }
                        elements.forEach(e => e.addEventListener(ev, function(event) {
                             handler.call(this, event);
                        }));
                        return this;
                    },
                    click: function(handler) {
                        if (handler) return this.on('click', handler);
                        this.trigger('click');
                        return this;
                    },
                    change: function(handler) {
                        if (handler) return this.on('change', handler);
                        this.trigger('change');
                        return this;
                    },
                    text: function(t) {
                        if (t === undefined) return elements[0] ? elements[0].textContent : "";
                        elements.forEach(e => e.textContent = t);
                        return this;
                    },
                    html: function(h) {
                        if (h === undefined) return elements[0] ? elements[0].innerHTML : "";
                        elements.forEach(e => e.innerHTML = h);
                        return this;
                    },
                    show: function() { elements.forEach(e => e.style.display = 'block'); return this; },
                    hide: function() { elements.forEach(e => e.style.display = 'none'); return this; },
                    toggle: function(cond) { elements.forEach(e => e.style.display = cond ? 'block' : 'none'); return this; },
                    css: function() { return this; },
                    prop: function(p, v) {
                         if (v === undefined) return elements[0] ? elements[0][p] : null;
                         elements.forEach(e => e[p] = v);
                         return this;
                    },
                    is: function(sel) {
                        return elements.some(e => {
                             if (sel === ':checked') return e.checked;
                             if (sel === ':selected') return e.selected;
                             return e.matches && e.matches(sel);
                        });
                    },
                    data: function(key) {
                        if (!elements[0]) return undefined;
                        const camelKey = key.replace(/-([a-z])/g, g => g[1].toUpperCase());
                        return elements[0].dataset ? elements[0].dataset[camelKey] : elements[0].getAttribute('data-' + key);
                    },
                    removeAttr: function(a) { elements.forEach(e => e.removeAttribute(a)); return this; },
                    append: function(html) { 
                         elements.forEach(e => {
                              if (typeof html === 'string') e.insertAdjacentHTML('beforeend', html);
                              else if (html.length) Array.from(html).forEach(n => e.appendChild(n.cloneNode(true)));
                         }); 
                         return this; 
                    },
                    appendTo: function(sel) {
                         const target = typeof sel === 'string' ? document.querySelector(sel) : (sel.length ? sel[0] : sel);
                         if (target) elements.forEach(e => target.appendChild(e));
                         return this;
                    },
                    empty: function() { elements.forEach(e => e.innerHTML = ''); return this; },
                    closest: function(sel) { return wrap(elements.map(e => e.closest(sel)).filter(Boolean)); }
                };
                Object.assign(res, elements);
                return res;
            };

            return wrap(el);
        };
        $.ajax = () => {};
        window.$ = window.jQuery = $;

        // Mock external libraries
        window.Swal = { fire: () => {} };
        window.pdfjsLib = { getDocument: () => ({ promise: new Promise(() => {}) }), GlobalWorkerOptions: {} };
        window.Html5Qrcode = class { start() { return new Promise(()=>{}); } };

        const scriptContent = fs.readFileSync(filePath, 'utf-8');
        try {
             dom.window.eval(scriptContent + '\nif(typeof InProcessCreate !== "undefined") window.InProcessCreate = InProcessCreate;\nif(typeof FpaCreate !== "undefined") window.FpaCreate = FpaCreate;');
        } catch(e) {
             console.error("Error evaluating script: " + e.message);
        }

        let instance;
        let testPassed = true;

        console.log(`\nTesting Dimension Validation in: ${filePath}`);

        try {
            if (fileType === 'InProcessCreate') {
                 // InProcess config
                 const config = {
                     partDimensionStandards: {
                         "123-ABC": {
                             "1": { min: "10.0", max: "10.5", size: null, tolerance: null }, // simple min/max
                             "2": { min: null, max: null, size: "20.0", tolerance: "+0.1/-0.2" }, // split tolerance
                             "3": { min: null, max: null, size: "+15.0", tolerance: null } // standard starts with op
                         }
                     },
                     plantContext: 'karawang'
                 };
                 instance = new window.InProcessCreate(config);
                 
                 const select = document.getElementById('itemSelect');
                 select.options[0].selected = true;
                 select.dispatchEvent(new window.Event('change'));
            } else if (fileType === 'FpaCreate') {
                 const config = {
                     currentDimensionStandards: {
                         "1": { min: "10.0", max: "10.5", size: null, tolerance: null },
                         "2": { min: null, max: null, size: "20.0", tolerance: "0.2" }, // FPA uses simpler tolerance
                     }
                 };
                 instance = new window.FpaCreate(config);
            }

            const dim1 = document.getElementById('dim1');
            const dim2 = document.getElementById('dim2');
            const dim3 = document.getElementById('dim3');

            // --- Test 1: +0 cleanup ---
            $(dim1).val('+010.2').trigger('input');
            if (dim1.value !== '10.2') {
                 console.log(`  [FAIL] Input cleanup: Expected '10.2', got '${dim1.value}'`);
                 testPassed = false;
            } else {
                 console.log(`  [PASS] Input cleanup (+0 prefix removed)`);
            }

            // --- Test 2: Valid Input ---
            $(dim1).val('10.3').trigger('input');
            $(dim2).val('20.05').trigger('input');
            
            // Allow time for async or timeout if any, though it's sync here
            if (dim1.classList.contains('is-invalid') || dim2.classList.contains('is-invalid')) {
                 console.log(`  [FAIL] Valid inputs marked as invalid. (dim1=${dim1.classList.contains('is-invalid')}, dim2=${dim2.classList.contains('is-invalid')})`);
                 testPassed = false;
                 console.log(dim1.className);
            } else {
                 console.log(`  [PASS] Valid Inputs within tolerance accepted`);
            }

            // --- Test 3: Invalid Input (Below Min) ---
            $(dim1).val('9.9').trigger('input');
            if (!dim1.classList.contains('is-invalid')) {
                 console.log(`  [FAIL] Invalid input (below min) NOT marked as invalid.`);
                 testPassed = false;
            } else {
                 console.log(`  [PASS] Invalid Input (below min) properly catches NG`);
            }

            // --- Test 4: Invalid Input (Above Tolerance bounds) ---
            if (fileType === 'InProcessCreate') {
                 // tolerance +0.1/-0.2 -> bounds [19.8, 20.1]
                 $(dim2).val('20.2').trigger('input');
                 if (!dim2.classList.contains('is-invalid')) {
                      console.log(`  [FAIL] Invalid input (above split tolerance) NOT marked as invalid.`);
                      testPassed = false;
                 } else {
                      console.log(`  [PASS] Invalid Input (above split tol) properly catches NG`);
                 }
                 
                 $(dim2).val('19.7').trigger('input');
                 if (!dim2.classList.contains('is-invalid')) {
                      console.log(`  [FAIL] Invalid input (below split tolerance) NOT marked as invalid.`);
                      testPassed = false;
                 } else {
                      console.log(`  [PASS] Invalid Input (below split tol) properly catches NG`);
                 }

                 $(dim3).val('15.1').trigger('input');
                 if (dim3.classList.contains('is-invalid')) {
                      console.log(`  [FAIL] Valid input (matches +15.0 op check incorrectly) marked as invalid.`);
                      testPassed = false;
                 } else {
                      console.log(`  [PASS] Size standard starting with '+' properly validated`);
                 }
            } else if (fileType === 'FpaCreate') {
                 $(dim2).val('20.3').trigger('input');
                 if (!dim2.classList.contains('is-invalid')) {
                      console.log(`  [FAIL] Invalid input (above flat tolerance) NOT marked as invalid.`);
                      testPassed = false;
                 } else {
                      console.log(`  [PASS] Invalid Input (above flat tol) properly catches NG`);
                 }
            }

        } catch (e) {
             console.error(`  [ERROR] Exception during tests: ${e.message}`);
             testPassed = false;
        }

        resolve(testPassed);
    });
}

async function main() {
    console.log("Installing jsdom for testing...");
    const cp = require('child_process');
    try {
        cp.execSync('npm install jsdom', { stdio: 'ignore' });
    } catch(e) {} // ignore if already installed
    
    let pass1 = await runTests('public/js/checksheet/in-process.js', 'InProcessCreate');
    let pass2 = await runTests('public/js/checksheet/fpa.js', 'FpaCreate');

    if (pass1 && pass2) {
        console.log("\n✅ ALL DIMENSION VALIDATION TESTS COMPLETED SUCCESSFULLY.");
    } else {
        console.log("\n❌ SOME TESTS FAILED.");
        process.exit(1);
    }
}

main();
