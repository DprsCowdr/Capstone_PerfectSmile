<?php
$title = 'Role Permission Management - Perfect Smile';
$content = '
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-2">
        <i class="fas fa-user-shield mr-3 text-blue-600"></i>Role Permission Management
    </h1>
    <p class="text-gray-600">Manage roles and permissions for your clinic users.</p>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <div class="text-center py-12">
        <i class="fas fa-user-shield fa-3x text-gray-300 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Role Permission</h3>
        <p class="text-gray-500">This area will contain role and permission management tools.</p>
    </div>
</div>
';

echo view('templates/admin_layout', [
    'title' => $title,
    'content' => $content,
    'user' => $user ?? session('user')
]);
