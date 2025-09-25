<?php
/**
 * Edit role + permissions
 * Expects: $role (array), $permissions (array of existing permissions keyed by module=>actions)
 */

$title = 'Edit Role - ' . (isset($role['name']) ? esc($role['name']) : 'Role');
ob_start();
?>
<div class="min-h-screen bg-gradient-to-br from-purple-50 to-lavender-50 py-8">
    <div class="container mx-auto px-4 max-w-4xl">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="<?= site_url('admin/roles') ?>" class="mr-4 p-2 text-purple-600 hover:text-purple-800 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Edit Role</h1>
                    <p class="text-gray-600 mt-1">Modify role details and permissions for: <span class="font-semibold text-purple-600"><?= esc($role['name'] ?? '') ?></span></p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-xl rounded-2xl border border-purple-100 overflow-hidden">
            <form method="post" action="<?= site_url('admin/roles/update/' . ($role['id'] ?? '')) ?>">
                <div class="p-8">
                    <!-- Role Details Section -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                            <div class="w-8 h-8 bg-gradient-to-br from-purple-100 to-indigo-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            Role Information
                        </h2>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Role Name *</label>
                                <input type="text" name="name" 
                                       value="<?= esc($role['name'] ?? '') ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200" 
                                       placeholder="Enter role name"
                                       required />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                                <textarea name="description" rows="3"
                                         class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 resize-none"
                                         placeholder="Describe the role's purpose"><?= esc($role['description'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Role Stats -->
                        <?php if (!empty($role['created_at']) || !empty($role['updated_at'])): ?>
                            <div class="mt-6 p-4 bg-gradient-to-r from-purple-50 to-indigo-50 rounded-xl border border-purple-100">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <?php if (!empty($role['created_at'])): ?>
                                        <div class="flex items-center text-gray-600">
                                            <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span><strong>Created:</strong> <?= esc(date('M d, Y g:i A', strtotime($role['created_at']))) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($role['updated_at'])): ?>
                                        <div class="flex items-center text-gray-600">
                                            <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            <span><strong>Updated:</strong> <?= esc(date('M d, Y g:i A', strtotime($role['updated_at']))) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Permissions Section -->
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                                <div class="w-8 h-8 bg-gradient-to-br from-purple-100 to-indigo-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                </div>
                                Permissions
                            </h2>
                            <div class="flex space-x-2">
                                <button type="button" onclick="selectAllPermissions()" 
                                        class="px-4 py-2 text-sm bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition-colors">
                                    Select All
                                </button>
                                <button type="button" onclick="clearAllPermissions()" 
                                        class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                    Clear All
                                </button>
                            </div>
                        </div>
                        
                        <?php
                        $modules = \Config\PermissionsModules::$modules;
                        $actions = ['view' => 'View','create' => 'Create','edit' => 'Edit','delete' => 'Delete','approve' => 'Approve'];
                        $existing = $permissions ?? [];
                        ?>
                        
                        <div class="border border-purple-200 rounded-xl overflow-hidden">
                            <table class="min-w-full">
                                <thead class="bg-gradient-to-r from-purple-50 to-indigo-50">
                                    <tr>
                                        <th class="px-6 py-4 text-left font-semibold text-purple-800 text-sm uppercase tracking-wider">Module</th>
                                        <?php foreach ($actions as $aKey => $aLabel): ?>
                                            <th class="px-4 py-4 text-center font-semibold text-purple-800 text-sm uppercase tracking-wider">
                                                <div class="flex flex-col items-center">
                                                    <span><?= esc($aLabel) ?></span>
                                                    <label class="mt-2 cursor-pointer">
                                                        <input type="checkbox" class="select-all-<?= $aKey ?> text-purple-600 rounded focus:ring-purple-500" 
                                                               onchange="toggleColumn('<?= $aKey ?>', this.checked)">
                                                        <span class="text-xs text-gray-500 ml-1">All</span>
                                                    </label>
                                                </div>
                                            </th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    <?php foreach ($modules as $m): $mKey = str_replace(' ', '_', strtolower($m)); ?>
                                        <tr class="hover:bg-purple-25 transition-colors duration-150">
                                            <td class="px-6 py-4 font-medium text-gray-900">
                                                <div class="flex items-center">
                                                    <div class="w-8 h-8 bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg flex items-center justify-center mr-3">
                                                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                        </svg>
                                                    </div>
                                                    <?= esc($m) ?>
                                                </div>
                                            </td>
                                            <?php foreach ($actions as $aKey => $aLabel): 
                                                $checked = isset($existing[$mKey]) && !empty($existing[$mKey][$aKey]);
                                            ?>
                                                <td class="px-4 py-4 text-center">
                                                    <div class="flex justify-center">
                                                        <label class="relative inline-flex items-center cursor-pointer">
                                                            <input type="checkbox" name="permissions[<?= esc($mKey) ?>][<?= esc($aKey) ?>]" 
                                                                   value="1" class="permission-<?= $aKey ?> permission-checkbox sr-only peer" <?= $checked ? 'checked' : '' ?>>
                                                            <div class="w-6 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-purple-300 rounded-md peer-checked:bg-gradient-to-r peer-checked:from-purple-600 peer-checked:to-indigo-600 transition-all duration-200 flex items-center justify-center">
                                                                <svg class="w-4 h-4 text-white opacity-0 peer-checked:opacity-100 transition-opacity duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                                </svg>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Permission Summary -->
                        <div class="mt-4 p-4 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl border border-blue-200">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-sm text-blue-800">
                                    <span id="selected-count">0</span> permissions selected across <span id="module-count">0</span> modules
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="bg-gradient-to-r from-gray-50 to-purple-50 px-8 py-6 flex items-center justify-between border-t border-purple-100">
                    <div class="text-sm text-gray-600">
                        <span class="font-medium">*</span> Required fields
                    </div>
                    <div class="flex items-center gap-4">
                        <a href="<?= site_url('admin/roles') ?>" 
                           class="px-6 py-3 text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 font-medium transition-all duration-150">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-8 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-purple-700 hover:to-indigo-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            Update Role
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleColumn(action, checked) {
    const checkboxes = document.querySelectorAll(`.permission-${action}`);
    checkboxes.forEach(checkbox => {
        checkbox.checked = checked;
    });
    updatePermissionCount();
}

function selectAllPermissions() {
    const checkboxes = document.querySelectorAll('.permission-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    updatePermissionCount();
}

function clearAllPermissions() {
    const checkboxes = document.querySelectorAll('.permission-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    updatePermissionCount();
}

function updatePermissionCount() {
    const checkedBoxes = document.querySelectorAll('.permission-checkbox:checked');
    const modules = new Set();
    checkedBoxes.forEach(checkbox => {
        const row = checkbox.closest('tr');
        if (row) {
            modules.add(row);
        }
    });
    
    document.getElementById('selected-count').textContent = checkedBoxes.length;
    document.getElementById('module-count').textContent = modules.size;
}

// Add interactive feedback
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.permission-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const row = this.closest('tr');
            if (row) {
                const rowCheckboxes = row.querySelectorAll('.permission-checkbox:checked');
                if (rowCheckboxes.length > 0) {
                    row.classList.add('bg-purple-25');
                } else {
                    row.classList.remove('bg-purple-25');
                }
            }
            updatePermissionCount();
        });
    });
    
    // Initial count
    updatePermissionCount();
    
    // Initial row highlighting
    checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
            const row = checkbox.closest('tr');
            if (row) {
                row.classList.add('bg-purple-25');
            }
        }
    });
});
</script>

<?php
$content = ob_get_clean();
echo $content;
?>