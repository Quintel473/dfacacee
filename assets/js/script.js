/* D'FACEEE booking wizard */
(function () {
    'use strict';

    // Set this to an endpoint returning {"booked": ["10:00", "14:00"]} for ?date=YYYY-MM-DD
    // to hide already-booked slots. Leave null to show all default slots.
    var AVAILABILITY_URL = null;

    var DEFAULT_SLOTS = ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'];

    var currentStep = 1;
    var state = { name: '', price: 0, duration: '' };

    function $(id) { return document.getElementById(id); }

    function setText(id, value) {
        var el = $(id);
        if (el) el.textContent = value;
    }

    function money(n) { return '$' + Number(n).toFixed(2); }

    function formatDate(iso) {
        if (!iso) return '-';
        var p = iso.split('-');
        var d = new Date(Number(p[0]), Number(p[1]) - 1, Number(p[2]));
        return d.toLocaleDateString(undefined, { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
    }

    function formatTime(t) {
        if (!t) return '-';
        var p = t.split(':');
        var h = Number(p[0]);
        var suffix = h >= 12 ? 'PM' : 'AM';
        return ((h % 12) || 12) + ':' + p[1] + ' ' + suffix;
    }

    function showError(step, msg) {
        var el = $('step' + step + '-error');
        if (!el) { alert(msg); return; }
        el.textContent = msg;
        el.style.display = msg ? 'block' : 'none';
    }

    function showStep(n) {
        for (var i = 1; i <= 4; i++) {
            var s = $('step' + i);
            var ind = $('step' + i + '-indicator');
            if (s) s.classList.toggle('active', i === n);
            if (ind) {
                ind.classList.toggle('active', i === n);
                ind.classList.toggle('completed', i < n);
            }
        }
        currentStep = n;
        var container = document.querySelector('.booking-container');
        if (container && container.scrollIntoView) container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    /* ---------- Sidebar ---------- */
    function updateSidebar() {
        var deposit = state.price * 0.5;
        setText('sideService', state.name || 'Not selected');
        setText('sideDuration', state.duration || '-');
        setText('sideDate', formatDate($('date').value));
        setText('sideTime', $('time').value ? formatTime($('time').value) : '-');
        setText('sidePrice', money(state.price));
        setText('sideDeposit', money(deposit));
        setText('sideBalance', money(state.price - deposit));
    }

    /* ---------- Step 1: service selection ---------- */
    function selectService(card) {
        var cards = document.querySelectorAll('.service-card');
        for (var i = 0; i < cards.length; i++) cards[i].classList.remove('selected');
        card.classList.add('selected');

        state.name = card.getAttribute('data-name') || '';
        state.price = parseFloat(card.getAttribute('data-price')) || 0;
        state.duration = card.getAttribute('data-duration') || '';

        $('selectedService').value = state.name;
        $('selectedPrice').value = state.price;
        $('selectedDuration').value = state.duration;

        showError(1, '');
        updateSidebar();
    }

    /* ---------- Step 2: date & time ---------- */
    function todayISO() {
        var d = new Date();
        var m = String(d.getMonth() + 1).padStart(2, '0');
        var day = String(d.getDate()).padStart(2, '0');
        return d.getFullYear() + '-' + m + '-' + day;
    }

    function renderSlots(booked) {
        var select = $('time');
        var note = $('availabilityNote');
        var dateVal = $('date').value;
        var now = new Date();
        var isToday = dateVal === todayISO();

        select.innerHTML = '<option value="">-- Select a time --</option>';
        var count = 0;

        DEFAULT_SLOTS.forEach(function (slot) {
            if (booked.indexOf(slot) !== -1) return;
            if (isToday) {
                var h = Number(slot.split(':')[0]);
                if (h <= now.getHours()) return;
            }
            var opt = document.createElement('option');
            opt.value = slot;
            opt.textContent = formatTime(slot);
            select.appendChild(opt);
            count++;
        });

        if (count === 0) {
            select.innerHTML = '<option value="">No times available</option>';
            select.disabled = true;
            note.textContent = 'No slots left on this date. Please choose another day.';
            note.style.display = 'block';
        } else {
            select.disabled = false;
            note.textContent = count + ' time slot' + (count === 1 ? '' : 's') + ' available';
            note.style.display = 'block';
        }
        updateSidebar();
    }

    function loadSlots() {
        var dateVal = $('date').value;
        var select = $('time');
        if (!dateVal) {
            select.innerHTML = '<option value="">-- Select a date first --</option>';
            select.disabled = true;
            $('availabilityNote').style.display = 'none';
            updateSidebar();
            return;
        }
        if (!AVAILABILITY_URL) { renderSlots([]); return; }

        fetch(AVAILABILITY_URL + '?date=' + encodeURIComponent(dateVal))
            .then(function (r) { return r.json(); })
            .then(function (data) { renderSlots((data && data.booked) || []); })
            .catch(function () { renderSlots([]); });
    }

    /* ---------- Review fill ---------- */
    function fillReview() {
        var deposit = state.price * 0.5;
        var notes = $('notes').value.trim();

        setText('reviewService', state.name);
        setText('reviewDuration', state.duration);
        setText('reviewDateTime', formatDate($('date').value) + ' at ' + formatTime($('time').value));
        setText('reviewName', $('name').value.trim());
        setText('reviewPhone', $('phone').value.trim());
        setText('reviewEmail', $('email').value.trim());
        setText('reviewStylist', $('stylist').value);
        setText('reviewNotes', notes || 'None');
        setText('reviewTotal', money(state.price));
        setText('reviewDeposit', money(deposit));

        $('hiddenDeposit').value = deposit.toFixed(2);
    }

    /* ---------- Navigation ---------- */
    window.nextStep = function (from) {
        if (from === 1) {
            if (!$('selectedService').value) {
                showError(1, 'Please select a service to continue.');
                return;
            }
            showError(1, '');
            showStep(2);
        } else if (from === 2) {
            if (!$('date').value) { showError(2, 'Please choose a date.'); return; }
            if (!$('time').value) { showError(2, 'Please choose a time.'); return; }
            showError(2, '');
            showStep(3);
        } else if (from === 3) {
            var fields = ['name', 'phone', 'email'];
            for (var i = 0; i < fields.length; i++) {
                var el = $(fields[i]);
                if (!el.checkValidity()) {
                    el.reportValidity();
                    return;
                }
            }
            fillReview();
            showStep(4);
        }
    };

    window.prevStep = function (from) {
        if (from > 1) showStep(from - 1);
    };

    /* ---------- Init ---------- */
    document.addEventListener('DOMContentLoaded', function () {
        var dateInput = $('date');
        if (dateInput) {
            dateInput.min = todayISO();
            dateInput.addEventListener('change', loadSlots);
        }
        var timeSelect = $('time');
        if (timeSelect) timeSelect.addEventListener('change', updateSidebar);

        var grid = document.querySelector('.service-grid');
        if (grid) {
            grid.addEventListener('click', function (e) {
                var card = e.target.closest('.service-card');
                if (card) selectService(card);
            });
            grid.addEventListener('keydown', function (e) {
                if (e.key !== 'Enter' && e.key !== ' ') return;
                var card = e.target.closest('.service-card');
                if (card) { e.preventDefault(); selectService(card); }
            });
        }

        // Safety net: block submit if something is missing
        var form = $('bookingWizard');
        if (form) {
            form.addEventListener('submit', function (e) {
                if (!$('selectedService').value || !$('date').value || !$('time').value ||
                    !$('name').value.trim() || !$('phone').value.trim() || !$('email').value.trim()) {
                    e.preventDefault();
                    alert('Please complete all required steps before continuing.');
                    return;
                }
                fillReview();
            });
        }

        updateSidebar();
    });
})();