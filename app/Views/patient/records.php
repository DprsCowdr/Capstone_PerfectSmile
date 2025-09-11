<?php echo view('templates/header', ['title' => 'My Records']); ?>

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

<!-- Printable A4 section (hidden on screen) -->
<div id="print-visual-chart" class="hidden">
	<div class="a4-page">
		<div class="chart-header">
			<h1>Visual Dental Chart</h1>
			<div class="patient-info">
				<p><strong>Patient:</strong> <?= esc($user['name'] ?? 'Patient') ?></p>
				<p><strong>Date Printed:</strong> <?= date('F j, Y') ?></p>
			</div>

			<div class="chart-date" id="printChartDate"></div>
		</div>

		<div class="chart-image-container">
			<canvas id="printChartCanvas" width="800" height="600"></canvas>
		</div>

		<div class="treatments-section">
			<h3>Treatment History</h3>
			<div class="treatment-list" id="printTreatmentList"></div>
		</div>
	</div>
</div>

<script>
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
		printBtn.addEventListener('click', async function() {
			try {
				// Fetch patient visual chart JSON and records
				const resp = await fetch('<?= site_url('patient/dental-chart') ?>', { credentials: 'same-origin' });
				if (!resp.ok) throw new Error('Failed to load dental chart');
				const data = await resp.json();

				const charts = Array.isArray(data.visual_charts) ? data.visual_charts : [];
				if (charts.length === 0) {
					alert('No visual chart available to print.');
					return;
				}

				// Pick the first chart that has a valid JSON state with background or a data URL fallback
				let selected = null;
				let state = null;
				for (const c of charts) {
					const raw = c.visual_chart_data || '';
					if (!raw) continue;
					if (raw.trim().startsWith('{')) {
						try {
							const parsed = JSON.parse(raw);
							if (parsed && parsed.background) { selected = c; state = parsed; break; }
						} catch (_) { /* ignore and try next */ }
					} else if (raw.startsWith('data:image/')) {
						selected = c; state = { background: null, strokes: null, dataUrl: raw, width: 1000, height: 600 }; break;
					}
				}
				if (!selected) { selected = charts[0]; }
				if (!state) { try { state = JSON.parse(selected.visual_chart_data); } catch (_) { state = null; } }

				// Populate date header
				document.getElementById('printChartDate').textContent = selected.record_date ? new Date(selected.record_date).toLocaleDateString() : '';

				// Build treatment list from visual chart dates (fallback if no treatments API)
				const listEl = document.getElementById('printTreatmentList');
				listEl.innerHTML = charts.map(c => {
					const d = new Date(c.record_date).toLocaleDateString();
					return `<div class="treatment-item"><span class="treatment-date">${d}:</span> <span class="treatment-description">Visual dental examination with annotations</span></div>`;
				}).join('');

				// Render canvas
				await renderPrintCanvas(state);

				// Print only the section
				prepareAndPrintSection();
			} catch (err) {
				console.error(err);
				alert('Unable to print visual chart right now.');
			}
		});
	}

	async function renderPrintCanvas(state) {
		const canvas = document.getElementById('printChartCanvas');
		const ctx = canvas.getContext('2d');
		ctx.clearRect(0, 0, canvas.width, canvas.height);

		if (!state) return;

		// Fallback: if we stored a composite image (data URL), render directly
		if (state.dataUrl && state.dataUrl.startsWith('data:image/')) {
			await new Promise((resolve) => {
				const img = new Image();
				img.onload = function() {
					canvas.width = img.width; canvas.height = img.height;
					ctx.drawImage(img, 0, 0);
					resolve();
				};
				img.onerror = function() { resolve(); };
				img.src = state.dataUrl;
			});
			return;
		}

		if (!state.background) return;

		const bgUrl = normalizeBgUrl(state.background);
		await new Promise((resolve) => {
			const img = new Image();
			img.crossOrigin = 'anonymous';
			img.onload = function() {
				ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
				if (Array.isArray(state.strokes)) drawStrokes(ctx, state.strokes);
				resolve();
			};
			img.onerror = function() {
				const alt = bgUrl.replace('localhost:8080', 'localhost:8081');
				if (alt !== bgUrl) { img.src = alt; return; }
				// try with leading slash fix if missing
				try {
					let fixed = state.background;
					if (fixed && !fixed.startsWith('http') && !fixed.startsWith('/')) fixed = '/' + fixed;
					img.src = window.location.origin + fixed;
					return;
				} catch(_) {}
				resolve();
			};
			img.src = bgUrl;
		});
	}

	function drawStrokes(ctx, strokes) {
		for (const s of strokes) {
			if (!s || !Array.isArray(s.points) || s.points.length === 0) continue;
			ctx.save();
			ctx.lineJoin = 'round';
			ctx.lineCap = 'round';
			ctx.lineWidth = Number(s.size) || 2;
			if (s.tool === 'eraser') { ctx.globalCompositeOperation = 'destination-out'; ctx.strokeStyle = 'rgba(0,0,0,1)'; }
			else { ctx.globalCompositeOperation = 'source-over'; ctx.strokeStyle = s.color || '#ff0000'; }
			ctx.beginPath();
			ctx.moveTo(s.points[0].x, s.points[0].y);
			for (let i = 1; i < s.points.length; i++) ctx.lineTo(s.points[i].x, s.points[i].y);
			ctx.stroke();
			ctx.restore();
		}
	}

	function normalizeBgUrl(bg) {
		try {
			if (bg.startsWith('http://') || bg.startsWith('https://')) return bg;
			if (!bg.startsWith('/')) bg = '/' + bg;
			return window.location.origin + bg;
		} catch (e) { return bg; }
	}

	function prepareAndPrintSection() {
		const section = document.getElementById('print-visual-chart');
		if (!section) return;
		// Temporarily show section for print
		section.classList.remove('hidden');
		window.onafterprint = () => section.classList.add('hidden');
		window.print();
	}
});
</script>

<style>
@media print {
	@page { size: A4; margin: 10mm; }
	body * { visibility: hidden; }
	#print-visual-chart, #print-visual-chart * { visibility: visible; }
	#print-visual-chart { position: absolute; inset: 0; }
	.screen-only { display: none !important; }
}

.a4-page { width: 190mm; height: 277mm; padding: 5mm; background: #fff; box-sizing: border-box; }
.chart-header { text-align: center; margin-bottom: 8px; border-bottom: 1px solid #333; padding-bottom: 6px; }
.chart-header h1 { font-size: 16px; font-weight: 700; color: #333; margin: 0 0 4px 0; }
.patient-info { display: flex; justify-content: space-between; font-size: 11px; }
.chart-date { font-size: 12px; color: #2563eb; margin-top: 4px; }
.chart-image-container { margin: 8px 0; display: flex; align-items: center; justify-content: center; height: 120mm; }
#printChartCanvas { max-width: 160mm; max-height: 115mm; border: 1px solid #e5e7eb; border-radius: 2px; }
.treatments-section h3 { font-size: 13px; margin: 6px 0; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
.treatment-list { column-count: 3; column-gap: 8px; }
.treatment-item { break-inside: avoid; font-size: 11px; margin-bottom: 4px; }
.treatment-date { font-weight: 600; color: #2563eb; }
.treatment-description { color: #374151; }
</style>

<?php echo view('templates/footer'); ?>

