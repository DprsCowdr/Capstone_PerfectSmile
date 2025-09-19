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
    <main class="p-6 bg-gray-50 min-h-screen">
        <div class="max-w-6xl mx-auto">
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-4">

                <!-- Flash Messages -->
                <?php if ($msg = session()->getFlashdata('success')): ?>
                    <div class="mb-4 p-3 rounded bg-green-50 border border-green-200 text-green-700 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <?= esc($msg) ?>
                    </div>
                <?php elseif ($msg = session()->getFlashdata('error')): ?>
                    <div class="mb-4 p-3 rounded bg-red-50 border border-red-200 text-red-700 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <?= esc($msg) ?>
                    </div>
                <?php endif; ?>

                <!-- Roles Table -->
                <?php if (!empty($roles) && is_array($roles)): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Role Name</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-4 py-3 text-center font-medium text-gray-500 uppercase tracking-wider">Users</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Updated</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <?php foreach ($roles as $role): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <!-- Role Info -->
                                        <td class="px-4 py-3">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900"><?= esc($role['name']) ?></div>
                                                    <div class="text-xs text-gray-500">ID: <?= esc($role['id']) ?></div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Description -->
                                        <td class="px-4 py-3 text-gray-700">
                                            <?= esc($role['description'] ?? 'No description') ?>
                                        </td>

                                        <!-- User Count -->
                                        <td class="px-4 py-3 text-center">
                                            <?php $count = intval($role['user_count'] ?? 0); ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $count > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                                <?= $count ?> <?= $count === 1 ? 'user' : 'users' ?>
                                            </span>
                                        </td>

                                        <!-- Created -->
                                        <td class="px-4 py-3 text-gray-700">
                                            <?php if (!empty($role['created_at']) && strtotime($role['created_at'])): ?>
                                                <?= esc(date('M d, Y', strtotime($role['created_at']))) ?>
                                                <div class="text-xs text-gray-500"><?= esc(date('g:i A', strtotime($role['created_at']))) ?></div>
                                            <?php else: ?>
                                                <span class="text-gray-400">-</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Updated -->
                                        <td class="px-4 py-3 text-gray-700">
                                            <?php if (!empty($role['updated_at']) && strtotime($role['updated_at'])): ?>
                                                <?= esc(date('M d, Y', strtotime($role['updated_at']))) ?>
                                                <div class="text-xs text-gray-500"><?= esc(date('g:i A', strtotime($role['updated_at']))) ?></div>
                                            <?php else: ?>
                                                <span class="text-gray-400">-</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Actions -->
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex justify-end space-x-2">
                                                <a href="<?= esc(site_url('admin/roles/show/' . $role['id'])) ?>" 
                                                   class="px-3 py-1 text-sm bg-white border border-gray-200 rounded hover:bg-gray-50">
                                                   View
                                                </a>
                                                <a href="<?= esc(site_url('admin/roles/edit/' . $role['id'])) ?>" 
                                                   class="px-3 py-1 text-sm bg-white border border-gray-200 rounded hover:bg-gray-50">
                                                   Edit
                                                </a>
                                                <a href="<?= esc(site_url('admin/roles/assign/' . $role['id'])) ?>" 
                                                   class="px-3 py-1 text-sm bg-blue-50 border border-blue-200 text-blue-700 rounded hover:bg-blue-100">
                                                   Assign
                                                </a>
                                                <button onclick="confirmDelete('<?= esc($role['name']) ?>', '<?= esc(site_url('admin/roles/delete/' . $role['id'])) ?>')" 
                                                        class="px-3 py-1 text-sm bg-red-50 border border-red-200 text-red-700 rounded hover:bg-red-100">
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
                    <div class="text-center py-12">
                        <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No roles found</h3>
                        <p class="text-gray-600 mb-6">Get started by creating your first user role.</p>
                        <a href="<?= esc(site_url('admin/roles/create')) ?>" 
                           class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center" role="dialog" aria-modal="true" aria-labelledby="deleteRoleTitle">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 id="deleteRoleTitle" class="text-lg font-medium text-gray-900 mt-4">Delete Role</h3>
            <p class="text-sm text-gray-500 mt-2">
                Are you sure you want to delete the role "<span id="roleNameToDelete" class="font-medium"></span>"? 
                This action cannot be undone and may affect users assigned to this role.
            </p>
            <div class="flex justify-center space-x-3 mt-6">
                <button onclick="closeDeleteModal()" 
                        class="px-4 py-2 bg-white text-gray-500 border border-gray-300 rounded hover:bg-gray-50">
                    Cancel
                </button>
                <form id="deleteForm" method="post" action="" class="inline">
                    <?= csrf_field() ?>
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        Delete Role
                    </button>
                </form>
            </div>
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

// Ensure $user is provided to the admin layout; fallback to session user
$userData = isset($user) ? $user : (session('user') ?? []);

echo view('templates/admin_layout', [
    'title' => $title,
    'content' => $content,
    'user' => $userData,
    'additionalJS' => "<script>function confirmDelete(roleName, deleteUrl) { document.getElementById('roleNameToDelete').textContent = roleName; document.getElementById('deleteForm').action = deleteUrl; document.getElementById('deleteModal').classList.remove('hidden'); } function closeDeleteModal() { document.getElementById('deleteModal').classList.add('hidden'); } document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeDeleteModal(); }); document.getElementById('deleteModal').addEventListener('click', function(e) { if (e.target === this) closeDeleteModal(); });</script>"
]);

?>
