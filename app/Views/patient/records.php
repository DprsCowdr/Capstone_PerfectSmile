<?php echo view('templates/header', ['title' => 'My Records']); ?>

<!-- Include Display Manager modules for printing functionality -->
<script src="<?= base_url('js/modules/records-utilities.js') ?>"></script>
<script src="<?= base_url('js/modules/display-manager.js') ?>"></script>

<!-- Three.js Library for 3D Dental Model -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>

<!-- 3D Dental Viewer Styles and Scripts -->
<link rel="stylesheet" href="<?= base_url('css/dental-3d-viewer.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/records-management.css') ?>">
<script src="<?= base_url('js/dental-3d-viewer.js') ?>"></script>

<!-- Modular Records Management System (Admin System) -->
<script src="<?= base_url('js/modules/records-utilities.js') ?>"></script>
<script src="<?= base_url('js/modules/modal-controller.js') ?>"></script>
<script src="<?= base_url('js/modules/data-loader.js') ?>"></script>
<script src="<?= base_url('js/modules/display-manager.js') ?>"></script>
<script src="<?= base_url('js/modules/dental-3d-manager.js') ?>"></script>
<script src="<?= base_url('js/modules/conditions-analyzer.js') ?>"></script>
<script src="<?= base_url('js/modules/records-manager.js') ?>"></script>

<?php echo view('templates/sidebar', ['active' => 'records', 'user' => $user]); ?>

<div class="main-content" data-sidebar-offset>
	<?php echo view('templates/patient_topbar', ['user' => $user]); ?>

	<div class="container mx-auto p-6">
		<div class="bg-gradient-to-r from-green-600 to-green-700 rounded-lg p-6 mb-8 text-white">
			<div class="flex items-center justify-between">
				<div>
					<h1 class="text-3xl font-bold mb-2">My Records</h1>
					<p class="text-green-100 text-lg">View your appointments, treatments, prescriptions and invoices</p>
				</div>
				<div class="hidden md:block">
					<svg class="w-16 h-16 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
					</svg>
				</div>
			</div>
		</div>

	<!-- Patient Info removed for privacy on patient records page -->

		<!-- Tabs -->
		<div class="bg-white rounded-lg shadow-md mb-6">
			<div class="px-6 py-4 border-b border-gray-200">
				<div class="flex items-center justify-between gap-3 flex-wrap">
					<div>
						<h3 class="text-lg font-semibold text-gray-900">Records</h3>
						<p class="text-sm text-gray-600">Access your personal records</p>
					</div>
					<div class="flex items-center gap-2">
						<button id="debugRecordsBtn" class="px-3 py-2 rounded-lg bg-yellow-100 border border-yellow-300 text-yellow-700 text-sm hover:bg-yellow-200 shadow-sm" title="Debug records loading">
							<i class="fas fa-bug mr-2"></i>Debug Records
						</button>
						<button id="printVisualChartBtn" class="px-3 py-2 rounded-lg bg-white border border-gray-300 text-gray-700 text-sm hover:bg-gray-50 shadow-sm" title="Print latest visual chart with treatment list">
							<i class="fas fa-print mr-2"></i>Print Visual Chart
						</button>
					</div>
				</div>
			</div>
			<div class="px-6 py-4">
				<div class="flex flex-wrap gap-2">
					<button class="records-tab flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white shadow-sm hover:bg-blue-700 transition-colors" data-tab="appointments">
						<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
						</svg>
						Appointments
					</button>
					<button class="records-tab flex items-center px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors" data-tab="treatments">
						<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
						</svg>
						Treatments
					</button>
					<button class="records-tab flex items-center px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors" data-tab="prescriptions">
						<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
						</svg>
						Prescriptions
					</button>
					<button class="records-tab flex items-center px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors" data-tab="invoices">
						<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
						</svg>
						Invoices
					</button>
					<button class="records-tab flex items-center px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors" data-tab="3d-chart">
						<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
						</svg>
						3D Chart
					</button>
				</div>
			</div>
		</div>

		<div id="tab-content" class="bg-white rounded-lg shadow-md min-h-48">
			<div class="flex items-center justify-center py-16">
				<div class="text-center">
					<div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto mb-4"></div>
					<p class="text-gray-500 text-lg">Loading your records...</p>
				</div>
			</div>
		</div>

	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const tabContent = document.getElementById('tab-content');
	const tabs = document.querySelectorAll('.records-tab');
	let currentTab = 'appointments';
	
	console.log('DOM loaded, found tabs:', tabs.length);
	console.log('Tab content element:', tabContent);

	function updateActiveTab() {
		tabs.forEach(tab => {
			if (tab.dataset.tab === currentTab) {
				tab.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
				tab.classList.add('bg-blue-600', 'text-white', 'shadow-sm');
			} else {
				tab.classList.remove('bg-blue-600', 'text-white', 'shadow-sm');
				tab.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
			}
		});
	}

	function loadTabContent() {
		if (!currentTab) return;
		tabContent.innerHTML = `
			<div class="flex items-center justify-center py-16">
				<div class="text-center">
					<div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto mb-4"></div>
					<p class="text-gray-500 text-lg">Loading ${currentTab.replace('_',' ')}...</p>
				</div>
			</div>
		`;

		const url = `<?php echo site_url('patient/records'); ?>?ajax=1&tab=${currentTab}`;

		fetch(url)
			.then(res => { 
				console.log('Response status:', res.status);
				if (!res.ok) throw new Error('Status ' + res.status); 
				return res.text(); 
			})
			.then(html => {
				console.log('Received HTML length:', html ? html.length : 0);
				const trimmed = (html || '').trim();
				if (!trimmed) {
					tabContent.innerHTML = `<div class="p-8 text-center text-gray-500">No records found.</div>`;
					return;
				}
				tabContent.innerHTML = html;
				
				// Initialize 3D viewer if 3D chart tab is loaded
				if (currentTab === '3d-chart') {
					initialize3DViewer();
				}
			})
			.catch(err => {
				console.error('Fetch error:', err);
				tabContent.innerHTML = `<div class="p-8 text-center text-red-600">Unable to load records. ${err.message}</div>`;
			});
	}

	tabs.forEach(t => {
		console.log('Adding click listener to tab:', t.dataset.tab);
		t.addEventListener('click', function() {
			console.log('Tab clicked:', this.dataset.tab);
			currentTab = this.dataset.tab;
			updateActiveTab();
			loadTabContent();
		});
	});

	// initial load
	updateActiveTab();
	loadTabContent();

	// ============== Debug Records ==============
	const debugBtn = document.getElementById('debugRecordsBtn');
	if (debugBtn) {
		debugBtn.addEventListener('click', async function() {
			try {
				const response = await fetch('<?= base_url('patient/debug-records') ?>');
				const data = await response.json();
				console.log('Debug Records Response:', data);
				alert('Debug info logged to console. Check browser console for details.');
			} catch (err) {
				console.error('Debug error:', err);
				alert('Debug failed: ' + err.message);
			}
		});
	}

	
	// Initialize 3D viewer using same approach as admin view
	function initialize3DViewer() {
		console.log('🎯 Initializing 3D viewer using admin approach...');
		
		// Get dental chart data from PHP
		const dentalChartData = <?= json_encode($dentalChart ?? null) ?>;
		
		if (!dentalChartData || !dentalChartData.chart_data) {
			console.log('⚠️ No dental chart data available');
			return;
		}
		
		// Initialize 3D viewer using same approach as admin view (patientsTable.php)
		try {
			if (window.Dental3DViewer) {
				console.log('🎯 Initializing 3D viewer for dental chart...');
				
				// Ensure proper cleanup before creating new instance
				if (window._patientChart3DViewer) {
					console.log('⚠️ Existing 3D viewer found, cleaning up...');
					try {
						window._patientChart3DViewer.destroy();
					} catch (e) {
						console.warn('Warning during existing viewer cleanup:', e);
					}
					window._patientChart3DViewer = null;
				}
				
				// Initialize new instance using same approach as admin
				window._patientChart3DViewer = new window.Dental3DViewer('dentalChart3DViewer', {
					enableToothSelection: true,
					showControls: true,
					highlightOnClick: false, // Same as admin
					onToothClick: (toothNumber, clickPoint, event, meta) => {
						showChartToothPopup(toothNumber, clickPoint, event, meta);
					},
					onModelLoaded: () => {
						console.log('✅ 3D model loaded successfully');
						// Apply colors after model is loaded
						applyChartColors(dentalChartData);
					}
				});
				
				const initResult = window._patientChart3DViewer.init();
				if (!initResult) {
					console.error('❌ Failed to initialize 3D viewer');
					return;
				}
				
				console.log('✅ 3D viewer initialized, waiting for model to load...');
			} else {
				console.warn('⚠️ Dental3DViewer class not available');
			}
		} catch (e) {
			console.error('❌ Error initializing 3D viewer:', e);
		}
	}
	
	// Show popup for tooth information (same as admin view)
	function showChartToothPopup(toothNumber, clickPoint, event, meta) {
		const viewerContainer = document.getElementById('dentalChart3DViewer');
		if (!viewerContainer) return;
		
		let popup = document.getElementById('chartTreatmentPopup');
		if (!popup) {
			popup = document.createElement('div');
			popup.id = 'chartTreatmentPopup';
			popup.className = 'treatment-popup';
			popup.innerHTML = `
				<div class="treatment-popup-header">
					<span class="treatment-popup-title" id="chartPopupTitle">Tooth Information</span>
					<button class="treatment-popup-close" id="chartPopupClose"><i class="fas fa-times"></i></button>
				</div>
				<div class="treatment-popup-content" id="chartPopupContent"></div>
			`;
			viewerContainer.appendChild(popup);
			
			const closeBtn = popup.querySelector('#chartPopupClose');
			closeBtn.addEventListener('click', () => { 
				popup.style.display = 'none';
			});
		}

		// Get tooth data from chart data
		const dentalChartData = <?= json_encode($dentalChart ?? null) ?>;
		const toothData = dentalChartData?.chart_data?.find(t => t.tooth_number == toothNumber);
		
		// Update title
		const title = document.getElementById('chartPopupTitle');
		if (title) {
			title.textContent = `Tooth #${toothNumber}`;
		}
		
		// Update content
		const content = document.getElementById('chartPopupContent');
		if (content && toothData) {
			content.innerHTML = `
				<div class="space-y-2">
					<div class="text-sm"><b>Condition:</b> <span class="capitalize">${(toothData.condition || 'Healthy').replace('_', ' ')}</span></div>
					${toothData.surface ? `<div class="text-sm"><b>Surface:</b> ${toothData.surface}</div>` : ''}
					<div class="text-sm"><b>Type:</b> ${toothData.tooth_type || 'Permanent'}</div>
					<div class="text-sm"><b>Notes:</b> ${toothData.notes ? toothData.notes : '<span class="text-gray-500">None</span>'}</div>
				</div>
			`;
		} else if (content) {
			content.innerHTML = '<div class="text-sm text-gray-500">No data available for this tooth</div>';
		}
		
		// Show popup
		popup.style.display = 'block';
		
		// Position popup near click point
		if (event) {
			const rect = viewerContainer.getBoundingClientRect();
			const x = event.clientX - rect.left;
			const y = event.clientY - rect.top;
			
			const popupWidth = 300;
			const popupHeight = 200;
			let left = x + 20;
			let top = y - 20;
			
			if (left + popupWidth > rect.width) {
				left = x - popupWidth - 20;
			}
			if (top + popupHeight > rect.height) {
				top = y - popupHeight - 20;
			}
			if (left < 0) left = 20;
			if (top < 0) top = 20;
			
			popup.style.left = left + 'px';
			popup.style.top = top + 'px';
		}
	}
	
	// Apply chart colors to 3D model (same as admin)
	function applyChartColors(chartData) {
		if (!chartData || !chartData.chart_data || !window._patientChart3DViewer) {
			console.log('⚠️ No chart data or 3D viewer not ready');
			return;
		}
		
		console.log('🎨 Applying chart colors to 3D model...');
		
		// Convert to format expected by 3D viewer
		const chartDataFor3D = chartData.chart_data.map(tooth => ({
			tooth_number: tooth.tooth_number,
			condition: tooth.condition,
			surface: tooth.surface,
			notes: tooth.notes
		}));
		
		// Apply colors using the 3D viewer's method
		window._patientChart3DViewer.applyChartColors(chartDataFor3D);
		console.log('✅ Chart colors applied successfully');
	}

	// ============== Visual Chart Printing ==============
	const printBtn = document.getElementById('printVisualChartBtn');
	if (printBtn) {
		// Initialize DisplayManager for printing
		const displayManager = new DisplayManager();
		
		printBtn.addEventListener('click', async function() {
			try {
				// Get current patient ID from the user data
				const currentPatientId = <?= $user['id'] ?? 'null' ?>;
				
				if (!currentPatientId) {
					alert('Unable to identify patient for printing.');
					return;
				}
				
				// Use DisplayManager's simplified print function
				await displayManager.printPatientRecord(currentPatientId);
				
			} catch (err) {
				console.error('Print error:', err);
				alert('Unable to print visual chart right now.');
			}
		});
	}

});
</script>

<style>
/* Patient records specific styles can go here */
.records-tab {
	cursor: pointer;
	user-select: none;
}

.records-tab:hover {
	transform: translateY(-1px);
}

#tab-content {
	min-height: 200px;
}
</style>

<!-- Patient Records Modal (Admin System Integration) -->
<div id="patientRecordsModal" class="hidden fixed inset-0 z-50 flex items-start justify-center bg-gray-900/50 backdrop-blur-sm p-4 overflow-y-auto">
    <div id="modalDialog" class="w-full max-w-5xl mx-auto">
        <div class="modal-panel relative bg-white rounded-lg border border-gray-200 shadow-xl overflow-hidden transform transition-all scale-95 opacity-0" style="min-height:560px;">
            
            <!-- Modal Header -->
            <header class="flex justify-between items-center px-5 py-3 border-b border-gray-100 bg-white">
                <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-user-md text-blue-600"></i>
                    Patient Records
                </h3>
                <button type="button" onclick="window.recordsManager?.closePatientRecordsModal()" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-md hover:bg-gray-100">
                    <span class="sr-only">Close</span>
                    <i class="fas fa-times text-sm"></i>
                </button>
            </header>
            
            <!-- Modal Content -->
            <div class="px-5 py-4 overflow-y-auto" style="height: calc(100% - 92px);">
                <div id="modalContent" class="w-full h-full text-sm">
                    <div class="flex items-center justify-center h-40">
                        <div class="text-center space-y-2">
                            <i class="fas fa-spinner fa-spin text-xl text-blue-500"></i>
                            <p class="text-xs text-gray-500">Loading patient information...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 3D Viewer Modal (Admin System Integration) -->
<div id="dentalModalViewer" class="dental-3d-viewer" style="height: 460px;">
    <div class="model-loading hidden" id="modalModelLoading">
        <i class="fas fa-spinner fa-spin mr-2"></i>Loading 3D Model... 100%
    </div>
    <div class="model-error hidden" id="modalModelError">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <div>Failed to load 3D model</div>
    </div>
    <canvas class="dental-3d-canvas" width="1022" height="826" style="width: 568px; height: 459px;"></canvas>
</div>

<?php echo view('templates/footer'); ?>

