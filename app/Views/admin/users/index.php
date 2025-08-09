<?= view('templates/header') ?>

<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    <div class="flex-1 flex flex-col min-h-screen bg-white">
        <!-- Topbar -->
        <nav class="flex items-center justify-between bg-white shadow px-6 py-4 mb-6">
            <button id="sidebarToggleTop" class="block lg:hidden text-gray-600 mr-3 text-2xl focus:outline-none">
                <i class="fa fa-bars"></i>
            </button>
            <div class="flex items-center ml-auto">
                <span class="mr-4 hidden lg:inline text-gray-600 font-semibold"><?= $user['name'] ?? 'Admin' ?></span>
                <div class="relative">
                    <button class="focus:outline-none">
                        <img class="w-10 h-10 rounded-full border-2 border-gray-200" src="<?= base_url('img/undraw_profile.svg') ?>" alt="Profile">
                    </button>
                </div>
            </div>
        </nav>

        <main class="flex-1 px-6 pb-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Users Management</h1>
                <div class="flex gap-3">
                    <a href="<?= base_url('admin/patients/activation') ?>" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-user-check mr-2"></i> Patient Activation
                    </a>
                    <a href="<?= base_url('admin/users/add') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-plus mr-2"></i> Add User
                    </a>
                </div>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <!-- Users Table -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">Staff & Dentists</h2>
                    <p class="text-sm text-gray-600">Manage all staff members, dentists, and administrators</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branches</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($users as $userData): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <i class="fas fa-user text-blue-600"></i>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900"><?= esc($userData['name']) ?></div>
                                                <div class="text-sm text-gray-500"><?= esc($userData['email']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                            <?= $userData['user_type'] === 'admin' ? 'bg-red-100 text-red-800' : 
                                               ($userData['user_type'] === 'dentist' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') ?>">
                                            <?= ucfirst($userData['user_type']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php 
                                        $userBranches = $branchAssignments[$userData['id']] ?? [];
                                        if (!empty($userBranches)): 
                                        ?>
                                            <div class="space-y-1">
                                                <?php foreach ($userBranches as $assignment): ?>
                                                    <div class="flex items-center gap-2">
                                                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded font-medium">
                                                            <?= esc($assignment['branch_name']) ?>
                                                        </span>
                                                        <span class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">
                                                            <?= esc($assignment['position'] ?: 'Staff') ?>
                                                        </span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-sm">No branches assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div><?= esc($userData['phone']) ?></div>
                                        <div class="text-gray-500"><?= esc($userData['gender']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                            <?= $userData['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                            <?= ucfirst($userData['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="<?= base_url('admin/users/edit/' . $userData['id']) ?>" 
                                               class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= base_url('admin/users/toggle-status/' . $userData['id']) ?>" 
                                               class="text-yellow-600 hover:text-yellow-900"
                                               onclick="return confirm('Are you sure you want to change this user\'s status?')">
                                                <i class="fas fa-toggle-on"></i>
                                            </a>
                                            <?php if ($userData['id'] != $user['id']): ?>
                                            <a href="<?= base_url('admin/users/delete/' . $userData['id']) ?>" 
                                               class="text-red-600 hover:text-red-900"
                                               onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (empty($users)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-users text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No Users Found</h3>
                    <p class="text-gray-500">Get started by adding your first staff member or dentist.</p>
                    <a href="<?= base_url('admin/users/add') ?>" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                        <i class="fas fa-plus mr-2"></i> Add First User
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </main>

        <footer class="bg-white py-4 mt-auto shadow-inner">
            <div class="text-center text-gray-500 text-sm">
                &copy; Perfect Smile <?= date('Y') ?>
            </div>
        </footer>
    </div>
</div>

<?= view('templates/footer') ?> 