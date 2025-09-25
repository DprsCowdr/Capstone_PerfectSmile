<?php
/**
 * Assign users to role
 * Expects: $role (array), $assignedUsers (array of users assigned), $users (optional array of available users)
 */

$title = 'Assign Users - ' . (isset($role['name']) ? esc($role['name']) : 'Role');
ob_start();
?>
<div class="min-h-screen bg-gradient-to-br from-purple-50 to-lavender-50 py-8">
    <div class="container mx-auto px-4 max-w-6xl">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="<?= site_url('admin/roles') ?>" class="mr-4 p-2 text-purple-600 hover:text-purple-800 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Assign Users to Role</h1>
                    <p class="text-gray-600 mt-1">Managing users for: <span class="font-semibold text-purple-600"><?= esc($role['name'] ?? '') ?></span></p>
                </div>
            </div>
        </div>

        <!-- Search Section -->
        <div class="bg-white shadow-lg rounded-2xl border border-purple-100 p-6 mb-8">
            <div class="flex items-center mb-4">
                <div class="w-8 h-8 bg-gradient-to-br from-purple-100 to-indigo-100 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <label class="text-lg font-semibold text-gray-800">Search Users</label>
            </div>
            <input id="user-search" type="text" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200" 
                   placeholder="Search by name or email...">
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Available Users -->
            <div class="bg-white shadow-lg rounded-2xl border border-purple-100 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-50 to-indigo-50 p-6 border-b border-purple-100">
                    <h3 class="text-xl font-semibold text-purple-800 flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Available Users
                    </h3>
                </div>
                <div id="available-users" class="p-4 h-96 overflow-auto">
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $u): ?>
                            <div class="user-row mb-3 p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl border border-gray-200 hover:from-purple-50 hover:to-indigo-50 hover:border-purple-200 transition-all duration-200" 
                                 data-name="<?= strtolower(esc($u['name'] . ' ' . ($u['email'] ?? ''))) ?>">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full flex items-center justify-center mr-3">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-900"><?= esc($u['name']) ?></div>
                                            <div class="text-sm text-gray-500"><?= esc($u['email'] ?? 'No email') ?></div>
                                        </div>
                                    </div>
                                    <button class="px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white text-sm font-medium rounded-lg hover:from-purple-700 hover:to-indigo-700 transition-all duration-150 shadow-sm hover:shadow-md" 
                                            data-user-id="<?= $u['id'] ?>">
                                        <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Assign
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="flex flex-col items-center justify-center h-full text-center">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500 font-medium">No users available</p>
                            <p class="text-sm text-gray-400 mt-1">All users may already be assigned to this role</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Assigned Users -->
            <div class="bg-white shadow-lg rounded-2xl border border-purple-100 overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-50 to-teal-50 p-6 border-b border-emerald-100">
                    <h3 class="text-xl font-semibold text-emerald-800 flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Assigned Users
                        <span class="ml-2 px-2 py-1 bg-emerald-100 text-emerald-700 text-sm font-medium rounded-full" id="assigned-count">
                            <?= count($assignedUsers ?? []) ?>
                        </span>
                    </h3>
                </div>
                <div id="assigned-users" class="p-4 h-96 overflow-auto">
                    <?php if (!empty($assignedUsers)): ?>
                        <?php foreach ($assignedUsers as $au): ?>
                            <div class="assigned-row mb-3 p-4 bg-gradient-to-r from-emerald-50 to-teal-50 rounded-xl border border-emerald-200 hover:from-emerald-100 hover:to-teal-100 transition-all duration-200" 
                                 data-id="<?= $au['id'] ?>">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-full flex items-center justify-center mr-3">
                                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-900"><?= esc($au['name']) ?></div>
                                            <div class="text-sm text-gray-500"><?= esc($au['email'] ?? 'No email') ?></div>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <button class="remove-link px-3 py-2 text-red-600 bg-red-50 hover:bg-red-100 text-sm font-medium rounded-lg transition-all duration-150 border border-red-200 hover:border-red-300">
                                            <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Remove
                                        </button>
                                        <form method="post" action="<?= site_url('admin/roles/remove_user/' . ($role['id'] ?? '') . '/' . $au['id']) ?>" 
                                              onsubmit="return confirm('Remove user from role?');" class="hidden permanent-remove-form">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="text-red-600 bg-transparent border-0 p-0">Remove</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="flex flex-col items-center justify-center h-full text-center">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500 font-medium">No users assigned</p>
                            <p class="text-sm text-gray-400 mt-1">Start assigning users from the available list</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="mt-8 bg-white shadow-lg rounded-2xl border border-purple-100 overflow-hidden">
            <form method="post" action="<?= site_url('admin/roles/assign/' . ($role['id'] ?? '')) ?>" id="assign-form">
                <?= csrf_field() ?>
                <input type="hidden" name="user_ids" id="user-ids" value="">
                <div class="bg-gradient-to-r from-gray-50 to-purple-50 p-6 flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-purple-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Changes will be saved when you click "Save Assignments"</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <a href="<?= site_url('admin/roles') ?>" 
                           class="px-6 py-3 text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 font-medium transition-all duration-150">
                            Cancel
                        </a>
            <button type="submit" 
                class="px-8 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-purple-700 hover:to-indigo-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Save Assignments
                        </button>
                        
                        <!-- Production: no dev debug toggle shown -->
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Enhanced UI functionality
(function(){
    const search = document.getElementById('user-search');
    const available = document.getElementById('available-users');
    const assigned = document.getElementById('assigned-users');
    const userIdsInput = document.getElementById('user-ids');
    const assignedCount = document.getElementById('assigned-count');

    function refreshUserIds(){
        const ids = Array.from(assigned.querySelectorAll('.assigned-row')).map(r=>r.getAttribute('data-id'));
        userIdsInput.value = ids.join(',');
    }

    function updateAssignedCount(){
        const count = assigned.querySelectorAll('.assigned-row').length;
        assignedCount.textContent = count;
    }

    function createUserCard(userData, isAssigned = false) {
        const div = document.createElement('div');
        const cardClass = isAssigned 
            ? 'assigned-row mb-3 p-4 bg-gradient-to-r from-emerald-50 to-teal-50 rounded-xl border border-emerald-200 hover:from-emerald-100 hover:to-teal-100 transition-all duration-200'
            : 'user-row mb-3 p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl border border-gray-200 hover:from-purple-50 hover:to-indigo-50 hover:border-purple-200 transition-all duration-200';
        
        div.className = cardClass;
        div.setAttribute('data-' + (isAssigned ? 'id' : 'name'), userData.identifier);
        
        const iconColor = isAssigned ? 'emerald' : 'blue';
        const buttonClass = isAssigned 
            ? 'remove-link px-3 py-2 text-red-600 bg-red-50 hover:bg-red-100 text-sm font-medium rounded-lg transition-all duration-150 border border-red-200 hover:border-red-300'
            : 'px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white text-sm font-medium rounded-lg hover:from-purple-700 hover:to-indigo-700 transition-all duration-150 shadow-sm hover:shadow-md';
        
        div.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-br from-${iconColor}-100 to-${iconColor === 'emerald' ? 'teal' : 'indigo'}-100 rounded-full flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-${iconColor}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">${userData.name}</div>
                        <div class="text-sm text-gray-500">${userData.email || 'No email'}</div>
                    </div>
                </div>
                <button class="${buttonClass}" ${!isAssigned ? `data-user-id="${userData.id}"` : ''}>
                    <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${isAssigned ? 'M6 18L18 6M6 6l12 12' : 'M12 6v6m0 0v6m0-6h6m-6 0H6'}"></path>
                    </svg>
                    ${isAssigned ? 'Remove' : 'Assign'}
                </button>
            </div>
        `;
        
        return div;
    }

    // Handle assign button clicks
    available.addEventListener('click', function(e){
        if (e.target && (e.target.matches('button[data-user-id]') || e.target.closest('button[data-user-id]'))){
            const btn = e.target.matches('button[data-user-id]') ? e.target : e.target.closest('button[data-user-id]');
            const id = btn.getAttribute('data-user-id');
            const row = btn.closest('.user-row');
            
            // Extract user data
            const nameEl = row.querySelector('.font-semibold');
            const emailEl = row.querySelector('.text-sm.text-gray-500');
            const userData = {
                id: id,
                name: nameEl.textContent,
                email: emailEl.textContent === 'No email' ? '' : emailEl.textContent,
                identifier: id
            };
            
            // Create new assigned card
            const newCard = createUserCard(userData, true);
            assigned.appendChild(newCard);
            
            // Remove from available with animation
            row.style.transform = 'translateX(100%)';
            row.style.opacity = '0';
            setTimeout(() => row.remove(), 300);
            
            refreshUserIds();
            updateAssignedCount();
        }
    });

    // Handle remove button clicks
    assigned.addEventListener('click', function(e){
        if (e.target && (e.target.matches('.remove-link') || e.target.closest('.remove-link'))){
            e.preventDefault();
            const btn = e.target.matches('.remove-link') ? e.target : e.target.closest('.remove-link');
            const row = btn.closest('.assigned-row');
            
            // Remove with animation
            row.style.transform = 'translateX(-100%)';
            row.style.opacity = '0';
            setTimeout(() => {
                row.remove();
                refreshUserIds();
                updateAssignedCount();
            }, 300);
        }
    });

    // Enhanced search functionality
    search.addEventListener('input', function(){
        const q = this.value.trim().toLowerCase();
        Array.from(available.querySelectorAll('.user-row')).forEach(function(row){
            const name = row.getAttribute('data-name')||'';
            const shouldShow = name.indexOf(q) !== -1;
            row.style.display = shouldShow ? '' : 'none';
            
            // Add search highlight effect
            if (q && shouldShow) {
                row.classList.add('ring-2', 'ring-purple-200');
            } else {
                row.classList.remove('ring-2', 'ring-purple-200');
            }
        });
    });

    // Initial setup
    refreshUserIds();
    updateAssignedCount();
    // no debug button handler present in production view
})();
</script>

<script>
// Production: no client-side dev debug interception
</script>

<?php
$content = ob_get_clean();
echo $content;
?>