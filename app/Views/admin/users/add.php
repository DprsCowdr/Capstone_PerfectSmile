<?= view('templates/header') ?>

<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    <div class="flex-1 flex flex-col min-h-screen bg-white">
        <!-- Topbar -->
        <nav class="flex items-center justify-between bg-white shadow px-6 py-4 mb-6">
            <button id="sidebarToggleTop" class="block lg:hidden text-gray-600 mr-3 text-2xl focus:outline-none">
                <i class="fa fa-bars"></i>
            </button>
            <div class="flex items-center ml-auto">
                <span class="mr-4 hidden lg:inline text-gray-600 font-semibold"><?= $user['name'] ?? 'Admin' ?></span>
                <div class="relative">
                    <button class="focus:outline-none">
                        <img class="w-10 h-10 rounded-full border-2 border-gray-200" src="<?= base_url('img/undraw_profile.svg') ?>" alt="Profile">
                    </button>
                </div>
            </div>
        </nav>

        <main class="flex-1 px-6 pb-6">
            <div class="flex items-center mb-6">
                <a href="<?= base_url('admin/users') ?>" class="text-blue-600 hover:text-blue-800 mr-4">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
                <h1 class="text-2xl font-bold text-gray-800">Add New User</h1>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <!-- Add User Form -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <form action="<?= base_url('admin/users/store') ?>" method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Basic Information -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Basic Information</h3>
                            
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                                <input type="text" id="name" name="name" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       value="<?= old('name') ?>">
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                                <input type="email" id="email" name="email" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       value="<?= old('email') ?>">
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                                <input type="tel" id="phone" name="phone" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       value="<?= old('phone') ?>">
                            </div>

                            <div>
                                <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                                <select id="gender" name="gender" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?= old('gender') === 'Male' ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= old('gender') === 'Female' ? 'selected' : '' ?>>Female</option>
                                    <option value="Other" <?= old('gender') === 'Other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>

                            <div>
                                <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                                <input type="date" id="date_of_birth" name="date_of_birth" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       value="<?= old('date_of_birth') ?>" onchange="calculateAge()">
                            </div>

                            <div>
                                <label for="age" class="block text-sm font-medium text-gray-700 mb-1">Age</label>
                                <input type="number" id="age" name="age" readonly
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:outline-none"
                                       value="<?= old('age') ?>" placeholder="Auto-calculated">
                            </div>
                        </div>

                        <!-- Role & Access -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Role & Access</h3>
                            
                            <div>
                                <label for="user_type" class="block text-sm font-medium text-gray-700 mb-1">User Type *</label>
                                <select id="user_type" name="user_type" required 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select User Type</option>
                                    <option value="staff" <?= old('user_type') === 'staff' ? 'selected' : '' ?>>Staff</option>
                                    <option value="dentist" <?= old('user_type') === 'dentist' ? 'selected' : '' ?>>Dentist</option>
                                    <option value="admin" <?= old('user_type') === 'admin' ? 'selected' : '' ?>>Administrator</option>
                                </select>
                            </div>

                            <div>
                                <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                                <input type="text" id="position" name="position" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="e.g., Receptionist, Senior Dentist, etc."
                                       value="<?= old('position') ?>">
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                                <input type="password" id="password" name="password" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       minlength="6">
                                <p class="text-xs text-gray-500 mt-1">Minimum 6 characters</p>
                            </div>

                            <div>
                                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password *</label>
                                <input type="password" id="confirm_password" name="confirm_password" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Branch Assignment -->
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Branch Assignment</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach ($branches as $branch): ?>
                            <div class="flex items-center">
                                <input type="checkbox" id="branch_<?= $branch['id'] ?>" name="branches[]" 
                                       value="<?= $branch['id'] ?>" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                       <?= in_array($branch['id'], old('branches') ?? []) ? 'checked' : '' ?>>
                                <label for="branch_<?= $branch['id'] ?>" class="ml-2 text-sm text-gray-700">
                                    <?= esc($branch['name']) ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Select at least one branch where this user will work</p>
                    </div>

                    <!-- Additional Information -->
                    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="occupation" class="block text-sm font-medium text-gray-700 mb-1">Occupation</label>
                            <input type="text" id="occupation" name="occupation" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   value="<?= old('occupation') ?>">
                        </div>

                        <div>
                            <label for="nationality" class="block text-sm font-medium text-gray-700 mb-1">Nationality</label>
                            <input type="text" id="nationality" name="nationality" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   value="<?= old('nationality') ?>">
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="mt-8 flex justify-end space-x-4">
                        <a href="<?= base_url('admin/users') ?>" 
                           class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                            Create User
                        </button>
                    </div>
                </form>
            </div>
        </main>

        <footer class="bg-white py-4 mt-auto shadow-inner">
            <div class="text-center text-gray-500 text-sm">
                &copy; Perfect Smile <?= date('Y') ?>
            </div>
        </footer>
    </div>
</div>

<script>
// Password confirmation validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (password !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});

document.getElementById('password').addEventListener('input', function() {
    const confirmPassword = document.getElementById('confirm_password');
    if (confirmPassword.value) {
        confirmPassword.dispatchEvent(new Event('input'));
    }
});

// Age calculation function
function calculateAge() {
    const birthDate = document.getElementById('date_of_birth').value;
    if (birthDate) {
        const today = new Date();
        const birth = new Date(birthDate);
        let age = today.getFullYear() - birth.getFullYear();
        const monthDiff = today.getMonth() - birth.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
            age--;
        }
        
        document.getElementById('age').value = age >= 0 ? age : '';
    } else {
        document.getElementById('age').value = '';
    }
}

// Calculate age on page load if date is already selected
document.addEventListener('DOMContentLoaded', function() {
    calculateAge();
});
</script>

<?= view('templates/footer') ?> 