<?php
/**
 * Show role details
 * Expects: $role (array), $permissions (array), $assignedUsers (array), $logs (optional audit array)
 */

$title = 'Role: ' . (isset($role['name']) ? esc($role['name']) : 'Role');
ob_start();
?>
<div class="min-h-screen bg-gradient-to-br from-purple-50 to-lavender-50 py-8">
    <div class="container mx-auto px-4 max-w-6xl">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="<?= site_url('admin/roles') ?>" class="mr-4 p-2 text-purple-600 hover:text-purple-800 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800"><?= esc($role['name'] ?? 'Role Details') ?></h1>
                        <p class="text-gray-600 mt-1">Complete overview of role permissions and assignments</p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <a href="<?= site_url('admin/roles/edit/' . ($role['id'] ?? '')) ?>" 
                       class="inline-flex items-center px-4 py-2 bg-white border border-purple-300 text-purple-700 rounded-xl hover:bg-purple-50 transition-all duration-150 shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Role
                    </a>
                    <a href="<?= site_url('admin/roles/assign/' . ($role['id'] ?? '')) ?>" 
                       class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl hover:from-purple-700 hover:to-indigo-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        Assign Users
                    </a>
                </div>
            </div>
        </div>

        <!-- Role Overview Card -->
        <div class="bg-white shadow-xl rounded-2xl border border-purple-100 p-8 mb-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Role Info -->
                <div class="lg:col-span-2">
                    <div class="flex items-center mb-6">
                        <div class="w-16 h-16 bg-gradient-to-br from-purple-100 to-indigo-100 rounded-2xl flex items-center justify-center mr-4">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900"><?= esc($role['name'] ?? 'Unnamed Role') ?></h2>
                            <p class="text-gray-600 mt-1">Role ID: <?= esc($role['id'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2">Description</h3>
                            <p class="text-gray-800 leading-relaxed bg-gradient-to-r from-gray-50 to-purple-50 p-4 rounded-lg border border-gray-200">
                                <?= esc($role['description'] ?? 'No description provided for this role.') ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Role Stats -->
                <div class="space-y-6">
                    <div class="bg-gradient-to-br from-emerald-50 to-teal-50 p-6 rounded-xl border border-emerald-200">
                        <div class="flex items-center mb-2">
                            <svg class="w-6 h-6 text-emerald-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span class="text-emerald-800 font-semibold">Assigned Users</span>
                        </div>
                        <div class="text-3xl font-bold text-emerald-700"><?= count($assignedUsers ?? []) ?></div>
                        <p class="text-emerald-600 text-sm mt-1">Active assignments</p>
                    </div>

                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-6 rounded-xl border border-blue-200">
                        <div class="flex items-center mb-2">
                            <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            <span class="text-blue-800 font-semibold">Permissions</span>
                        </div>
                        <div class="text-3xl font-bold text-blue-700">
                            <?php 
                            $permissionCount = 0;
                            if (!empty($permissions)) {
                                foreach ($permissions as $module => $acts) {
                                    $permissionCount += count(array_filter($acts));
                                }
                            }
                            echo $permissionCount;
                            ?>
                        </div>
                        <p class="text-blue-600 text-sm mt-1">Total permissions</p>
                    </div>

                    <!-- Timeline -->
                    <div class="space-y-3">
                        <?php if (!empty($role['created_at'])): ?>
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span><strong>Created:</strong> <?= esc(date('M d, Y g:i A', strtotime($role['created_at']))) ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($role['updated_at'])): ?>
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                <span><strong>Updated:</strong> <?= esc(date('M d, Y g:i A', strtotime($role['updated_at']))) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Permissions Section -->
            <div class="bg-white shadow-xl rounded-2xl border border-purple-100 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-50 to-indigo-50 p-6 border-b border-purple-100">
                    <h3 class="text-xl font-semibold text-purple-800 flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        Role Permissions
                    </h3>
                </div>
                <div class="p-6">
                    <?php if (!empty($permissions)): ?>
                        <div class="space-y-4">
                            <?php foreach ($permissions as $module => $acts): ?>
                                <?php $activePermissions = array_keys(array_filter($acts)); ?>
                                <?php if (!empty($activePermissions)): ?>
                                    <div class="border border-gray-200 rounded-xl p-4 hover:border-purple-200 transition-colors duration-200">
                                        <div class="flex items-center justify-between mb-3">
                                            <h4 class="font-semibold text-gray-900 flex items-center">
                                                <div class="w-8 h-8 bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg flex items-center justify-center mr-3">
                                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                    </svg>
                                                </div>
                                                <?= esc(ucwords(str_replace('_', ' ', $module))) ?>
                                            </h4>
                                            <span class="px-2 py-1 bg-purple-100 text-purple-700 text-xs font-medium rounded-full">
                                                <?= count($activePermissions) ?> permissions
                                            </span>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <?php foreach ($activePermissions as $permission): ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-medium bg-gradient-to-r from-emerald-100 to-teal-100 text-emerald-700 border border-emerald-200">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    <?= esc(ucfirst($permission)) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500 font-medium">No permissions assigned</p>
                            <p class="text-sm text-gray-400 mt-1">This role has no specific permissions</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Assigned Users Section -->
            <div class="bg-white shadow-xl rounded-2xl border border-purple-100 overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-50 to-teal-50 p-6 border-b border-emerald-100">
                    <h3 class="text-xl font-semibold text-emerald-800 flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Assigned Users
                    </h3>
                </div>
                <div class="p-6">
                    <?php if (!empty($assignedUsers)): ?>
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            <?php foreach ($assignedUsers as $u): ?>
                                <div class="flex items-center p-4 bg-gradient-to-r from-emerald-50 to-teal-50 rounded-xl border border-emerald-200 hover:from-emerald-100 hover:to-teal-100 transition-all duration-200">
                                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-full flex items-center justify-center mr-4">
                                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-grow">
                                        <div class="font-semibold text-gray-900"><?= esc($u['name']) ?></div>
                                        <div class="text-sm text-gray-500"><?= esc($u['email'] ?? 'No email provided') ?></div>
                                    </div>
                                    <div class="flex items-center text-emerald-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
                                        </svg>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500 font-medium">No users assigned</p>
                            <p class="text-sm text-gray-400 mt-1">No users are currently assigned to this role</p>
                            <a href="<?= site_url('admin/roles/assign/' . ($role['id'] ?? '')) ?>" 
                               class="inline-flex items-center mt-4 px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 text-white text-sm font-medium rounded-lg hover:from-emerald-700 hover:to-teal-700 transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Assign Users
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Audit History Section -->
        <?php if (!empty($logs) || true): // Show section even if empty ?>
            <div class="mt-8 bg-white shadow-xl rounded-2xl border border-purple-100 overflow-hidden">
                <div class="bg-gradient-to-r from-orange-50 to-amber-50 p-6 border-b border-orange-100">
                    <h3 class="text-xl font-semibold text-orange-800 flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Audit History
                    </h3>
                </div>
                <div class="p-6">
                    <?php if (!empty($logs)): ?>
                        <div class="space-y-4 max-h-64 overflow-y-auto">
                            <?php foreach ($logs as $log): ?>
                                <div class="flex items-start p-4 bg-gradient-to-r from-orange-50 to-amber-50 rounded-xl border border-orange-200">
                                    <div class="w-8 h-8 bg-gradient-to-br from-orange-100 to-amber-100 rounded-full flex items-center justify-center mr-4 mt-0.5">
                                        <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-grow">
                                        <div class="text-sm text-gray-900">
                                            <span class="font-semibold"><?= esc($log['actor'] ?? 'Unknown user') ?></span>
                                            <span class="text-gray-600">changed <?= esc($log['changes'] ?? 'permissions') ?></span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            <?= esc($log['created_at'] ?? 'Unknown time') ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500 font-medium">No audit history available</p>
                            <p class="text-sm text-gray-400 mt-1">Role changes and modifications will appear here</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
echo $content;
?>