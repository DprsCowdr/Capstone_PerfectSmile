<?php
/**
 * Roles index
 * Expected: $roles (array of roles with keys: id, name, description, user_count, created_at, updated_at)
 */

$title = 'Roles & Permissions - Perfect Smile';

// Render dynamic page content into $content so we can pass it into the project's admin layout
ob_start();
?>
<div>
    <main class="p-6 bg-gradient-to-br from-purple-50 to-lavender-50 min-h-screen">
        <div class="max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 mb-2">Roles & Permissions</h1>
                        <p class="text-gray-600">Manage user roles and their permissions</p>
                    </div>
                    <a href="<?= esc(site_url('admin/roles/create')) ?>" 
                       class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-medium rounded-xl hover:from-purple-700 hover:to-indigo-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Role
                    </a>
                </div>
            </div>

            <div class="bg-white shadow-xl rounded-2xl border border-purple-100 overflow-hidden">

                <!-- Flash Messages -->
                <?php if ($msg = session()->getFlashdata('success')): ?>
                    <div class="m-6 p-4 rounded-xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 text-emerald-800 flex items-center">
                        <div class="flex-shrink-0 w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <span class="font-medium"><?= esc($msg) ?></span>
                    </div>
                <?php elseif ($msg = session()->getFlashdata('error')): ?>
                    <div class="m-6 p-4 rounded-xl bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 text-red-800 flex items-center">
                        <div class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span class="font-medium"><?= esc($msg) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Roles Table -->
                <?php if (!empty($roles) && is_array($roles)): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-purple-100">
                            <thead class="bg-gradient-to-r from-purple-50 to-indigo-50">
                                <tr>
                                    <th class="px-6 py-4 text-left font-semibold text-purple-800 uppercase tracking-wider text-xs">Role Information</th>
                                    <th class="px-6 py-4 text-left font-semibold text-purple-800 uppercase tracking-wider text-xs">Description</th>
                                    <th class="px-6 py-4 text-center font-semibold text-purple-800 uppercase tracking-wider text-xs">Users</th>
                                    <th class="px-6 py-4 text-right font-semibold text-purple-800 uppercase tracking-wider text-xs">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-50">
                                <?php foreach ($roles as $role): ?>
                                    <tr class="hover:bg-gradient-to-r hover:from-purple-25 hover:to-indigo-25 transition-all duration-200">
                                        <!-- Role Info -->
                                        <td class="px-6 py-5">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-12 w-12 bg-gradient-to-br from-purple-100 to-indigo-100 rounded-xl flex items-center justify-center mr-4 shadow-sm">
                                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <div class="text-base font-semibold text-gray-900"><?= esc($role['name']) ?></div>
                                                    <div class="text-sm text-purple-500 font-medium">ID: <?= esc($role['id']) ?></div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Description -->
                                        <td class="px-6 py-5">
                                            <div class="text-sm text-gray-700 leading-relaxed max-w-xs">
                                                <?= esc($role['description'] ?? 'No description provided') ?>
                                            </div>
                                        </td>

                                        <!-- User Count -->
                                        <td class="px-6 py-5 text-center">
                                            <?php $count = intval($role['user_count'] ?? 0); ?>
                                            <div class="inline-flex items-center">
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold <?= $count > 0 ? 'bg-gradient-to-r from-emerald-100 to-teal-100 text-emerald-700 border border-emerald-200' : 'bg-gradient-to-r from-gray-100 to-slate-100 text-gray-600 border border-gray-200' ?>">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                    <?= $count ?> <?= $count === 1 ? 'user' : 'users' ?>
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Timeline -->
                                            <!-- Updated column removed; timeline available on show page -->

                                        <!-- Actions -->
                                        <td class="px-6 py-5 text-right">
                                            <div class="flex justify-end space-x-2">
                                                <a href="<?= esc(site_url('admin/roles/show/' . $role['id'])) ?>" 
                                                   class="inline-flex items-center px-3 py-2 text-sm font-medium text-purple-700 bg-white border border-purple-200 rounded-lg hover:bg-purple-50 hover:border-purple-300 transition-all duration-150">
                                                   <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                   </svg>
                                                   View
                                                </a>
                                                <a href="<?= esc(site_url('admin/roles/edit/' . $role['id'])) ?>" 
                                                   class="inline-flex items-center px-3 py-2 text-sm font-medium text-indigo-700 bg-white border border-indigo-200 rounded-lg hover:bg-indigo-50 hover:border-indigo-300 transition-all duration-150">
                                                   <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                   </svg>
                                                   Edit
                                                </a>
                                                    <!-- Assign button removed; use Role > Assign page from View/Edit links if needed -->
                                                <button onclick="confirmDelete('<?= esc($role['name']) ?>', '<?= esc(site_url('admin/roles/delete/' . $role['id'])) ?>')" 
                                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-700 bg-white border border-red-200 rounded-lg hover:bg-red-50 hover:border-red-300 transition-all duration-150">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="text-center py-16 px-6">
                        <div class="mx-auto w-32 h-32 bg-gradient-to-br from-purple-100 to-indigo-100 rounded-full flex items-center justify-center mb-6 shadow-lg">
                            <svg class="w-16 h-16 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-3">No roles found</h3>
                        <p class="text-gray-600 mb-8 max-w-md mx-auto">Get started by creating your first user role to manage permissions and access control.</p>
                        <a href="<?= esc(site_url('admin/roles/create')) ?>" 
                           class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-purple-700 hover:to-indigo-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Create Your First Role
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="deleteRoleTitle">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div class="p-6">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gradient-to-br from-red-100 to-pink-100 mb-4">
                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"></path>
                    </svg>
                </div>
                <h3 id="deleteRoleTitle" class="text-xl font-bold text-gray-900 mb-2">Delete Role</h3>
                <p class="text-gray-600 mb-6">
                    Are you sure you want to delete the role "<span id="roleNameToDelete" class="font-semibold text-gray-800"></span>"? 
                    This action cannot be undone and may affect users assigned to this role.
                </p>
            </div>
        </div>
        <div class="bg-gray-50 px-6 py-4 flex justify-center space-x-3">
            <button onclick="closeDeleteModal()" 
                    class="px-6 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium transition-colors duration-150">
                Cancel
            </button>
            <form id="deleteForm" method="post" action="" class="inline">
                <?= csrf_field() ?>
                <button type="submit" 
                        class="px-6 py-2 bg-gradient-to-r from-red-600 to-pink-600 text-white rounded-lg hover:from-red-700 hover:to-pink-700 font-medium transition-all duration-150 shadow-lg">
                    Delete Role
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDelete(roleName, deleteUrl) {
    document.getElementById('roleNameToDelete').textContent = roleName;
    document.getElementById('deleteForm').action = deleteUrl;
    document.getElementById('deleteModal').classList.remove('hidden');
}
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeDeleteModal();
});
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>

<?php

$content = ob_get_clean();
// Finish buffer and output fragment (no global admin sidebar/layout)
echo $content;

?>