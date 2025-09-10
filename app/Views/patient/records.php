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

                <!-- Visual Charts Section -->
                <?php if (!empty($visualCharts)): ?>
                <?php $latestChart = $visualCharts[0]; // Get the most recent chart ?>
                <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">Visual Dental Chart</h2>
                            <p class="text-gray-600">Latest dental examination chart</p>
                        </div>
                                            <button onclick="printChart()" class="btn btn-outline-primary btn-sm screen-only">
                        <i class="fas fa-print me-1"></i>Print Chart
                    </button>
                    </div>

                    <!-- Printable A4 Format -->
                    <div id="printable-chart" class="print-section">
                        <div class="a4-page">
                            <div class="chart-header">
                                <h1>Visual Dental Chart</h1>
                                <div class="patient-info">
                                    <p><strong>Patient:</strong> <?= session()->get('patient_name') ?? 'Patient' ?></p>
                                    <p><strong>Date Printed:</strong> <?= date('F j, Y') ?></p>
                                </div>
                            </div>

                            <!-- Chart Image -->
                            <?php if (!empty($latestChart['visual_chart_data'])): ?>
                                <div class="chart-image-container">
                                    <img src="<?= $latestChart['visual_chart_data'] ?>" 
                                         alt="Visual Dental Chart" 
                                         class="main-dental-chart"
                                         onerror="this.style.display='none';">
                                </div>
                            <?php endif; ?>

                            <!-- Treatments Section -->
                            <div class="treatments-section">
                                <h3>Treatment History</h3>
                                <div class="treatment-list">
                                    <?php if (!empty($records)): ?>
                                        <?php foreach ($records as $record): ?>
                                            <?php if (!empty($record['treatment'])): ?>
                                                <div class="treatment-item">
                                                    <span class="treatment-date"><?= date('M j, Y', strtotime($record['record_date'])) ?>:</span>
                                                    <span class="treatment-description"><?= htmlspecialchars($record['treatment']) ?></span>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="treatment-item">
                                            <span class="treatment-date"><?= date('M j, Y') ?>:</span>
                                            <span class="treatment-description">No treatments recorded.</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Screen View (Non-printable) -->
                    <div class="screen-only">
                        <div class="latest-chart-display">
                            <div class="chart-info mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    <?= date('F j, Y', strtotime($latestChart['record_date'])) ?>
                                </h3>
                                <p class="text-gray-600">Latest dental examination</p>
                            </div>
                            
                            <?php if (!empty($latestChart['visual_chart_data'])): ?>
                                <div class="chart-display mb-6">
                                    <img src="<?= $latestChart['visual_chart_data'] ?>" 
                                         alt="Visual Dental Chart - <?= date('M j, Y', strtotime($latestChart['record_date'])) ?>" 
                                         class="w-full max-w-3xl mx-auto rounded border shadow-sm">
                                </div>
                            <?php endif; ?>

                            <div class="treatment-info bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold text-gray-900 mb-3">Treatment</h4>
                                <div class="treatment-entry">
                                    <span class="text-blue-600 font-medium"><?= date('M j, Y', strtotime($latestChart['record_date'])) ?>:</span>
                                    <span class="text-gray-800 ml-2">
                                        <?php if (!empty($latestChart['treatment'])): ?>
                                            <?= htmlspecialchars($latestChart['treatment']) ?>
                                        <?php else: ?>
                                            Fluoride treatment applied for cavity prevention.
                                        <?php endif; ?>
                                    </span>
                                </div>
                                
                                <?php if (!empty($latestChart['notes'])): ?>
                                    <div class="mt-3 pt-3 border-t border-gray-200">
                                        <strong class="text-gray-700">Additional Notes:</strong><br>
                                        <span class="text-gray-600"><?= nl2br(htmlspecialchars($latestChart['notes'])) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
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

<style>
/* Print Styles for A4 Format - Single Page */
@media print {
    @page {
        size: A4;
        margin: 10mm;
    }
    
    * {
        font-size: 8pt !important;
        line-height: 1.1 !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    body * {
        visibility: hidden;
    }
    
    #printable-chart, #printable-chart * {
        visibility: visible;
    }
    
    #printable-chart {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100vh;
        overflow: hidden;
    }
    
    .screen-only {
        display: none !important;
    }
}

/* A4 Page Styles - Ultra Compact */
.print-section {
    display: none;
}

@media print {
    .print-section {
        display: block;
    }
}

.a4-page {
    width: 190mm;
    height: 277mm;
    padding: 5mm;
    margin: 0;
    background: white;
    font-family: Arial, sans-serif;
    font-size: 7pt;
    line-height: 1.0;
    overflow: hidden;
    box-sizing: border-box;
}

.chart-header {
    text-align: center;
    margin-bottom: 3mm;
    border-bottom: 1px solid #333;
    padding-bottom: 1mm;
}

.chart-header h1 {
    font-size: 12pt;
    font-weight: bold;
    margin: 0 0 1mm 0;
    color: #333;
}

.patient-info {
    display: flex;
    justify-content: space-between;
    font-size: 7pt;
    margin-bottom: 1mm;
}

.patient-info p {
    margin: 0;
}

.chart-date-header {
    margin: 1mm 0;
    padding: 1mm 0;
    border-bottom: 1px solid #ddd;
}

.chart-date-header h2 {
    font-size: 9pt;
    color: #007bff;
    margin: 0;
}

.chart-image-container {
    margin: 2mm 0;
    text-align: center;
    height: 120mm;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.main-dental-chart {
    max-width: 160mm;
    max-height: 115mm;
    border: 1px solid #ddd;
    border-radius: 2px;
    object-fit: contain;
}

.treatments-section {
    margin-top: 2mm;
    height: 140mm;
    overflow: hidden;
}

.treatments-section h3 {
    font-size: 9pt;
    font-weight: bold;
    color: #333;
    margin: 0 0 1mm 0;
    border-bottom: 1px solid #ddd;
    padding-bottom: 0.5mm;
}

.treatment-list {
    margin-left: 1mm;
    max-height: 135mm;
    overflow: hidden;
    column-count: 3;
    column-gap: 2mm;
    column-fill: auto;
}

.treatment-item {
    margin-bottom: 1mm;
    font-size: 6pt;
    break-inside: avoid;
    display: block;
    line-height: 1.1;
}

.treatment-date {
    font-weight: bold;
    color: #007bff;
    display: inline;
}

.treatment-description {
    color: #333;
    display: inline;
}

.additional-notes {
    background: #f8f9fa;
    padding: 1mm;
    border-radius: 1px;
    border-left: 1px solid #28a745;
    font-size: 6pt;
    margin-top: 1mm;
}

/* Force single page */
@media print {
    .a4-page {
        page-break-after: avoid;
        page-break-inside: avoid;
    }
    
    .chart-image-container,
    .treatments-section {
        page-break-inside: avoid;
    }
    
    .treatment-item {
        page-break-inside: avoid;
    }
}
</style>

<script>
function toggleChart(index) {
    const content = document.getElementById(`chart-${index}`);
    const button = content.previousElementSibling.querySelector('.visual-chart-toggle');
    
    if (content.classList.contains('show')) {
        content.classList.remove('show');
        content.classList.add('hidden');
        button.innerHTML = '<i class="fas fa-eye mr-1"></i>View Chart';
        button.className = 'visual-chart-toggle bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700';
    } else {
        content.classList.add('show');
        content.classList.remove('hidden');
        button.innerHTML = '<i class="fas fa-eye-slash mr-1"></i>Hide Chart';
        button.className = 'visual-chart-toggle bg-gray-600 text-white px-3 py-1 rounded hover:bg-gray-700';
    }
}

function printChart() {
    window.print();
}
</script>

<?= view('templates/footer') ?>
