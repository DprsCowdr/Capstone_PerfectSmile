<?php
/**
 * Show role details
 * Expects: $role (array), $permissions (array), $assignedUsers (array), $logs (optional audit array)
 */

$title = 'Role: ' . (isset($role['name']) ? esc($role['name']) : 'Role');
ob_start();
?>
<div class="container mx-auto p-4">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Role: <?= esc($role['name'] ?? '') ?></h1>
        <div>
            <a href="<?= site_url('admin/roles/edit/' . ($role['id'] ?? '')) ?>" class="btn mr-2">Edit</a>
            <a href="<?= site_url('admin/roles/assign/' . ($role['id'] ?? '')) ?>" class="btn">Assign Users</a>
        </div>
    </div>

    <div class="mb-6 bg-white rounded shadow p-4">
        <p class="text-sm text-gray-700 mb-2"><strong>Description:</strong> <?= esc($role['description'] ?? '—') ?></p>
        <p class="text-sm text-gray-700"><strong>Created:</strong> <?= esc($role['created_at'] ?? '') ?> &nbsp; <strong>Updated:</strong> <?= esc($role['updated_at'] ?? '') ?></p>
    </div>

    <div class="mb-6">
        <h2 class="text-lg font-medium mb-2">Permissions</h2>
        <div class="border rounded p-2 bg-white">
            <?php if (!empty($permissions)): ?>
                <ul>
                <?php foreach ($permissions as $module => $acts): ?>
                    <li><strong><?= esc($module) ?>:</strong> <?= esc(implode(', ', array_keys(array_filter($acts)))) ?></li>
                <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="text-sm text-gray-500">No permissions assigned.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mb-6">
        <h2 class="text-lg font-medium mb-2">Assigned Users</h2>
        <div class="border rounded p-2 bg-white">
            <?php if (!empty($assignedUsers)): ?>
                <ul>
                    <?php foreach ($assignedUsers as $u): ?>
                        <li><?= esc($u['name']) ?> <small class="text-gray-500">(<?= esc($u['email'] ?? '') ?>)</small></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="text-sm text-gray-500">No users assigned.</div>
            <?php endif; ?>
        </div>
    </div>

    <div>
        <h2 class="text-lg font-medium mb-2">Audit / History</h2>
        <div class="border rounded p-2 bg-white">
            <?php if (!empty($logs)): ?>
                <ul>
                    <?php foreach ($logs as $log): ?>
                        <li class="text-sm mb-1"><?= esc($log['actor'] ?? 'Unknown') ?> changed <?= esc($log['changes'] ?? 'permissions') ?> on <?= esc($log['created_at'] ?? '') ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="text-sm text-gray-500">No audit history available.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
echo view('templates/admin_layout', [
    'title' => $title,
    'content' => $content,
    'user' => $user ?? session('user')
]);

