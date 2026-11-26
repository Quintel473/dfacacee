let currentStep = 1;
let selectedServices = []; // Array to hold multiple services
let bookingData = { 
    date: '', 
    time: '', 
    name: '', 
    email: '', 
    phone: '' 
};

document.addEventListener('DOMContentLoaded', () => {
    const today = new Date().toISOString().split('T')[0];
    const dateEl = document.getElementById('date');
    if (dateEl) dateEl.setAttribute('min', today);
});

// Toggle service selection
function toggleService(card, serviceName, price, duration) {
    const index = selectedServices.findIndex(s => s.name === serviceName);
    
    if (index > -1) {
        // Service already selected, remove it
        selectedServices.splice(index, 1);
        card.classList.remove('selected');
    } else {
        // Service not selected, add it
        selectedServices.push({ name: serviceName, price: parseFloat(price), duration: duration });
        card.classList.add('selected');
    }
    
    updateBookingSummary();
}

function updateBookingSummary() {
    let totalPrice = 0;
    let totalDuration = 0;
    let serviceNames = [];

    selectedServices.forEach(svc => {
        totalPrice += svc.price;
        serviceNames.push(svc.name);
        // Extract minutes from duration string (e.g., "60 mins" -> 60)
        const mins = parseInt(svc.duration) || 0;
        totalDuration += mins;
    });

    const deposit = totalPrice * 0.5;
    
    // Format duration back to a string (e.g., 90 mins -> 1 hr 30 mins)
    let durationText = `${totalDuration} mins`;
    if (totalDuration >= 60) {
        const hrs = Math.floor(totalDuration / 60);
        const remMins = totalDuration % 60;
        durationText = `${hrs} hr${hrs > 1 ? 's' : ''} ${remMins > 0 ? remMins + ' mins' : ''}`;
    }

    // Update Hidden Inputs
    document.getElementById('selectedService').value = serviceNames.join(', ');
    document.getElementById('selectedPrice').value = totalPrice.toFixed(2);
    document.getElementById('selectedDuration').value = durationText;
    document.getElementById('hiddenDeposit').value = deposit.toFixed(2);

    // Update Sidebar
    document.getElementById('sideService').textContent = serviceNames.length > 0 ? serviceNames.join(', ') : 'None selected';
    document.getElementById('sideDuration').textContent = totalDuration > 0 ? durationText : '-';
    document.getElementById('sidePrice').textContent = `$${totalPrice.toFixed(2)}`;
    document.getElementById('sideDeposit').textContent = `$${deposit.toFixed(2)}`;
    document.getElementById('sideBalance').textContent = `$${(totalPrice - deposit).toFixed(2)}`;
}

function updateProgressBar(step) {
    for (let i = 1; i <= 4; i++) {
        const indicator = document.getElementById(`step${i}-indicator`);
        if (indicator) indicator.classList.toggle('active', i <= step);
    }
}

function showStep(step) {
    document.querySelectorAll('.form-step').forEach(el => el.classList.remove('active'));
    const targetStep = document.getElementById(`step${step}`);
    if (targetStep) targetStep.classList.add('active');
    updateProgressBar(step);
    
    const container = document.querySelector('.booking-container');
    if (container) container.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function nextStep(current) {
    if (current === 1 && selectedServices.length === 0) { 
        alert("Please select at least one service."); 
        return; 
    }
    
    if (current === 2) {
        const date = document.getElementById('date').value;
        const time = document.getElementById('time').value;
        if (!date || !time) { 
            alert("Please select both a date and time."); 
            return; 
        }
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
        if (!name || !email || !phone) { 
            alert("Please fill in your name, email, and phone."); 
            return; 
        }
        bookingData.name = name; 
        bookingData.email = email; 
        bookingData.phone = phone;
    }
    
    currentStep++;
    showStep(currentStep);
}

function prevStep(current) {
    currentStep--;
    showStep(currentStep);
}

/* ═══════════════════════════════════════════════
   AVAILABILITY CHECK
   ═══════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    const dateInput = document.getElementById('date');
    const timeSelect = document.getElementById('time');
    const note = document.getElementById('availabilityNote');

    if (!dateInput || !timeSelect) return;

    const ALL_SLOTS = ['05:00', '06:00', '07:00', '15:00', '16:00', '17:00', '18:00', '19:00'];

    dateInput.addEventListener('change', () => {
        const date = dateInput.value;
        if (!date) return;

        timeSelect.innerHTML = '<option value="">Loading...</option>';
        timeSelect.disabled = true;

        fetch(`../actions/check_availability.php?date=${date}`)
            .then(res => res.json())
            .then(data => {
                const booked = data.booked || [];
                const blocked = data.blocked || [];
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