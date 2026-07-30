/**
 * Item Part Search Autocomplete
 * Usage: initItemSearch(selectId, options?)
 *   selectId       – id of the <select name="item_id"> element
 *   options        – { placeholder, maxResults, startButtonId }
 *
 * Lock behaviour:
 *   The search input mirrors the disabled state of the underlying <select>.
 *   If a startButtonId is provided (default: 'startTimerBtn'), the widget
 *   also listens for that button's click and unlocks at that moment — same
 *   as every other field in the checksheet forms.
 */
(function (global) {
    'use strict';

    var STYLE_ID = 'item-search-styles';

    function injectStyles() {
        if (document.getElementById(STYLE_ID)) return;
        var style = document.createElement('style');
        style.id = STYLE_ID;
        style.textContent = [
            '.ips-wrapper { position: relative; margin-bottom: 6px; }',
            '.ips-input {',
            '  width: 100%; box-sizing: border-box;',
            '  padding: 6px 32px 6px 10px;',
            '  border: 1px solid #ced4da; border-radius: 4px;',
            '  font-size: 13px; outline: none; transition: border-color .15s;',
            '}',
            '.ips-input:focus { border-color: #4e73df; box-shadow: 0 0 0 2px rgba(78,115,223,.25); }',
            '.ips-clear {',
            '  position: absolute; right: 8px; top: 50%; transform: translateY(-50%);',
            '  cursor: pointer; color: #aaa; font-size: 14px; line-height: 1;',
            '  display: none; user-select: none;',
            '}',
            '.ips-clear:hover { color: #333; }',
            '.ips-dropdown {',
            '  position: absolute; z-index: 9999; box-sizing: border-box;',
            '  max-height: 400px; overflow-y: auto;',
            '  background: #fff; border: 1px solid #ced4da; border-radius: 4px;',
            '  box-shadow: 0 4px 12px rgba(0,0,0,.12);',
            '  display: none; margin-top: 2px;',
            '}',
            '.ips-item {',
            '  padding: 7px 10px; cursor: pointer; font-size: 12px;',
            '  border-bottom: 1px solid #f0f0f0;',
            '}',
            '.ips-item:last-child { border-bottom: none; }',
            '.ips-item:hover, .ips-item.ips-active { background: #eef2ff; }',
            '.ips-item .ips-name { font-weight: 600; color: #333; }',
            '.ips-item .ips-sub { color: #888; font-size: 11px; margin-top: 1px; }',
            '.ips-item mark { background: #fef08a; padding: 0; border-radius: 2px; }',
            '.ips-empty { padding: 8px 10px; color: #999; font-size: 12px; font-style: italic; }',
            '.ips-badge { display:inline-block; padding:1px 5px; border-radius:10px;',
            '  font-size:10px; font-weight:600; margin-left:4px;',
            '  background:#e8f5e9; color:#388e3c; }',
            '.ips-input:disabled, .ips-input[readonly] {',
            '  background:#e9ecef; color:#6c757d; cursor:not-allowed;',
            '  border-color:#ced4da;',
            '}',
            '.ips-detail { color:#4e73df !important; font-size:10px !important; }',
        ].join('\n');
        document.head.appendChild(style);
    }

    function escapeRegex(str) {
        return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function highlight(text, query) {
        if (!query) return text;
        var re = new RegExp('(' + escapeRegex(query) + ')', 'gi');
        return text.replace(re, '<mark>$1</mark>');
    }

    /**
     * @param {string|HTMLSelectElement} selectEl  – id or element of the target <select>
     * @param {object} [opts]
     * @param {string} [opts.placeholder]
     * @param {number} [opts.maxResults=50]
     * @param {string} [opts.startButtonId='startTimerBtn']  – button that unlocks all fields
     */
    function initItemSearch(selectEl, opts) {
        if (typeof selectEl === 'string') {
            selectEl = document.getElementById(selectEl);
        }
        if (!selectEl) return;

        // Don't double-init
        if (selectEl.dataset.ipsInit) return;
        selectEl.dataset.ipsInit = '1';

        opts = opts || {};
        var placeholder = opts.placeholder || 'Cari items / part number / kode sap...';
        var maxResults = opts.maxResults || 60;
        var startBtnId = opts.startButtonId !== undefined ? opts.startButtonId : 'startTimerBtn';

        injectStyles();

        /* ── Build wrapper ── */
        var wrapper = document.createElement('div');
        wrapper.className = 'ips-wrapper';

        var input = document.createElement('input');
        input.type = 'text';
        input.className = 'ips-input no-autoupper';
        input.placeholder = placeholder;
        input.autocomplete = 'off';
        input.spellcheck = false;

        var clearBtn = document.createElement('span');
        clearBtn.className = 'ips-clear';
        clearBtn.innerHTML = '&times;';
        clearBtn.title = 'Hapus pencarian';

        var dropdown = document.createElement('div');
        dropdown.className = 'ips-dropdown';

        wrapper.appendChild(input);
        wrapper.appendChild(clearBtn);
        document.body.appendChild(dropdown);

        selectEl.parentNode.insertBefore(wrapper, selectEl);

        /* ── Helpers ── */
        function getOptions() {
            var opts = [];
            for (var i = 0; i < selectEl.options.length; i++) {
                var opt = selectEl.options[i];
                if (!opt.value) continue; // skip placeholder option
                opts.push(opt);
            }
            return opts;
        }

        function matchOption(opt, q) {
            var name = (opt.dataset.name || opt.text || '').toLowerCase();
            var partNo = (opt.dataset.partNumber || opt.dataset.part_number || '').toLowerCase();
            var sapCode = (opt.dataset.sapCode || opt.dataset.sap_code || '').toLowerCase();
            var customer = (opt.dataset.customer || '').toLowerCase();
            var detail = (opt.dataset.detail || '').toLowerCase();
            return name.indexOf(q) !== -1 || partNo.indexOf(q) !== -1 || sapCode.indexOf(q) !== -1 || customer.indexOf(q) !== -1 || detail.indexOf(q) !== -1;
        }

        var activeIdx = -1;
        var currentItems = [];

        function renderDropdown(query) {
            var q = query.toLowerCase().trim();
            dropdown.innerHTML = '';
            activeIdx = -1;
            currentItems = [];

            var allOpts = getOptions();
            var matched = q ? allOpts.filter(function (o) { return matchOption(o, q); }) : allOpts;

            if (matched.length === 0) {
                var empty = document.createElement('div');
                empty.className = 'ips-empty';
                empty.textContent = 'Tidak ada item ditemukan';
                dropdown.appendChild(empty);
                dropdown.style.display = 'block';
                updateDropdownPosition();
                return;
            }

            var shown = matched.slice(0, maxResults);
            currentItems = shown;

            shown.forEach(function (opt, idx) {
                var name = opt.dataset.name || opt.text || '';
                var partNo = opt.dataset.partNumber || opt.dataset.part_number || '';
                var sapCode = opt.dataset.sapCode || opt.dataset.sap_code || '';
                var customer = opt.dataset.customer || '';
                var detail = opt.dataset.detail || '';

                var item = document.createElement('div');
                item.className = 'ips-item';
                item.dataset.idx = idx;

                var sub = [];
                if (customer) sub.push(customer);
                if (partNo) sub.push(partNo);
                if (sapCode) sub.push('SAP: ' + sapCode);

                item.innerHTML =
                    '<div class="ips-name">' + highlight(name, q) + '</div>' +
                    (sub.length ? '<div class="ips-sub">' + highlight(sub.join(' · '), q) + '</div>' : '') +
                    (detail ? '<div class="ips-sub ips-detail">' + highlight(detail, q) + '</div>' : '');

                item.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    selectOption(opt);
                    closeDropdown();
                });
                dropdown.appendChild(item);
            });

            if (matched.length > maxResults) {
                var more = document.createElement('div');
                more.className = 'ips-empty';
                more.textContent = '...dan ' + (matched.length - maxResults) + ' item lainnya. Ketik lebih spesifik.';
                dropdown.appendChild(more);
            }

            dropdown.style.display = 'block';
            updateDropdownPosition();
        }

        function updateDropdownPosition() {
            var rect = input.getBoundingClientRect();
            var scrollY = window.pageYOffset || document.documentElement.scrollTop;
            var scrollX = window.pageXOffset || document.documentElement.scrollLeft;

            dropdown.style.setProperty('position', 'absolute', 'important');
            dropdown.style.setProperty('top', (rect.bottom + scrollY) + 'px', 'important');
            dropdown.style.setProperty('left', (rect.left + scrollX) + 'px', 'important');
            dropdown.style.setProperty('width', rect.width + 'px', 'important');
            dropdown.style.setProperty('margin', '0', 'important');
            dropdown.style.setProperty('z-index', '999999', 'important');
            dropdown.style.setProperty('display', 'block', 'important');
        }

        // Only close on resize, no need to close on scroll anymore as it scrolls with the page
        window.addEventListener('resize', closeDropdown);

        function selectOption(opt) {
            selectEl.value = opt.value;
            // Display part name and part number in search box
            var partNo = opt.dataset.partNumber || opt.dataset.part_number;
            var name = opt.dataset.name || opt.text || '';
            var displayText = name;
            if (partNo && partNo !== '-' && name.indexOf(partNo) === -1) {
                displayText += ' - ' + partNo;
            }
            input.value = displayText.replace(/\s*\(.*\)\s*(-\s*SAP:.*)?$/i, '').trim();
            clearBtn.style.display = 'inline';
            // Trigger change so existing JS listeners fire
            var ev = new Event('change', { bubbles: true });
            selectEl.dispatchEvent(ev);
        }

        function closeDropdown() {
            dropdown.style.display = 'none';
            activeIdx = -1;
        }

        function setActive(idx) {
            var items = dropdown.querySelectorAll('.ips-item');
            items.forEach(function (el) { el.classList.remove('ips-active'); });
            if (idx >= 0 && idx < items.length) {
                items[idx].classList.add('ips-active');
                items[idx].scrollIntoView({ block: 'nearest' });
            }
            activeIdx = idx;
        }

        /* ── Events ── */
        input.addEventListener('input', function () {
            var q = input.value.trim();
            clearBtn.style.display = q ? 'inline' : 'none';
            renderDropdown(input.value);
        });

        input.addEventListener('focus', function () {
            renderDropdown(input.value);
        });

        input.addEventListener('blur', function () {
            setTimeout(closeDropdown, 150);
        });

        input.addEventListener('keydown', function (e) {
            var items = dropdown.querySelectorAll('.ips-item');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                setActive(Math.min(activeIdx + 1, items.length - 1));
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                setActive(Math.max(activeIdx - 1, 0));
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeIdx >= 0 && currentItems[activeIdx]) {
                    selectOption(currentItems[activeIdx]);
                    closeDropdown();
                }
            } else if (e.key === 'Escape') {
                closeDropdown();
            }
        });

        clearBtn.addEventListener('click', function () {
            input.value = '';
            clearBtn.style.display = 'none';
            selectEl.value = '';
            var ev = new Event('change', { bubbles: true });
            selectEl.dispatchEvent(ev);
            closeDropdown();
            input.focus();
        });

        /* ── Lock / unlock to match other form fields ── */
        function applyLockState(locked) {
            input.disabled = locked;
            if (locked) {
                input.placeholder = 'Klik Start terlebih dahulu...';
                input.style.cursor = 'not-allowed';
                clearBtn.style.display = 'none';
                closeDropdown();
            } else {
                input.placeholder = placeholder;
                input.style.cursor = '';
            }
        }

        // Mirror disabled state of the underlying <select> on init
        applyLockState(selectEl.disabled);

        // Listen to the start button (same element that unlocks other fields)
        if (startBtnId) {
            var startBtn = document.getElementById(startBtnId);
            if (startBtn) {
                startBtn.addEventListener('click', function () {
                    applyLockState(false);
                });
            }
        }

        // Also observe if <select> itself gets enabled/disabled externally
        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (m) {
                if (m.attributeName === 'disabled') {
                    applyLockState(selectEl.disabled);
                }
            });
        });
        observer.observe(selectEl, { attributes: true, attributeFilter: ['disabled'] });

        /* ── Sync if select already has a value (e.g. after validation error or external script update) ── */
        var syncDisplay = function () {
            if (selectEl.value) {
                var selOpt = selectEl.options[selectEl.selectedIndex];
                if (selOpt) {
                    var pNo = selOpt.dataset.partNumber || selOpt.dataset.part_number;
                    var nme = selOpt.dataset.name || selOpt.text || '';
                    var displayText = nme;
                    if (pNo && pNo !== '-' && nme.indexOf(pNo) === -1) {
                        displayText += ' - ' + pNo;
                    }
                    input.value = displayText.replace(/\s*\(.*\)\s*(-\s*SAP:.*)?$/i, '').trim();
                    clearBtn.style.display = 'inline';
                }
            } else {
                input.value = '';
                clearBtn.style.display = 'none';
            }
        };

        // Sync on init
        syncDisplay();

        // Sync on external change (e.g. scanner or manual trigger)
        // We use both jQuery and native listener for maximum compatibility
        if (typeof jQuery !== 'undefined') {
            jQuery(selectEl).on('change', syncDisplay);
        } else {
            selectEl.addEventListener('change', syncDisplay);
        }

        /* ── Expose helper so external code can reset the search box ── */
        selectEl._ipsReset = function () {
            input.value = '';
            clearBtn.style.display = 'none';
        };
    }

    global.initItemSearch = initItemSearch;
})(window);
