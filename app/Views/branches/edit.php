<?php /** @var array $branch */ ?>
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-violet-50 to-emerald-50 p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-violet-500 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-purple-600 via-violet-600 to-emerald-600 bg-clip-text text-transparent">
                        Edit Branch
                    </h1>
                    <p class="text-slate-600">Update branch information and details</p>
                </div>
            </div>
            
            <!-- Branch Info Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-100 to-green-100 text-emerald-800 rounded-full border border-emerald-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span class="font-medium"><?= esc($branch['name'] ?? 'Branch') ?></span>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100">
            <div class="p-8">
                <form method="post" action="<?= site_url('admin/branches/update/'.$branch['id']) ?>" class="space-y-8">
                    <?= csrf_field() ?>

                    <!-- Validation Errors -->
                    <?php if (session()->getFlashdata('errors')): ?>
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                            <div class="flex items-center mb-2">
                                <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <h3 class="text-sm font-semibold text-red-800">Please correct the following errors:</h3>
                            </div>
                            <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Basic Information Section -->
                    <div class="space-y-6">
                        <div class="border-b border-slate-200 pb-2">
                            <h3 class="text-lg font-semibold text-slate-800">Basic Information</h3>
                            <p class="text-sm text-slate-600">Essential details about the branch</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Branch Name -->
                            <div class="md:col-span-2 space-y-2">
                                <label for="name" class="block text-sm font-semibold text-slate-700">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        Branch Name *
                                    </div>
                                </label>
                                <input 
                                    id="name" 
                                    name="name" 
                                    type="text"
                                    value="<?= esc(old('name', $branch['name'] ?? '')) ?>"
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                    placeholder="Enter branch name"
                                    required
                                >
                            </div>

                            <!-- Contact Number -->
                            <div class="space-y-2">
                                <label for="contact_number" class="block text-sm font-semibold text-slate-700">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        Contact Number
                                    </div>
                                </label>
                                <input 
                                    id="contact_number" 
                                    name="contact_number" 
                                    type="tel"
                                    value="<?= esc(old('contact_number', $branch['contact_number'] ?? '')) ?>"
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                    placeholder="+1 (555) 123-4567"
                                >
                            </div>

                            <!-- Email -->
                            <div class="space-y-2">
                                <label for="email" class="block text-sm font-semibold text-slate-700">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                        </svg>
                                        Email Address
                                    </div>
                                </label>
                                <input 
                                    id="email" 
                                    name="email" 
                                    type="email"
                                    value="<?= esc(old('email', $branch['email'] ?? '')) ?>"
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                    placeholder="branch@dentalclinic.com"
                                >
                            </div>

                            <!-- Address -->
                            <div class="md:col-span-2 space-y-2">
                                <label for="address" class="block text-sm font-semibold text-slate-700">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        Address *
                                    </div>
                                </label>
                                <textarea 
                                    id="address" 
                                    name="address" 
                                    rows="3"
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 resize-none"
                                    placeholder="Enter complete branch address"
                                    required
                                ><?= esc(old('address', $branch['address'] ?? '')) ?></textarea>
                            </div>

                            <!-- Status -->
                            <div class="space-y-2">
                                <label for="status" class="block text-sm font-semibold text-slate-700">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Status
                                    </div>
                                </label>
                                <select 
                                    id="status" 
                                    name="status" 
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                >
                                    <option value="active" <?= old('status', $branch['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= old('status', $branch['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Operating Hours Section -->
                    <div class="space-y-6">
                        <div class="border-b border-slate-200 pb-2">
                            <h3 class="text-lg font-semibold text-slate-800">Operating Hours</h3>
                            <p class="text-sm text-slate-600">Update business hours for each day of the week</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php 
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            foreach ($days as $day): 
                                $dayLower = strtolower($day);
                                $isEnabled = old($dayLower.'_enabled', $branch['operating_hours'][$dayLower]['enabled'] ?? '1');
                                $openTime = old($dayLower.'_open', $branch['operating_hours'][$dayLower]['open'] ?? '09:00');
                                $closeTime = old($dayLower.'_close', $branch['operating_hours'][$dayLower]['close'] ?? '17:00');
                            ?>
                            <div class="bg-gradient-to-r from-slate-50 to-purple-50 rounded-xl p-4 border border-slate-200">
                                <div class="flex items-center justify-between mb-3">
                                    <label class="text-sm font-medium text-slate-700"><?= $day ?></label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="<?= $dayLower ?>_enabled" value="1" 
                                               <?= $isEnabled ? 'checked' : '' ?>
                                               class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500">
                                        <span class="ml-2 text-xs text-slate-600">Open</span>
                                    </label>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs text-slate-600 mb-1">Open</label>
                                        <input type="time" name="<?= $dayLower ?>_open" 
                                               value="<?= $openTime ?>"
                                               class="w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-purple-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-600 mb-1">Close</label>
                                        <input type="time" name="<?= $dayLower ?>_close" 
                                               value="<?= $closeTime ?>"
                                               class="w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-purple-500">
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Metadata Section -->
                    <?php if (isset($branch['created_at']) || isset($branch['updated_at'])): ?>
                    <div class="bg-gradient-to-r from-slate-50 to-purple-50 rounded-xl p-4 border border-slate-200">
                        <h4 class="text-sm font-semibold text-slate-700 mb-2">Branch Information</h4>
                        <div class="grid grid-cols-2 gap-4 text-xs text-slate-600">
                            <?php if (isset($branch['created_at'])): ?>
                            <div>
                                <span class="font-medium">Created:</span> <?= date('M d, Y \a\t g:i A', strtotime($branch['created_at'])) ?>
                            </div>
                            <?php endif; ?>
                            <?php if (isset($branch['updated_at'])): ?>
                            <div>
                                <span class="font-medium">Last Updated:</span> <?= date('M d, Y \a\t g:i A', strtotime($branch['updated_at'])) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Form Actions -->
                    <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-slate-200">
                        <button 
                            type="submit"
                            class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-purple-500 to-violet-500 hover:from-purple-600 hover:to-violet-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Update Branch
                        </button>
                        <a 
                            href="<?= site_url('admin/branches/'.$branch['id']) ?>"
                            class="inline-flex items-center justify-center px-6 py-3 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-semibold rounded-xl transition-colors duration-200"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            View Branch
                        </a>
                        <a 
                            href="<?= site_url('admin/branches') ?>"
                            class="inline-flex items-center justify-center px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition-colors duration-200"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Help Text -->
        <div class="mt-6 text-center">
            <p class="text-sm text-slate-500">
                Changes will be saved immediately. Make sure to verify operating hours are correct for your branch location.
            </p>
        </div>
    </div>
</div>