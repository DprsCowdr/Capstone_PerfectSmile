<?= view('templates/header') ?>

<style>
.register-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #F5ECFE;
    padding: 2rem;
}
.register-form-container {
    background: #fff;
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 500px;
}
.register-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 2rem;
    color: #2d2d2d;
    text-align: center;
    font-family: 'Nunito', sans-serif;
}
.register-form {
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
.form-group select {
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
.form-group select:focus {
    border-color: #a47be5;
}
.register-form button {
    background: #c7aefc;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 0.8rem 0;
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s;
    width: 100%;
    margin-top: 1rem;
}
.register-form button:hover {
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
.error-message {
    color: #c53030;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}
@media (max-width: 600px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    .register-form-container {
        padding: 1.5rem;
    }
}
</style>

<div class="register-container">
    <div class="register-form-container">
        <div class="register-title">Create Account</div>
        
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error">
                <?= session()->getFlashdata('error') ?>
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
        
        <form class="register-form" method="POST" action="<?= base_url('auth/registerUser') ?>">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Enter your full name" 
                           value="<?= old('name') ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" 
                           value="<?= old('email') ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" 
                           value="<?= old('phone') ?>">
                </div>
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender">
                        <option value="">Select Gender</option>
                        <option value="male" <?= old('gender') == 'male' ? 'selected' : '' ?>>Male</option>
                        <option value="female" <?= old('gender') == 'female' ? 'selected' : '' ?>>Female</option>
                        <option value="other" <?= old('gender') == 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="date_of_birth">Date of Birth</label>
                <input type="date" id="date_of_birth" name="date_of_birth" value="<?= old('date_of_birth') ?>">
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" placeholder="Enter your address" 
                       value="<?= old('address') ?>">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" 
                           placeholder="Confirm your password" required>
                </div>
            </div>
            
            <button type="submit">Create Account</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="<?= base_url('auth') ?>">Login here</a>
        </div>
    </div>
</div>

<script>
// Simple password confirmation validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (password !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?= view('templates/footer') ?> 