<?php 
$title = 'Settings - Perfect Smile';
$content = '
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-2">
        <i class="fas fa-cog mr-3 text-blue-600"></i>System Settings
    </h1>
    <p class="text-gray-600">Configure system preferences and settings</p>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <div class="text-center py-12">
        <i class="fas fa-cog fa-3x text-gray-300 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">System Settings</h3>
        <p class="text-gray-500">Settings configuration features will be implemented here.</p>
    </div>
</div>
';
?>

<?= view('templates/admin_layout', [
    'title' => $title,
    'content' => $content,
    'user' => $user
]) ?> 