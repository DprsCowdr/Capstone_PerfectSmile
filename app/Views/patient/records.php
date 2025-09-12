<?php echo view('templates/header', ['title' => 'My Records']); ?>

<!-- Include Display Manager modules for printing functionality -->
<script src="<?= base_url('js/modules/records-utilities.js') ?>"></script>
<script src="<?= base_url('js/modules/display-manager.js') ?>"></script>

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

<style>
document.addEventListener('DOMContentLoaded', function() {
	const tabContent = document.getElementById('tab-content');
	const tabs = document.querySelectorAll('.records-tab');
	let currentTab = 'appointments';

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
			.then(res => { if (!res.ok) throw new Error('Status ' + res.status); return res.text(); })
			.then(html => {
				const trimmed = (html || '').trim();
				if (!trimmed) {
					tabContent.innerHTML = `<div class="p-8 text-center text-gray-500">No records found.</div>`;
					return;
				}
				tabContent.innerHTML = html;
			})
			.catch(err => {
				tabContent.innerHTML = `<div class="p-8 text-center text-red-600">Unable to load records. ${err.message}</div>`;
			});
	}

	tabs.forEach(t => t.addEventListener('click', function() {
		currentTab = this.dataset.tab;
		updateActiveTab();
		loadTabContent();
	}));

	// initial load
	updateActiveTab();
	loadTabContent();

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
</style>

<?php echo view('templates/footer'); ?>

