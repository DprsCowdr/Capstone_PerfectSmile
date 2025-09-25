<?php
/**
 * Create role form
 * Expects: none
 */

$title = 'Create Role - Perfect Smile';
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
                    <h1 class="text-3xl font-bold text-gray-800">Create New Role</h1>
                    <p class="text-gray-600 mt-1">Define a new role with specific permissions</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-xl rounded-2xl border border-purple-100 overflow-hidden">
            <form method="post" action="<?= site_url('admin/roles/create') ?>">
                <?= csrf_field() ?>
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
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200" 
                                       placeholder="Enter role name"
                                       required />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                                <textarea name="description" rows="3"
                                         class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 resize-none"
                                         placeholder="Describe the role's purpose"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions Section -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                            <div class="w-8 h-8 bg-gradient-to-br from-purple-100 to-indigo-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            Permissions
                        </h2>
                        
                        <?php
                        $modules = \Config\PermissionsModules::$modules;
                        $actions = ['view' => 'View','create' => 'Create','edit' => 'Edit','delete' => 'Delete','approve' => 'Approve'];
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
                                            <?php foreach ($actions as $aKey => $aLabel): ?>
                                                <td class="px-4 py-4 text-center">
                                                    <div class="flex justify-center">
                                                        <label class="relative inline-flex items-center cursor-pointer">
                                                            <input type="checkbox" name="permissions[<?= esc($mKey) ?>][<?= esc($aKey) ?>]" 
                                                                   value="1" class="permission-<?= $aKey ?> sr-only peer">
                                                            <div class="w-6 h-6 bg-gray-200 rounded-md transition-colors peer-checked:bg-gradient-to-r peer-checked:from-purple-600 peer-checked:to-indigo-600"></div>
                                                        </label>
                                                    </div>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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
                            Create Role
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
}

// Add some interactive feedback
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const row = this.closest('tr');
            if (row) {
                if (this.checked) {
                    row.classList.add('bg-purple-25');
                } else {
                    // Check if any checkbox in this row is still checked
                    const rowCheckboxes = row.querySelectorAll('input[type="checkbox"]:checked');
                    if (rowCheckboxes.length === 0) {
                        row.classList.remove('bg-purple-25');
                    }
                }
            }
        });
    });
});
</script>

<?php
$content = ob_get_clean();
echo $content;
?>