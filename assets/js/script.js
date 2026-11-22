let currentStep = 1;
let bookingData = { service: '', price: 0, duration: '', date: '', time: '', name: '', email: '', phone: '' };

document.addEventListener('DOMContentLoaded', () => {
    const today = new Date().toISOString().split('T')[0];
    const dateEl = document.getElementById('date');
    if (dateEl) dateEl.setAttribute('min', today);
});

function selectService(card, serviceName, price, duration) {
    document.querySelectorAll('.service-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    
    document.getElementById('selectedService').value = serviceName;
    document.getElementById('selectedPrice').value = price;
    document.getElementById('selectedDuration').value = duration;
    
    bookingData.service = serviceName;
    bookingData.price = parseFloat(price);
    bookingData.duration = duration;
    
    document.getElementById('sideService').textContent = serviceName;
    document.getElementById('sideDuration').textContent = duration;
    document.getElementById('sidePrice').textContent = `$${bookingData.price.toFixed(2)}`;
    
    const deposit = bookingData.price * 0.5;
    document.getElementById('sideDeposit').textContent = `$${deposit.toFixed(2)}`;
    document.getElementById('sideBalance').textContent = `$${(bookingData.price - deposit).toFixed(2)}`;
}

function updateProgressBar(step) {
    for (let i = 1; i <= 4; i++) {
        const indicator = document.getElementById(`step${i}-indicator`);
        if (indicator) indicator.classList.toggle('active', i <= step);
    }
}

function showStep(step) {
    document.querySelectorAll('.form-step').forEach(el => el.classList.remove('active'));
    document.getElementById(`step${step}`).classList.add('active');
    updateProgressBar(step);
    document.querySelector('.booking-container').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function nextStep(current) {
    if (current === 1 && !bookingData.service) { alert("Please select a service."); return; }
    
    if (current === 2) {
        const date = document.getElementById('date').value;
        const time = document.getElementById('time').value;
        if (!date || !time) { alert("Please select both a date and time."); return; }
        bookingData.date = date;
        bookingData.time = time;
        
        document.getElementById('sideDate').textContent = new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        const [h, m] = time.split(':');
        const hour = parseInt(h);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const displayHour = hour % 12 || 12;
        document.getElementById('sideTime').textContent = `${displayHour}:${m} ${ampm}`;
    }
    
    if (current === 3) {
        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        const phone = document.getElementById('phone').value;
        if (!name || !email || !phone) { alert("Please fill in your name, email, and phone."); return; }
        bookingData.name = name; 
        bookingData.email = email; 
        bookingData.phone = phone;
        
        document.getElementById('reviewService').textContent = bookingData.service;
        document.getElementById('reviewDate').textContent = `${bookingData.date} at ${bookingData.time}`;
        document.getElementById('reviewPrice').textContent = `$${bookingData.price.toFixed(2)}`;
        
        const deposit = bookingData.price * 0.5;
        document.getElementById('reviewDeposit').textContent = `$${deposit.toFixed(2)}`;
        document.getElementById('hiddenDeposit').value = deposit.toFixed(2);
    }
    
    currentStep++;
    showStep(currentStep);
}

function prevStep(current) {
    currentStep--;
    showStep(currentStep);
}

/* ═══════════════════════════════════════════════
   AVAILABILITY CHECK — UPDATED FOR YOUR HOURS
   ═══════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    const dateInput = document.getElementById('date');
    const timeSelect = document.getElementById('time');
    const note = document.getElementById('availabilityNote');

    if (!dateInput || !timeSelect) return;

    // YOUR WORKING HOURS: 5am-7am and 3pm-7pm
    const ALL_SLOTS = [
        '05:00', '06:00', '07:00', 
        '15:00', '16:00', '17:00', '18:00', '19:00'
    ];

    dateInput.addEventListener('change', () => {
        const date = dateInput.value;
        if (!date) return;

        timeSelect.innerHTML = '<option value="">Loading...</option>';
        timeSelect.disabled = true;

        // Fetches from actions folder
        fetch(`../actions/check_availability.php?date=${date}`)
            .then(res => res.json())
            .then(data => {
                const booked = data.booked || [];
                const blocked = data.blocked || []; // Future-proofing for Phase 2
                
                const unavailable = [...booked, ...blocked];
                const available = ALL_SLOTS.filter(s => !unavailable.includes(s));

                timeSelect.innerHTML = '';
                if (available.length === 0) {
                    timeSelect.innerHTML = '<option value="">Fully booked — try another date</option>';
                    note.textContent = "Sorry, all slots on this date are taken.";
                    note.style.display = 'block';
                    return;
                }

                timeSelect.innerHTML = '<option value="">-- Select a time --</option>';
                available.forEach(slot => {
                    const opt = document.createElement('option');
                    opt.value = slot;
                    const [h, m] = slot.split(':');
                    const hour = parseInt(h);
                    const ampm = hour >= 12 ? 'PM' : 'AM';
                    const displayHour = hour % 12 || 12;
                    opt.textContent = `${displayHour}:${m} ${ampm}`;
                    timeSelect.appendChild(opt);
                });
                timeSelect.disabled = false;

                if (unavailable.length > 0) {
                    note.textContent = `${unavailable.length} slot(s) already taken on this date.`;
                    note.style.display = 'block';
                } else {
                    note.style.display = 'none';
                }
            })
            .catch(err => {
                console.error('Availability check failed:', err);
                timeSelect.innerHTML = '<option value="">Error loading slots</option>';
            });
    });
});