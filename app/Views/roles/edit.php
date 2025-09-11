<?php
/**
 * Edit role + permissions
 * Expects: $role (array), $permissions (array of existing permissions keyed by module=>actions)
 */

$title = 'Edit Role - ' . (isset($role['name']) ? esc($role['name']) : 'Role');
ob_start();
?>
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-semibold mb-4">Edit Role: <?= esc($role['name'] ?? '') ?></h1>

    <form method="post" action="<?= site_url('admin/roles/update/' . ($role['id'] ?? '')) ?>">
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Role Name</label>
            <input type="text" name="name" class="input w-full" value="<?= esc($role['name'] ?? '') ?>" required />
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea name="description" class="input w-full" rows="3"><?= esc($role['description'] ?? '') ?></textarea>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-2">Permissions</label>
            <?php
            $modules = \Config\PermissionsModules::$modules;
            $actions = ['view' => 'View','create' => 'Create','edit' => 'Edit','delete' => 'Delete','approve' => 'Approve'];
            $existing = $permissions ?? [];
            ?>
            <div class="overflow-auto border rounded">
                <table class="min-w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 text-left">Module</th>
                            <?php foreach ($actions as $aKey => $aLabel): ?>
                                <th class="px-3 py-2 text-center"><?= esc($aLabel) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($modules as $m): $mKey = str_replace(' ', '_', strtolower($m)); ?>
                            <tr class="border-t">
                                <td class="px-3 py-2"><?= esc($m) ?></td>
                                <?php foreach ($actions as $aKey => $aLabel): 
                                    $checked = isset($existing[$mKey]) && !empty($existing[$mKey][$aKey]);
                                ?>
                                    <td class="px-3 py-2 text-center">
                                        <input type="checkbox" name="permissions[<?= esc($mKey) ?>][<?= esc($aKey) ?>]" value="1" <?= $checked ? 'checked' : '' ?> >
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="<?= site_url('admin/roles') ?>" class="btn">Cancel</a>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
echo view('templates/admin_layout', [
    'title' => $title,
    'content' => $content,
    'user' => $user ?? session('user')
]);

