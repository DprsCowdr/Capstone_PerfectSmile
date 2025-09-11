<?php
/**
 * Assign users to role
 * Expects: $role (array), $assignedUsers (array of users assigned), $users (optional array of available users)
 */

$title = 'Assign Users - ' . (isset($role['name']) ? esc($role['name']) : 'Role');
ob_start();
?>
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-semibold mb-4">Assign Users to Role: <?= esc($role['name'] ?? '') ?></h1>

    <div class="mb-4">
        <label class="block text-sm font-medium mb-1">Search Users</label>
        <input id="user-search" type="text" class="input w-full" placeholder="Search by name or email...">
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <h3 class="font-medium mb-2">Available Users</h3>
            <div id="available-users" class="border rounded p-2 h-64 overflow-auto">
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $u): ?>
                        <div class="user-row flex items-center justify-between p-1" data-name="<?= strtolower(esc($u['name'] . ' ' . ($u['email'] ?? ''))) ?>">
                            <span><?= esc($u['name']) ?> <small class="text-gray-500">(<?= esc($u['email'] ?? '') ?>)</small></span>
                            <button class="btn btn-sm" data-user-id="<?= $u['id'] ?>">Assign</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-sm text-gray-500">No users available.</div>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <h3 class="font-medium mb-2">Assigned Users</h3>
            <div id="assigned-users" class="border rounded p-2 h-64 overflow-auto">
                <?php if (!empty($assignedUsers)): ?>
                    <?php foreach ($assignedUsers as $au): ?>
                        <div class="assigned-row flex items-center justify-between p-1" data-id="<?= $au['id'] ?>">
                            <span><?= esc($au['name']) ?> <small class="text-gray-500">(<?= esc($au['email'] ?? '') ?>)</small></span>
                            <form method="post" action="<?= site_url('admin/roles/remove_user/' . ($role['id'] ?? '') . '/' . $au['id']) ?>" onsubmit="return confirm('Remove user from role?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="text-red-600 bg-transparent border-0 p-0">Remove</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-sm text-gray-500">No users assigned.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <form method="post" action="<?= site_url('admin/roles/assign/' . ($role['id'] ?? '')) ?>" id="assign-form">
        <input type="hidden" name="user_ids" id="user-ids" value="">
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Save Assignments</button>
            <a href="<?= site_url('admin/roles') ?>" class="btn">Cancel</a>
        </div>
    </form>
</div>

<script>
// Simple client-side search and assign UI
(function(){
    const search = document.getElementById('user-search');
    const available = document.getElementById('available-users');
    const assigned = document.getElementById('assigned-users');
    const userIdsInput = document.getElementById('user-ids');

    function refreshUserIds(){
        const ids = Array.from(assigned.querySelectorAll('.assigned-row')).map(r=>r.getAttribute('data-id'));
        userIdsInput.value = ids.join(',');
    }

    available.addEventListener('click', function(e){
        if (e.target && e.target.matches('button[data-user-id]')){
            const btn = e.target;
            const id = btn.getAttribute('data-user-id');
            const row = btn.closest('.user-row');
            // move to assigned
            const clone = document.createElement('div');
            clone.className = 'assigned-row flex items-center justify-between p-1';
            clone.setAttribute('data-id', id);
            clone.innerHTML = row.innerHTML.replace(/Assign/, '<a href="#" class="remove-link text-red-600">Remove</a>');
            assigned.appendChild(clone);
            row.remove();
            refreshUserIds();
        }
    });

    assigned.addEventListener('click', function(e){
        if (e.target && e.target.matches('.remove-link')){
            e.preventDefault();
            const row = e.target.closest('.assigned-row');
            row.remove();
            refreshUserIds();
        }
    });

    search.addEventListener('input', function(){
        const q = this.value.trim().toLowerCase();
        Array.from(available.querySelectorAll('.user-row')).forEach(function(row){
            const name = row.getAttribute('data-name')||'';
            row.style.display = name.indexOf(q) === -1 ? 'none' : '';
        });
    });

    // initial refresh
    refreshUserIds();
})();
</script>

<?php
$content = ob_get_clean();
echo view('templates/admin_layout', [
    'title' => $title,
    'content' => $content,
    'user' => $user ?? session('user')
]);

