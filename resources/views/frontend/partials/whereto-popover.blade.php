{{-- Searchable "Where To" destination selector (SafariBookings-style).
    Rendered at body level so nothing can clip it; positioned by JS under
    .sfb-whereto__field. Mobile: fixed full-width panel under the field.

    Wire-up (tours listing sidebar):
      [data-sfb-wt]            wrapper containing field + hidden input
      [data-sfb-wt-input]      visible combobox input
      [data-sfb-wt-value]      hidden input receiving slug/id for submission
      [data-sfb-wt-icon]       search icon (shown when no selection)
      [data-sfb-wt-remove]     circular X clearing the selection
      [data-sfb-show-tours]    submit button whose count updates on selection --}}
<div class="wt-dropdown" id="sfbWheretoDropdown" role="dialog" aria-label="Search destinations" hidden data-search-url="{{ route('tours.searchDestinations') }}">
    <div class="popover-pointer wt-pointer" aria-hidden="true"></div>
    <div class="wt-header">
        <span>Start typing or select below</span>
        <button type="button" class="popover-close" data-sfb-wt-close aria-label="Close destination search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
    </div>
    <div class="wt-results" id="sfbWheretoListbox" role="listbox" aria-label="Destinations"></div>
</div>

<script>
(function () {
    'use strict';

    var wrapper = document.querySelector('[data-sfb-wt]');
    var dropdown = document.getElementById('sfbWheretoDropdown');
    if (!wrapper || !dropdown) return;

    var input = wrapper.querySelector('[data-sfb-wt-input]');
    var hidden = wrapper.querySelector('[data-sfb-wt-value]');
    var field = wrapper.querySelector('[data-sfb-wt-field]');
    var searchIcon = wrapper.querySelector('[data-sfb-wt-icon]');
    var removeBtn = wrapper.querySelector('[data-sfb-wt-remove]');
    var showBtn = document.querySelector('[data-sfb-show-tours]');
    var showCount = document.querySelector('[data-sfb-show-count]');
    var results = document.getElementById('sfbWheretoListbox');
    var closeBtn = dropdown.querySelector('[data-sfb-wt-close]');
    var pointer = dropdown.querySelector('.wt-pointer');

    var SEARCH_URL = dropdown.dataset.searchUrl;
    var TOTAL = showBtn ? parseInt(showBtn.dataset.total, 10) : 0;
    var isOpen = false;
    var activeIndex = -1;
    var options = [];
    var cache = {};
    var fetchTimer = null;
    var abortCtrl = null;

    function isMobile() { return window.matchMedia('(max-width: 767.98px)').matches; }

    function escapeHtml(text) {
        return String(text).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function highlight(name, query) {
        var safe = escapeHtml(name);
        if (!query) return safe;
        var idx = name.toLowerCase().indexOf(query.toLowerCase());
        if (idx === -1) return safe;
        return escapeHtml(name.slice(0, idx))
            + '<mark>' + escapeHtml(name.slice(idx, idx + query.length)) + '</mark>'
            + escapeHtml(name.slice(idx + query.length));
    }

    /* ── Rendering ───────────────────────────────────────────── */

    function renderLoading() {
        results.innerHTML = '<div class="wt-state"><span class="wt-spinner" aria-hidden="true"></span>Searching destinations&hellip;</div>';
    }

    function renderEmpty() {
        results.innerHTML = '<div class="wt-state">No destinations found</div>';
    }

    function renderRows(payload, query) {
        options = [];
        var html = '';
        if (!query) {
            html += rowHtml({
                slug: '',
                name: 'All Safari Destinations',
                subtitle: 'Search Everywhere',
                count: payload.total != null ? payload.total : TOTAL
            });
        }
        payload.results.forEach(function (entry) { html += rowHtml(entry); });
        results.innerHTML = html || '';
        if (!html) { renderEmpty(); return; }

        options = Array.prototype.slice.call(results.querySelectorAll('.wt-item'));
        setActive(0);
    }

    function rowHtml(entry) {
        var index = options.length;
        return '<button type="button" class="wt-item" role="option" id="wt-opt-' + index + '"'
            + ' data-slug="' + escapeHtml(entry.slug == null ? '' : entry.slug) + '"'
            + ' data-count="' + (parseInt(entry.count, 10) || 0) + '"'
            + ' aria-selected="false">'
            + '<span class="wt-item__text"><strong>' + highlight(entry.name, currentQuery)
            + '</strong><span class="wt-item__sub">' + escapeHtml(entry.subtitle || '') + '</span></span>'
            + '</button>';
    }

    /* ── Data ────────────────────────────────────────────────── */

    var currentQuery = '';

    function loadResults(query) {
        currentQuery = query;
        if (Object.prototype.hasOwnProperty.call(cache, query)) {
            renderRows(cache[query], query);
            return;
        }
        renderLoading();
        if (abortCtrl) abortCtrl.abort();
        abortCtrl = typeof AbortController !== 'undefined' ? new AbortController() : null;
        fetch(SEARCH_URL + '?q=' + encodeURIComponent(query), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal: abortCtrl ? abortCtrl.signal : undefined
        })
            .then(function (res) { return res.ok ? res.json() : Promise.reject(res.status); })
            .then(function (payload) {
                cache[query] = payload;
                if (currentQuery === query && isOpen) renderRows(payload, query);
            })
            .catch(function () {
                if (currentQuery === query && isOpen) renderEmpty();
            });
    }

    /* ── Positioning ─────────────────────────────────────────── */

    function position() {
        if (!isOpen) return;
        var r = field.getBoundingClientRect();
        if (isMobile()) {
            dropdown.style.left = '12px';
            dropdown.style.right = '12px';
            dropdown.style.width = '';
            dropdown.style.top = (r.bottom + window.scrollY + 8) + 'px';
            pointer.style.display = 'none';
            return;
        }
        var left = r.left + window.scrollX;
        var width = Math.max(r.width, 470);
        var maxLeft = window.scrollX + document.documentElement.clientWidth - width - 12;
        left = Math.min(left, Math.max(window.scrollX + 12, maxLeft));
        dropdown.style.left = left + 'px';
        dropdown.style.top = (r.bottom + window.scrollY + 10) + 'px';
        dropdown.style.width = width + 'px';
        pointer.style.display = '';
        pointer.style.left = Math.min(Math.max((r.left + 28 + window.scrollX) - left - 9, 14), width - 32) + 'px';
    }

    /* ── Open / close ────────────────────────────────────────── */

    function openDropdown() {
        closeAllOtherPopovers();
        isOpen = true;
        dropdown.hidden = false;
        dropdown.classList.add('is-open');
        input.setAttribute('aria-expanded', 'true');
        position();
        loadResults(input.value && hidden.value ? '' : input.value);
        input.focus();
    }

    function closeDropdown(refocus) {
        if (!isOpen) return;
        isOpen = false;
        dropdown.hidden = true;
        dropdown.classList.remove('is-open');
        input.setAttribute('aria-expanded', 'false');
        if (refocus) input.focus();
    }

    function closeAllOtherPopovers() {
        ['startDateCalendar', 'travellersPopover'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el && !el.hidden) {
                el.hidden = true;
                el.classList.remove('is-open');
            }
        });
    }

    /* ── Selection ───────────────────────────────────────────── */

    function applySelection(value, label, count) {
        hidden.value = value;
        input.value = label;
        field.classList.toggle('has-value', !!value);
        searchIcon.hidden = !!value;
        removeBtn.hidden = !value;
        if (showCount) showCount.textContent = (parseInt(count, 10) || 0).toLocaleString();
    }

    function choose(optionEl) {
        if (!optionEl) return;
        var slug = optionEl.dataset.slug || '';
        var label = slug ? optionEl.querySelector('strong').textContent : '';
        applySelection(slug, label, optionEl.dataset.count);
        closeDropdown(true);
    }

    function setActive(index) {
        if (!options.length) return;
        activeIndex = Math.max(0, Math.min(index, options.length - 1));
        options.forEach(function (opt, i) {
            opt.classList.toggle('is-highlighted', i === activeIndex);
            opt.setAttribute('aria-selected', i === activeIndex ? 'true' : 'false');
        });
        var el = options[activeIndex];
        if (el) {
            input.setAttribute('aria-activedescendant', el.id);
            if (el.scrollIntoViewIfNeeded) el.scrollIntoViewIfNeeded(false);
        }
    }

    /* ── Events ──────────────────────────────────────────────── */

    field.addEventListener('click', function (event) {
        if (event.target.closest('[data-sfb-wt-remove]')) return;
        if (!isOpen) openDropdown();
        else input.focus();
    });

    input.addEventListener('input', function () {
        hidden.value = '';
        field.classList.remove('has-value');
        searchIcon.hidden = false;
        removeBtn.hidden = true;
        window.clearTimeout(fetchTimer);
        fetchTimer = window.setTimeout(function () { loadResults(input.value.trim()); }, 300);
        if (!isOpen) openDropdown();
    });

    removeBtn.addEventListener('click', function (event) {
        event.stopPropagation();
        applySelection('', '', TOTAL);
        closeDropdown(false);
    });

    closeBtn.addEventListener('click', function () { closeDropdown(true); });

    results.addEventListener('click', function (event) {
        var item = event.target.closest('.wt-item');
        if (item) choose(item);
    });

    results.addEventListener('mousedown', function (event) {
        if (event.target.closest('.wt-item')) event.preventDefault();
    });

    input.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowDown') { event.preventDefault(); if (!isOpen) openDropdown(); else setActive(activeIndex + 1); }
        else if (event.key === 'ArrowUp') { event.preventDefault(); if (isOpen) setActive(activeIndex - 1); }
        else if (event.key === 'Enter') { event.preventDefault(); if (isOpen && options[activeIndex]) choose(options[activeIndex]); }
        else if (event.key === 'Escape') { event.preventDefault(); closeDropdown(true); }
        else if (event.key === 'Tab') { closeDropdown(false); }
    });

    document.addEventListener('click', function (event) {
        if (!isOpen) return;
        if (!dropdown.contains(event.target) && !wrapper.contains(event.target)) closeDropdown(false);
    });

    window.addEventListener('scroll', function () { position(); }, { passive: true });
    window.addEventListener('resize', function () { position(); });
})();
</script>
