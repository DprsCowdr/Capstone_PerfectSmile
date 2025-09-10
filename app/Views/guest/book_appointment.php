<?= view('templates/header') ?>

<style>
.booking-container {
    min-height: 100vh;
    background: #F5ECFE;
    padding: 2rem 0;
}
.booking-form-container {
    background: #fff;
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    max-width: 800px;
    margin: 0 auto;
}
.booking-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 2rem;
    color: #2d2d2d;
    text-align: center;
    font-family: 'Nunito', sans-serif;
}
.booking-subtitle {
    text-align: center;
    color: #666;
    margin-bottom: 2rem;
}
.booking-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
.form-group {
    display: flex;
    flex-direction: column;
}
.form-group label {
    font-weight: 600;
    color: #2d2d2d;
    margin-bottom: 0.5rem;
    display: block;
}
.form-group input,
.form-group select,
.form-group textarea {
    border: 2px solid #c7aefc;
    border-radius: 8px;
    padding: 0.7rem 1rem;
    font-size: 1rem;
    background: #f9f6ff;
    color: #2d2d2d;
    outline: none;
    transition: border 0.2s;
    width: 100%;
    box-sizing: border-box;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #a47be5;
}
.form-group textarea {
    resize: vertical;
    min-height: 100px;
}
.booking-form button {
    background: #c7aefc;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 1rem 0;
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s;
    width: 100%;
    margin-top: 1rem;
}
.booking-form button:hover {
    background: #a47be5;
}
.login-link {
    text-align: center;
    margin-top: 1.5rem;
    color: #2d2d2d;
}
.login-link a {
    color: #6c63ff;
    text-decoration: none;
    font-weight: 600;
}
.login-link a:hover {
    text-decoration: underline;
}
.alert {
    padding: 0.75rem 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    font-weight: 500;
}
.alert-error {
    background: #fee;
    color: #c53030;
    border: 1px solid #feb2b2;
}
.alert-success {
    background: #f0fff4;
    color: #2f855a;
    border: 1px solid #9ae6b4;
}
.service-info {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}
.service-info h4 {
    color: #2d2d2d;
    margin-bottom: 0.5rem;
}
.service-info p {
    color: #666;
    margin-bottom: 0.5rem;
}
@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    .booking-form-container {
        padding: 1.5rem;
        margin: 1rem;
    }
}
</style>

<div class="booking-container">
    <div class="booking-form-container">
        <div class="booking-title">Book Your Appointment</div>
        <div class="booking-subtitle">Quick and easy appointment booking - no account required!</div>
        
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>
        
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>
        
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 1rem;">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form class="booking-form" method="POST" action="<?= base_url('guest/book-appointment') ?>">
            <!-- Personal Information -->
            <div class="service-info">
                <h4>Personal Information</h4>
                <p>Please provide your contact details for the appointment.</p>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="guest_name">Full Name *</label>
                    <input type="text" id="guest_name" name="guest_name" 
                           placeholder="Enter your full name" 
                           value="<?= old('guest_name') ?>" required>
                </div>
                <div class="form-group">
                    <label for="guest_email">Email Address *</label>
                    <input type="email" id="guest_email" name="guest_email" 
                           placeholder="Enter your email address" 
                           value="<?= old('guest_email') ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="guest_phone">Phone Number *</label>
                <input type="tel" id="guest_phone" name="guest_phone" 
                       placeholder="Enter your phone number" 
                       value="<?= old('guest_phone') ?>" required>
            </div>
            
            <!-- Appointment Details -->
            <div class="service-info">
                <h4>Appointment Details</h4>
                <p>Select your preferred service, branch, date, and time.</p>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="service_id">Service *</label>
                    <select id="service_id" name="service_id" required>
                        <option value="">Select a service</option>
                        <?php foreach ($services as $service): ?>
                            <option value="<?= $service['id'] ?>" <?= old('service_id') == $service['id'] ? 'selected' : '' ?>>
                                <?= $service['name'] ?> - $<?= number_format($service['price'], 2) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="branch_id">Branch Location *</label>
                    <?php $selBranch = session('selected_branch_id') ?? old('branch_id') ?? ''; ?>
                    <select id="branch_id" name="branch_id" required class="mobile-friendly-select">
                        <option value="">Select a branch</option>
                        <?php foreach ($branches as $branch): ?>
                            <?php $sel = ($selBranch == $branch['id']) ? 'selected' : ''; ?>
                            <option value="<?= $branch['id'] ?>" <?= $sel ?>>
                                <?= $branch['name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="appointment_date">Preferred Date *</label>
                    <input type="date" id="appointment_date" name="appointment_date" 
                           value="<?= old('appointment_date') ?>" 
                           min="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label for="appointment_time">Preferred Time *</label>
                    <select id="appointment_time" name="appointment_time" required>
                        <option value="">Select time</option>
                        <option value="09:00" <?= old('appointment_time') == '09:00' ? 'selected' : '' ?>>9:00 AM</option>
                        <option value="10:00" <?= old('appointment_time') == '10:00' ? 'selected' : '' ?>>10:00 AM</option>
                        <option value="11:00" <?= old('appointment_time') == '11:00' ? 'selected' : '' ?>>11:00 AM</option>
                        <option value="12:00" <?= old('appointment_time') == '12:00' ? 'selected' : '' ?>>12:00 PM</option>
                        <option value="13:00" <?= old('appointment_time') == '13:00' ? 'selected' : '' ?>>1:00 PM</option>
                        <option value="14:00" <?= old('appointment_time') == '14:00' ? 'selected' : '' ?>>2:00 PM</option>
                        <option value="15:00" <?= old('appointment_time') == '15:00' ? 'selected' : '' ?>>3:00 PM</option>
                        <option value="16:00" <?= old('appointment_time') == '16:00' ? 'selected' : '' ?>>4:00 PM</option>
                        <option value="17:00" <?= old('appointment_time') == '17:00' ? 'selected' : '' ?>>5:00 PM</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="notes">Additional Notes (Optional)</label>
                <textarea id="notes" name="notes" placeholder="Any special requests or additional information..."><?= old('notes') ?></textarea>
            </div>
            
            <button type="submit">Book Appointment</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="<?= base_url('login') ?>">Login here</a> to manage your appointments
        </div>
    </div>
</div>

<script>
// Set minimum date to today
const dateInput = document.getElementById('appointment_date');
dateInput.min = new Date().toISOString().split('T')[0];

// Dynamic available times
const branchInput = document.getElementById('branch_id');
const timeSelect = document.getElementById('appointment_time');

function formatTimeLabel(time) {
    const [h, m] = time.split(':');
    let hour = parseInt(h);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    if (hour > 12) hour -= 12;
    if (hour === 0) hour = 12;
    return `${hour}:${m} ${ampm}`;
}

function updateAvailableTimes() {
    const date = dateInput.value;
    const branchId = branchInput.value;
    if (!date || !branchId) return;
    fetch(`/guest/available-times?date=${date}&branch_id=${branchId}`)
        .then(res => res.json())
        .then(times => {
            timeSelect.innerHTML = '<option value="">Select time</option>';
            if (Array.isArray(times) && times.length > 0) {
                times.forEach(time => {
                    timeSelect.innerHTML += `<option value="${time}">${formatTimeLabel(time)}</option>`;
                });
            } else {
                timeSelect.innerHTML = '<option value="">No available times</option>';
            }
        });
}

dateInput.addEventListener('change', updateAvailableTimes);
branchInput.addEventListener('change', updateAvailableTimes);
</script>

<script>
// Improve mobile touch targets for branch select and auto-select single branch
document.addEventListener('DOMContentLoaded', function(){
    try{
        var sel = document.getElementById('branch_id');
        if(!sel) return;
        sel.classList.add('w-full');
        // If only one branch option (besides placeholder), auto-select it
        var opts = Array.from(sel.options).filter(o => o.value !== '');
        if(opts.length === 1 && !sel.value){ sel.value = opts[0].value; sel.dispatchEvent(new Event('change')); }
    }catch(e){ console.error('branch select init error', e); }
});
</script>

<?= view('templates/footer') ?> 