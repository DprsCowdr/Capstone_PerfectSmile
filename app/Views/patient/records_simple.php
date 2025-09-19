<?= view('templates/header') ?>

<style>
    .tooth-grid {
        display: grid;
        grid-template-columns: repeat(8, 1fr);
        gap: 8px;
        max-width: 600px;
        margin: 0 auto;
    }
    .tooth-item {
        aspect-ratio: 1;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        position: relative;
        background: white;
    }
    .tooth-number {
        font-weight: bold;
        color: #374151;
    }
    .tooth-visual {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        margin: 2px 0;
    }
    .condition-healthy { background-color: #10b981; }
    .condition-cavity { background-color: #f59e0b; }
    .condition-filled { background-color: #3b82f6; }
    .condition-crown { background-color: #8b5cf6; }
    .condition-root-canal { background-color: #ec4899; }
    .condition-extracted { background-color: #ef4444; }
    
    .stats-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .stats-number {
        font-size: 2rem;
        font-weight: bold;
        color: #3b82f6;
    }
    
    .visual-chart-item {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 16px;
        background: white;
    }
    .visual-chart-toggle {
        cursor: pointer;
        background: #3b82f6;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        font-size: 14px;
    }
    .visual-chart-content {
        display: none;
        margin-top: 16px;
        padding: 16px;
        background: #f9fafb;
        border-radius: 4px;
    }
    .visual-chart-content.show {
        display: block;
    }
</style>

<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    
    <div class="flex-1 flex flex-col min-h-screen bg-white">
        <!-- Topbar -->
        <nav class="flex items-center justify-between bg-white shadow px-6 py-4 mb-6">
            <button id="sidebarToggleTop" class="block lg:hidden text-gray-600 mr-3 text-2xl focus:outline-none">
                <i class="fa fa-bars"></i>
            </button>
            <div class="flex items-center ml-auto">
                <span class="mr-4 hidden lg:inline text-gray-600 font-semibold"><?= $user['name'] ?? 'Patient' ?></span>
                <div class="relative">
                    <button class="focus:outline-none">
                        <img class="w-10 h-10 rounded-full border-2 border-gray-200" src="<?= base_url('img/undraw_profile.svg') ?>" alt="Profile">
                    </button>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            <div class="max-w-6xl mx-auto">
                <!-- Page Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">My Dental Records</h1>
                    <p class="text-gray-600">View your dental history and tooth conditions</p>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="stats-card">
                        <div class="stats-number"><?= count($records) ?></div>
                        <div class="text-sm text-gray-600">Total Records</div>
                    </div>
                    <div class="stats-card">
                        <div class="stats-number"><?= $healthyTeeth + ($treatmentCounts['filled'] ?? 0) ?></div>
                        <div class="text-sm text-gray-600">Healthy Teeth</div>
                    </div>
                    <div class="stats-card">
                        <div class="stats-number"><?= ($treatmentCounts['filled'] ?? 0) + ($treatmentCounts['crown'] ?? 0) + ($treatmentCounts['root-canal'] ?? 0) ?></div>
                        <div class="text-sm text-gray-600">Treatments</div>
                    </div>
                    <div class="stats-card">
                        <div class="stats-number">
                            <?php if (!empty($latestDate)): ?>
                                <?= date('M j', strtotime($latestDate)) ?>
                            <?php else: ?>
                                None
                            <?php endif; ?>
                        </div>
                        <div class="text-sm text-gray-600">Last Visit</div>
                    </div>
                </div>

                <!-- Dental Chart Section -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Dental Chart</h2>
                        <p class="text-gray-600">Your current tooth conditions
                            <?php if (!empty($latestDate)): ?>
                                as of <?= date('F j, Y', strtotime($latestDate)) ?>
                            <?php endif; ?>
                        </p>
                    </div>

                    <!-- Tooth Grid -->
                    <div class="tooth-grid mb-6">
                        <?php for ($i = 1; $i <= 32; $i++): ?>
                            <?php 
                                $condition = $toothConditions[$i] ?? 'healthy';
                                $hasData = isset($toothConditions[$i]);
                            ?>
                            <div class="tooth-item <?= $hasData ? 'border-blue-300' : '' ?>">
                                <div class="tooth-number"><?= $i ?></div>
                                <div class="tooth-visual condition-<?= strtolower(str_replace([' ', '_'], '-', $condition)) ?>"></div>
                                <div class="text-xs text-gray-600 mt-1"><?= ucfirst($condition) ?></div>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <!-- Legend -->
                    <div class="border-t pt-4">
                        <h4 class="font-semibold text-gray-900 mb-3">Condition Legend</h4>
                        <div class="grid grid-cols-3 md:grid-cols-6 gap-3">
                            <div class="flex items-center">
                                <div class="w-4 h-4 rounded-full condition-healthy mr-2"></div>
                                <span class="text-sm">Healthy</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-4 h-4 rounded-full condition-cavity mr-2"></div>
                                <span class="text-sm">Cavity</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-4 h-4 rounded-full condition-filled mr-2"></div>
                                <span class="text-sm">Filled</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-4 h-4 rounded-full condition-crown mr-2"></div>
                                <span class="text-sm">Crown</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-4 h-4 rounded-full condition-root-canal mr-2"></div>
                                <span class="text-sm">Root Canal</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-4 h-4 rounded-full condition-extracted mr-2"></div>
                                <span class="text-sm">Extracted</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visual Charts Section -->
                <?php if (!empty($visualCharts)): ?>
                <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Visual Dental Charts</h2>
                        <p class="text-gray-600"><?= count($visualCharts) ?> visual charts from dental examinations</p>
                    </div>

                    <div class="space-y-4">
                        <?php foreach ($visualCharts as $index => $chart): ?>
                            <div class="visual-chart-item">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Chart from <?= date('F j, Y', strtotime($chart['record_date'])) ?></h4>
                                        <p class="text-sm text-gray-600">Visual annotations and markings</p>
                                    </div>
                                    <button class="visual-chart-toggle" onclick="toggleChart(<?= $index ?>)">
                                        <i class="fas fa-eye mr-1"></i>View Chart
                                    </button>
                                </div>
                                <div id="chart-<?= $index ?>" class="visual-chart-content">
                                    <?php if (!empty($chart['visual_chart_data'])): ?>
                                        <img src="<?= $chart['visual_chart_data'] ?>" 
                                             alt="Visual Dental Chart - <?= date('M j, Y', strtotime($chart['record_date'])) ?>" 
                                             class="w-full max-w-2xl mx-auto rounded border"
                                             onerror="this.src='/public/img/d.jpg'; this.alt='Chart not available';">
                                    <?php else: ?>
                                        <div class="text-center py-8 text-gray-500">
                                            <i class="fas fa-image text-3xl mb-2"></i>
                                            <p>No visual chart available for this date</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Records History -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Records History</h2>
                    
                    <?php if (empty($records)): ?>
                        <div class="text-center py-12">
                            <i class="fas fa-tooth text-6xl text-gray-300 mb-4"></i>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Records Yet</h3>
                            <p class="text-gray-600 mb-6">You haven't had any dental checkups recorded.</p>
                            <a href="<?= base_url('/patient/book-appointment') ?>" 
                               class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                                Book Your First Appointment
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($records as $record): ?>
                                <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                    <div class="flex items-start justify-between mb-4">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">Dental Checkup</h3>
                                            <p class="text-gray-600">
                                                <i class="fas fa-calendar mr-2"></i>
                                                <?= !empty($record['record_date']) ? date('F j, Y', strtotime($record['record_date'])) : 'Date not specified' ?>
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm text-gray-500">Record #<?= $record['id'] ?></div>
                                            <?php if (!empty($record['dentist_name'])): ?>
                                                <div class="text-sm text-gray-600">
                                                    <i class="fas fa-user-md mr-1"></i>Dr. <?= htmlspecialchars($record['dentist_name']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <?php if (!empty($record['treatment'])): ?>
                                        <div class="mb-4">
                                            <h4 class="font-medium text-gray-900 mb-2">Treatment</h4>
                                            <p class="text-gray-700 bg-gray-50 p-3 rounded"><?= nl2br(htmlspecialchars($record['treatment'])) ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($record['notes'])): ?>
                                        <div>
                                            <h4 class="font-medium text-gray-900 mb-2">Notes</h4>
                                            <p class="text-gray-700 bg-blue-50 p-3 rounded"><?= nl2br(htmlspecialchars($record['notes'])) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-lg p-6 mt-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <a href="<?= base_url('/patient/book-appointment') ?>" 
                           class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-calendar-plus text-white"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">Book Appointment</div>
                                <div class="text-sm text-gray-600">Schedule your next visit</div>
                            </div>
                        </a>
                        <a href="<?= base_url('/patient/appointments') ?>" 
                           class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                            <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">View Appointments</div>
                                <div class="text-sm text-gray-600">Check upcoming visits</div>
                            </div>
                        </a>
                        <a href="<?= base_url('/patient/profile') ?>" 
                           class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                            <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-user text-white"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">Update Profile</div>
                                <div class="text-sm text-gray-600">Manage your information</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Minimal JavaScript for visual chart toggles
function toggleChart(index) {
    const content = document.getElementById(`chart-${index}`);
    const button = content.previousElementSibling.querySelector('.visual-chart-toggle');
    
    if (content.classList.contains('show')) {
        content.classList.remove('show');
        button.innerHTML = '<i class="fas fa-eye mr-1"></i>View Chart';
        button.className = 'visual-chart-toggle';
    } else {
        content.classList.add('show');
        button.innerHTML = '<i class="fas fa-eye-slash mr-1"></i>Hide Chart';
        button.className = 'visual-chart-toggle bg-gray-600';
    }
}

// Simple sidebar toggle
document.getElementById('sidebarToggleTop')?.addEventListener('click', function() {
    // Simple mobile sidebar toggle logic if needed
});
</script>

<?= view('templates/footer') ?>
