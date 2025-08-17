<?= view('templates/header') ?>
<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <main class="flex-1 px-6 py-8">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <h1 class="text-3xl font-bold text-gray-800 mb-8">Add New Patient</h1>
                    
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded mb-6">
                            <?php 
                            $errors = session()->getFlashdata('error');
                            if (is_array($errors)) {
                                foreach ($errors as $field => $error) {
                                    if (is_array($error)) {
                                        foreach ($error as $err) {
                                            echo esc($err) . '<br>';
                                        }
                                    } else {
                                        echo esc($error) . '<br>';
                                    }
                                }
                            } else {
                                echo esc($errors);
                            }
                            ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('staff/patients/store') ?>" method="post" novalidate>
                        <?= csrf_field() ?>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                                <input type="text" name="name" value="<?= old('name') ?>" required
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                                <input type="email" name="email" value="<?= old('email') ?>" required
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                                <input type="text" name="phone" value="<?= old('phone') ?>" required
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth *</label>
                                <input type="date" name="date_of_birth" value="<?= old('date_of_birth') ?>" required
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Gender *</label>
                                <select name="gender" required
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors">
                                    <option value="">Select Gender</option>
                                    <option value="male" <?= old('gender') === 'male' ? 'selected' : '' ?>>👨 Male</option>
                                    <option value="female" <?= old('gender') === 'female' ? 'selected' : '' ?>>👩 Female</option>
                                    <option value="other" <?= old('gender') === 'other' ? 'selected' : '' ?>>⚧ Other</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Age</label>
                                <input type="number" name="age" value="<?= old('age') ?>" min="0" max="150"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Occupation</label>
                                <input type="text" name="occupation" value="<?= old('occupation') ?>"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nationality</label>
                                <input type="text" name="nationality" value="<?= old('nationality') ?>"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors">
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address *</label>
                            <textarea name="address" rows="3" required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-white text-gray-700 focus:border-purple-500 focus:outline-none transition-colors resize-none"><?= old('address') ?></textarea>
                        </div>
                        
                        <div class="mt-8 flex gap-4">
                            <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-lg transition-colors duration-200 shadow-lg hover:shadow-xl">
                                Add Patient
                            </button>
                            <a href="<?= base_url('staff/patients') ?>" 
                                class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-8 rounded-lg transition-colors duration-200 shadow-lg hover:shadow-xl">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
<?= view('templates/footer') ?> 