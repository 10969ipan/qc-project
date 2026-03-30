/**
 * Item Part Search Autocomplete
 * Usage: initItemSearch(selectId, options?)
 *   selectId  – id of the <select name="item_id"> element
 *   options   – { placeholder, maxResults }
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
            '  position: absolute; z-index: 9999; width: 100%;',
            '  max-height: 220px; overflow-y: auto;',
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
        var placeholder = opts.placeholder || 'Cari nama / part no / SAP...';
        var maxResults  = opts.maxResults  || 60;

        injectStyles();

        /* ── Build wrapper ── */
        var wrapper = document.createElement('div');
        wrapper.className = 'ips-wrapper';

        var input = document.createElement('input');
        input.type = 'text';
        input.className = 'ips-input';
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
        wrapper.appendChild(dropdown);

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
            var name    = (opt.dataset.name     || opt.text || '').toLowerCase();
            var partNo  = (opt.dataset.partNumber || '').toLowerCase();
            var sapCode = (opt.dataset.sapCode   || opt.dataset.sap_code || '').toLowerCase();
            return name.indexOf(q) !== -1 || partNo.indexOf(q) !== -1 || sapCode.indexOf(q) !== -1;
        }

        var activeIdx = -1;
        var currentItems = [];

        function renderDropdown(query) {
            var q = query.toLowerCase().trim();
            dropdown.innerHTML = '';
            activeIdx = -1;
            currentItems = [];

            var allOpts = getOptions();
            var matched = q ? allOpts.filter(function(o){ return matchOption(o, q); }) : allOpts;

            if (matched.length === 0) {
                var empty = document.createElement('div');
                empty.className = 'ips-empty';
                empty.textContent = 'Tidak ada item ditemukan';
                dropdown.appendChild(empty);
                dropdown.style.display = 'block';
                return;
            }

            var shown = matched.slice(0, maxResults);
            currentItems = shown;

            shown.forEach(function(opt, idx) {
                var name    = opt.dataset.name     || opt.text || '';
                var partNo  = opt.dataset.partNumber || '';
                var sapCode = opt.dataset.sapCode   || opt.dataset.sap_code || '';
                var customer= opt.dataset.customer  || '';

                var item = document.createElement('div');
                item.className = 'ips-item';
                item.dataset.idx = idx;

                var sub = [];
                if (partNo)  sub.push(partNo);
                if (sapCode) sub.push('SAP: ' + sapCode);
                if (customer)sub.push(customer);

                item.innerHTML =
                    '<div class="ips-name">' + highlight(name, q) + '</div>' +
                    (sub.length ? '<div class="ips-sub">' + highlight(sub.join(' · '), q) + '</div>' : '');

                item.addEventListener('mousedown', function(e) {
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
        }

        function selectOption(opt) {
            selectEl.value = opt.value;
            // Display name in search box
            input.value = (opt.dataset.name || opt.text || '').replace(/\s*\(.*\)\s*(-\s*SAP:.*)?$/i, '').trim();
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
            items.forEach(function(el) { el.classList.remove('ips-active'); });
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

        /* ── Sync if select already has a value (e.g. after validation error) ── */
        if (selectEl.value) {
            var selOpt = selectEl.options[selectEl.selectedIndex];
            if (selOpt) {
                input.value = (selOpt.dataset.name || selOpt.text || '').replace(/\s*\(.*\)\s*(-\s*SAP:.*)?$/i, '').trim();
                clearBtn.style.display = 'inline';
            }
        }

        /* ── Expose helper so external code can reset the search box ── */
        selectEl._ipsReset = function () {
            input.value = '';
            clearBtn.style.display = 'none';
        };
    }

    global.initItemSearch = initItemSearch;
})(window);
