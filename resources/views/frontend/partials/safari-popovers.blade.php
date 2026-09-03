{{-- Shared SafariBookings-style popovers: Start Date calendar + Travellers selector.
    Generic binding — any page can include this once and wire triggers via attributes:

      Trigger (date):   data-safpop="date"  data-safpop-target="{hiddenInputId}"
                        data-safpop-flex="{flexHiddenInputId}"   (optional)
      Trigger (trav):   data-safpop="trav"
                        data-trav-total / data-trav-adults / data-trav-children
                        = ids of hidden inputs receiving committed values

    Both popovers render at body level (this partial is included at the end of the
    layout flow), so ancestor overflow:hidden can never clip them. --}}
<div class="start-date-calendar" id="startDateCalendar" role="dialog" aria-label="Choose start date" hidden>
    <div class="popover-pointer" aria-hidden="true"></div>
    <div class="sdc-header">
        <span class="sdc-title">Start Date</span>
        <button type="button" class="popover-close" data-popover-close aria-label="Close calendar">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
    </div>
    <div class="sdc-nav">
        <button type="button" class="sdc-nav-btn" id="sdcPrev" aria-label="Previous month">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 5-7 7 7 7"/></svg>
        </button>
        <div class="sdc-month" id="sdcMonth" aria-live="polite">Month</div>
        <button type="button" class="sdc-nav-btn sdc-nav-btn--next" id="sdcNext" aria-label="Next month">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg>
        </button>
    </div>
    <div class="sdc-weekdays" aria-hidden="true">
        <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
    </div>
    <div class="calendar-grid" id="sdcGrid" role="grid"></div>
</div>

<div class="travelers-popover" id="travellersPopover" role="dialog" aria-label="Travelers selector" hidden>
    <div class="popover-pointer" aria-hidden="true"></div>
    <div class="tp-header">
        <span class="tp-title">Travelers</span>
        <button type="button" class="popover-close" data-popover-close aria-label="Close travelers selector">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
    </div>
    <div class="tp-rows">
        <div class="tp-row">
            <div class="tp-row__text">
                <span class="tp-row__label">Adults</span>
                <span class="tp-row__age">(18+ years)</span>
            </div>
            <div class="tp-counter" role="group" aria-label="Adults count">
                <button type="button" class="tp-counter__btn" data-step="-1" data-target="adults" aria-label="Decrease adults">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14"/></svg>
                </button>
                <span class="tp-counter__value" id="tpAdults" aria-live="polite">2</span>
                <button type="button" class="tp-counter__btn tp-counter__btn--plus" data-step="1" data-target="adults" aria-label="Increase adults">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                </button>
            </div>
        </div>
        <div class="tp-row">
            <div class="tp-row__text">
                <span class="tp-row__label">Children</span>
                <span class="tp-row__age">(0–17 years)</span>
            </div>
            <div class="tp-counter" role="group" aria-label="Children count">
                <button type="button" class="tp-counter__btn" data-step="-1" data-target="children" aria-label="Decrease children">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14"/></svg>
                </button>
                <span class="tp-counter__value" id="tpChildren" aria-live="polite">0</span>
                <button type="button" class="tp-counter__btn tp-counter__btn--plus" data-step="1" data-target="children" aria-label="Increase children">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                </button>
            </div>
        </div>
    </div>
    <div class="tp-footer">
        <button type="button" class="tp-done" id="tpDone">Done</button>
    </div>
</div>

<script>
(function () {
    'use strict';

    var cal = document.getElementById('startDateCalendar');
    var pop = document.getElementById('travellersPopover');
    if (!cal || !pop) return;

    var openPop = null;
    var activeTrigger = null;
    var isMobile = function () { return window.matchMedia('(max-width: 767.98px)').matches; };

    function positionUnder(popEl, anchorEl, pointer, alignRight) {
        if (isMobile()) { popEl.style.left = ''; popEl.style.top = ''; return; }
        var r = anchorEl.getBoundingClientRect();
        var left = alignRight ? r.right + window.scrollX - popEl.offsetWidth : r.left + window.scrollX;
        var maxLeft = window.scrollX + document.documentElement.clientWidth - popEl.offsetWidth - 12;
        var minLeft = window.scrollX + 12;
        left = Math.min(Math.max(left, minLeft), Math.max(minLeft, maxLeft));
        popEl.style.left = left + 'px';
        popEl.style.top = (r.bottom + window.scrollY + 10) + 'px';
        if (pointer) {
            var center = (r.left + r.width / 2) + window.scrollX - left;
            pointer.style.left = Math.min(Math.max(center - 9, 14), popEl.offsetWidth - 32) + 'px';
        }
    }

    function open(which, trigger) {
        close(true);
        openPop = which;
        activeTrigger = trigger;
        var el = which === 'date' ? cal : pop;
        el.hidden = false; el.classList.add('is-open');
        trigger.setAttribute('aria-expanded', 'true');
        positionUnder(el,
            which === 'date' ? trigger : trigger,
            el.querySelector('.popover-pointer'),
            which === 'trav');
        if (which === 'trav') syncTempFromCommitted(trigger);
        else renderCalendar();
        var f = el.querySelector('button:not([disabled])');
        if (f) f.focus();
    }

    function close(silent) {
        if (!openPop) return;
        var el = openPop === 'date' ? cal : pop;
        el.hidden = true; el.classList.remove('is-open');
        if (activeTrigger) activeTrigger.setAttribute('aria-expanded', 'false');
        openPop = null;
        if (!silent && activeTrigger) { try { activeTrigger.focus(); } catch (e) {} }
    }

    document.addEventListener('click', function (e) {
        if (!openPop || !activeTrigger) return;
        var el = openPop === 'date' ? cal : pop;
        if (!el.contains(e.target) && !activeTrigger.contains(e.target)) close();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && openPop) close();
    });

    [cal, pop].forEach(function (el) {
        el.querySelectorAll('[data-popover-close]').forEach(function (b) {
            b.addEventListener('click', function () { close(); });
        });
    });

    /* ── Calendar ────────────────────────────────────────────── */
    var MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    var MONTHS_S = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    var DAYS_FULL = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    var pad = function (n) { return String(n).padStart(2, '0'); };
    var toISO = function (d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); };
    var norm = function (d) { return new Date(d.getFullYear(), d.getMonth(), d.getDate()); };
    var today = norm(new Date());
    var todayISO = toISO(today);

    var grid = document.getElementById('sdcGrid');
    var monthLbl = document.getElementById('sdcMonth');
    var prevBtn = document.getElementById('sdcPrev');
    var nextBtn = document.getElementById('sdcNext');

    var viewY = today.getFullYear(), viewM = today.getMonth();

    function renderCalendar() {
        var isoInput = activeTrigger && activeTrigger.dataset.safpopTarget
            ? document.getElementById(activeTrigger.dataset.safpopTarget) : null;
        var selectedIso = isoInput ? isoInput.value : '';
        monthLbl.textContent = MONTHS[viewM] + ' ' + viewY;
        prevBtn.disabled = (viewY === today.getFullYear() && viewM === today.getMonth());
        grid.innerHTML = '';
        var first = new Date(viewY, viewM, 1);
        var offset = (first.getDay() + 6) % 7;
        var start = new Date(viewY, viewM, 1 - offset);
        for (var i = 0; i < 42; i++) {
            (function () {
                var d = new Date(start.getFullYear(), start.getMonth(), start.getDate() + i);
                var iso = toISO(d);
                var past = norm(d) < today;
                var b = document.createElement('button');
                b.type = 'button';
                b.className = 'calendar-day' + (past ? ' is-past' : '') + (iso === todayISO ? ' is-today' : '');
                b.textContent = d.getDate();
                b.setAttribute('role', 'gridcell');
                b.setAttribute('aria-label', DAYS_FULL[d.getDay()] + ', ' + d.getDate() + ' ' + MONTHS[d.getMonth()] + ' ' + d.getFullYear());
                if (past) { b.disabled = true; b.tabIndex = -1; b.setAttribute('aria-disabled', 'true'); }
                else {
                    b.dataset.iso = iso; b.tabIndex = -1;
                    if (selectedIso === iso) b.classList.add('is-selected');
                    b.addEventListener('click', function () { pickDate(iso); });
                }
                grid.appendChild(b);
            })();
        }
        var sel = grid.querySelector('.is-selected:not(.is-past)');
        var fa = grid.querySelector('.calendar-day:not(.is-past)');
        (sel || fa || grid.firstElementChild).tabIndex = 0;
    }

    function pickDate(iso) {
        if (!activeTrigger) return;
        var isoInput = activeTrigger.dataset.safpopTarget ? document.getElementById(activeTrigger.dataset.safpopTarget) : null;
        if (isoInput) isoInput.value = iso;
        var p = iso.split('-');
        var disp = activeTrigger.querySelector('[data-safpop-display]');
        var txt = parseInt(p[2], 10) + ' ' + MONTHS_S[parseInt(p[1], 10) - 1] + ' ' + p[0];
        if (disp) disp.value = txt; else if ('value' in activeTrigger) activeTrigger.value = txt;
        close();
    }

    prevBtn.addEventListener('click', function () { viewM--; if (viewM < 0) { viewM = 11; viewY--; } renderCalendar(); });
    nextBtn.addEventListener('click', function () { viewM++; if (viewM > 11) { viewM = 0; viewY++; } renderCalendar(); });

    grid.addEventListener('keydown', function (e) {
        var btns = Array.prototype.slice.call(grid.querySelectorAll('.calendar-day'));
        var idx = btns.indexOf(document.activeElement);
        if (idx === -1) return;
        var step = { ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7 }[e.key];
        if (!step) return;
        e.preventDefault();
        var n = idx + step;
        while (n >= 0 && n < btns.length && btns[n].disabled) n += (step > 0 ? 1 : -1);
        if (n >= 0 && n < btns.length) { btns[idx].tabIndex = -1; btns[n].tabIndex = 0; btns[n].focus(); }
    });

    /* ── Travellers ──────────────────────────────────────────── */
    var MAX_TOTAL = 12;
    var temp = { adults: 2, children: 0 };
    var committedTrigger = null;
    var aOut = document.getElementById('tpAdults');
    var cOut = document.getElementById('tpChildren');

    function readCommitted(trigger) {
        var get = function (key, dflt) {
            var id = trigger.dataset['trav' + key];
            var el = id ? document.getElementById(id) : null;
            var v = el ? parseInt(el.value, 10) : NaN;
            return isNaN(v) ? dflt : v;
        };
        return { adults: Math.max(1, get('Adults', 2)), children: Math.max(0, get('Children', 0)) };
    }

    function summary(a, c) {
        return a + ' ' + (a === 1 ? 'Adult' : 'Adults') + (c > 0 ? ', ' + c + ' ' + (c === 1 ? 'Child' : 'Children') : '');
    }

    function paintTemp() {
        aOut.textContent = temp.adults;
        cOut.textContent = temp.children;
        var total = temp.adults + temp.children;
        pop.querySelectorAll('.tp-counter__btn').forEach(function (b) {
            var t = b.dataset.target, s = parseInt(b.dataset.step, 10);
            var v = temp[t];
            b.disabled = (s < 0) ? v <= (t === 'adults' ? 1 : 0)
                                 : v >= MAX_TOTAL || total >= MAX_TOTAL;
        });
    }

    function syncTempFromCommitted(trigger) {
        committedTrigger = trigger;
        var c = readCommitted(trigger);
        temp.adults = c.adults; temp.children = c.children;
        paintTemp();
    }

    pop.querySelectorAll('.tp-counter__btn').forEach(function (b) {
        b.addEventListener('click', function () {
            var t = b.dataset.target, d = parseInt(b.dataset.step, 10);
            var min = t === 'adults' ? 1 : 0;
            var nv = temp[t] + d;
            if (nv < min || nv > MAX_TOTAL || (temp.adults + temp.children >= MAX_TOTAL && d > 0)) return;
            temp[t] = nv;
            paintTemp();
        });
    });

    document.getElementById('tpDone').addEventListener('click', function () {
        if (!committedTrigger) { close(); return; }
        var set = function (key, val) {
            var id = committedTrigger.dataset['trav' + key];
            var el = id ? document.getElementById(id) : null;
            if (el) el.value = val;
        };
        set('Total', temp.adults + temp.children);
        set('Adults', temp.adults);
        set('Children', temp.children);
        var disp = committedTrigger.querySelector('[data-safpop-display]');
        var txt = summary(temp.adults, temp.children);
        if (disp) disp.value = txt; else if ('value' in committedTrigger) committedTrigger.value = txt;
        close();
    });

    /* ── Trigger wiring ──────────────────────────────────────── */
    document.querySelectorAll('[data-safpop]').forEach(function (t) {
        t.addEventListener('click', function () {
            if (openPop === (t.dataset.safpop === 'date' ? 'date' : 'trav')) { close(); return; }
            open(t.dataset.safpop === 'date' ? 'date' : 'trav', t);
        });
        t.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); t.click(); }
        });
    });

    window.addEventListener('scroll', function () {
        if (!openPop || !activeTrigger || isMobile()) return;
        positionUnder(openPop === 'date' ? cal : pop, activeTrigger,
            (openPop === 'date' ? cal : pop).querySelector('.popover-pointer'),
            openPop === 'trav');
    }, { passive: true });
    window.addEventListener('resize', function () {
        if (!openPop || !activeTrigger || isMobile()) return;
        positionUnder(openPop === 'date' ? cal : pop, activeTrigger,
            (openPop === 'date' ? cal : pop).querySelector('.popover-pointer'),
            openPop === 'trav');
    });
})();
</script>
