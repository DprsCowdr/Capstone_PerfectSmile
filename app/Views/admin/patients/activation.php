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
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
                <h1 class="font-bold text-2xl md:text-3xl text-black tracking-tight">Patient Account Activation</h1>
                <a href="<?= base_url('admin/patients') ?>" class="bg-gray-500 hover:bg-gray-600 text-white font-bold text-base rounded-xl shadow px-7 py-2.5 transition">← Back to Patients</a>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="flex items-center gap-2 bg-green-100 text-green-800 rounded-lg px-4 py-3 mb-4 text-sm font-semibold">
                    <i class="fas fa-check-circle"></i>
                    <span><?= session()->getFlashdata('success') ?></span>
                    <button type="button" class="ml-auto text-green-700 hover:text-green-900 focus:outline-none" onclick="this.parentElement.remove()">&times;</button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="flex items-center gap-2 bg-red-100 text-red-800 rounded-lg px-4 py-3 mb-4 text-sm font-semibold">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>
                    <?php 
                    $errors = session()->getFlashdata('error');
                    if (is_array($errors)) {
                        foreach ($errors as $field => $error) {
                            if (is_array($error)) {
                                foreach ($error as $err) {
                                    echo esc($err) . '<br>';
                                }
                            } else {
                                echo esc($error) . '<br>';
                            }
                        }
                    } else {
                        echo esc($errors);
                    }
                    ?>
                    </span>
                    <button type="button" class="ml-auto text-red-700 hover:text-red-900 focus:outline-none" onclick="this.parentElement.remove()">&times;</button>
                </div>
            <?php endif; ?>

            <!-- Patient Activation Table -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Patient Account Management</h3>
                    <p class="text-sm text-gray-600 mt-1">Activate or deactivate patient accounts. Active patients can log in to the system.</p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-50">
                            <tr class="text-gray-700 font-semibold text-sm">
                                <th class="px-6 py-4 text-left">Patient Info</th>
                                <th class="px-4 py-4 text-left">Contact</th>
                                <th class="px-4 py-4 text-left">Status</th>
                                <th class="px-4 py-4 text-left">Last Updated</th>
                                <th class="px-6 py-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (!empty($patients)): ?>
                                <?php foreach ($patients as $patient): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center font-bold text-indigo-600">
                                                <?= strtoupper(substr($patient['name'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="font-semibold text-gray-900"><?= esc($patient['name']) ?></div>
                                                <div class="text-sm text-gray-500">ID: <?= esc($patient['id']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="text-sm">
                                            <div class="font-medium text-gray-900"><?= esc($patient['email']) ?></div>
                                            <div class="text-gray-500"><?= esc($patient['phone']) ?></div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <?php 
                                        $status = $patient['status'] ?? 'inactive';
                                        $statusClass = $status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                        $statusIcon = $status === 'active' ? 'fa-check-circle' : 'fa-times-circle';
                                        $statusText = $status === 'active' ? 'Active' : 'Inactive';
                                        ?>
                                        <span class="inline-flex items-center gap-1 font-semibold rounded-full px-3 py-1 text-xs <?= $statusClass ?>">
                                            <i class="fas <?= $statusIcon ?>"></i>
                                            <?= $statusText ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-500">
                                        <?= date('M j, Y', strtotime($patient['updated_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php if ($patient['status'] === 'active'): ?>
                                            <form method="post" action="<?= base_url('admin/patients/deactivate/' . $patient['id']) ?>" class="inline" 
                                                  onsubmit="return confirm('Are you sure you want to deactivate this patient account? They will not be able to log in.')">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg text-sm transition-colors" title="Deactivate Account">
                                                    <i class="fas fa-user-times mr-1"></i>
                                                    Deactivate
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="post" action="<?= base_url('admin/patients/activate/' . $patient['id']) ?>" class="inline" 
                                                  onsubmit="return confirm('Are you sure you want to activate this patient account? A temporary password will be generated.')">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg text-sm transition-colors" title="Activate Account">
                                                    <i class="fas fa-user-check mr-1"></i>
                                                    Activate
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-12 text-gray-500">
                                        <i class="fas fa-users text-4xl mb-4 block"></i>
                                        <p class="font-semibold">No patients found.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Info Card -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                    <div>
                        <h4 class="font-semibold text-blue-800 mb-2">How Patient Account Activation Works:</h4>
                        <ul class="text-sm text-blue-700 space-y-1">
                            <li>• <strong>Activate:</strong> Generates a temporary password and enables login access</li>
                            <li>• <strong>Deactivate:</strong> Disables login access (patient data is preserved)</li>
                            <li>• <strong>Temporary Password:</strong> Share the generated password with the patient via email or phone</li>
                            <li>• <strong>Security:</strong> Patients should change their password after first login</li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide success messages after 10 seconds
    const successAlert = document.querySelector('.bg-green-100');
    if (successAlert) {
        setTimeout(() => {
            successAlert.style.opacity = '0';
            setTimeout(() => successAlert.remove(), 300);
        }, 10000);
    }
});
</script>

<?= view('templates/footer') ?>
