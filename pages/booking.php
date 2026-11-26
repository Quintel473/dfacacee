<?php
require '../includes/db.php';
require '../includes/icons.php';
$pageTitle = "Book Appointment | D'FACEEE";
include '../includes/header.php';

// Fetch active services from DB
$stmt = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY price ASC");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="booking-layout">
    <!-- Main Form Area -->
    <div class="booking-main">
        <div class="booking-container">
            
            <!-- Progress Bar -->
            <div class="progress-bar">
                <div class="step active" id="step1-indicator">1</div>
                <div class="step" id="step2-indicator">2</div>
                <div class="step" id="step3-indicator">3</div>
                <div class="step" id="step4-indicator">4</div>
            </div>

            <form id="bookingWizard" action="checkout.php" method="POST">
                
                <!-- STEP 1: Select Service -->
                <div class="form-step active" id="step1">
                    <h2 class="step-title">Select Your Services</h2>
                    <p style="text-align: center; color: var(--text-gray); margin-bottom: 20px; font-size: 0.9rem;">You can select more than one service</p>
                    
                    <div class="service-grid">
                        <?php foreach($services as $svc): ?>
                            <!-- Changed to toggleService to allow multiple selections -->
                            <div class="service-card" onclick="toggleService(this, '<?php echo htmlspecialchars($svc['name']); ?>', <?php echo $svc['price']; ?>, '<?php echo htmlspecialchars($svc['duration']); ?>')">
                                <div class="svc-icon">
                                    <?php echo getServiceIcon($svc['icon']); ?>
                                </div>
                                <h4><?php echo htmlspecialchars($svc['name']); ?></h4>
                                <p class="svc-desc"><?php echo htmlspecialchars($svc['description']); ?></p>
                                <div class="svc-meta">
                                    <span class="svc-price">$<?php echo number_format($svc['price'], 2); ?></span>
                                    <span class="svc-duration">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: text-top; margin-right: 3px; pointer-events: none;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <?php echo htmlspecialchars($svc['duration']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <input type="hidden" name="service" id="selectedService" required>
                    <input type="hidden" name="total_price" id="selectedPrice" required>
                    <input type="hidden" name="service_duration" id="selectedDuration" required>
                    
                    <div class="btn-group">
                        <button type="button" class="btn-next" onclick="nextStep(1)">
                            Next: Date & Time
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: middle; margin-left: 5px; pointer-events: none;"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- STEP 2: Date & Time -->
                <div class="form-step" id="step2">
                    <h2 class="step-title">Choose Date & Time</h2>
                    <div class="form-group">
                        <label for="date">Preferred Date</label>
                        <input type="date" id="date" name="booking_date" required>
                    </div>
                    <div class="form-group">
                        <label for="time">Preferred Time</label>
                        <select id="time" name="booking_time" required disabled>
                            <option value="">-- Select a date first --</option>
                        </select>
                        <small id="availabilityNote" style="display:none; color: var(--gold); font-size: 0.85rem; margin-top: 5px;"></small>
                    </div>
                    
                    <div class="btn-group">
                        <button type="button" class="btn-prev" onclick="prevStep(2)">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: middle; margin-right: 5px; pointer-events: none;"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            Back
                        </button>
                        <button type="button" class="btn-next" onclick="nextStep(2)">
                            Next: Your Details
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: middle; margin-left: 5px; pointer-events: none;"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- STEP 3: Personal Details -->
                <div class="form-step" id="step3">
                    <h2 class="step-title">Your Details</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="full_name" required placeholder="Jane Doe">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" required placeholder="+1 234 567 890">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required placeholder="jane@example.com">
                    </div>
                    <div class="form-group">
                        <label for="notes">Special Requests / Notes (Optional)</label>
                        <textarea id="notes" name="special_notes" rows="3" placeholder="Allergies, skin concerns, event type, inspiration photos..."></textarea>
                    </div>
                    
                    <div class="btn-group">
                        <button type="button" class="btn-prev" onclick="prevStep(3)">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: middle; margin-right: 5px; pointer-events: none;"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            Back
                        </button>
                        <button type="button" class="btn-next" onclick="nextStep(3)">
                            Review Booking
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: middle; margin-left: 5px; pointer-events: none;"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- STEP 4: Review -->
                <div class="form-step" id="step4">
                    <h2 class="step-title">Review Your Booking</h2>
                    
                    <div class="review-badge">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: text-bottom; margin-right: 5px; pointer-events: none;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Please confirm your details below
                    </div>

                    <div class="policy-box">
                        <strong>Booking Policy:</strong>
                        <ul>
                            <li>A 50% non-refundable deposit is required to secure all appointments.</li>
                            <li>No plus ones are allowed.</li>
                            <li>Cancellations must be made 24 hours in advance.</li>
                            <li>Late arrivals will be subject to a late fee.</li>
                        </ul>
                    </div>

                    <input type="hidden" name="deposit_paid" id="hiddenDeposit" value="0">
                    
                    <div class="btn-group">
                        <button type="button" class="btn-prev" onclick="prevStep(4)">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: middle; margin-right: 5px; pointer-events: none;"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            Back
                        </button>
                        <button type="submit" class="btn-pay">
                            Proceed to Payment
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: middle; margin-left: 5px; pointer-events: none;"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sidebar Live Summary -->
    <aside class="booking-sidebar">
        <div class="summary-card">
            <h3>Booking Summary</h3>
            <div class="summary-item">
                <span class="summary-label">Service(s)</span>
                <span class="summary-value" id="sideService">Not selected</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Duration</span>
                <span class="summary-value" id="sideDuration">-</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Date</span>
                <span class="summary-value" id="sideDate">-</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Time</span>
                <span class="summary-value" id="sideTime">-</span>
            </div>
            
            <div class="summary-divider"></div>
            
            <div class="summary-item">
                <span class="summary-label">Total Price</span>
                <span class="summary-value" id="sidePrice">$0.00</span>
            </div>
            <div class="summary-item total-highlight">
                <span class="summary-label">Deposit (50%)</span>
                <span class="summary-value" id="sideDeposit">$0.00</span>
            </div>
            <div class="summary-item">
                <span class="summary-label" style="font-size: 0.8rem; color: #888;">Balance on day</span>
                <span class="summary-value" id="sideBalance" style="font-size: 0.9rem; color: #888;">$0.00</span>
            </div>
        </div>
    </aside>
</div>

<script src="../assets/js/script.js"></script>
<?php include '../includes/footer.php'; ?>