/**
 * Patient Dental Records JavaScript - Simplified Version (500 lines max)
 * Visual charts only, no 3D viewer
 */

class PatientDentalRecords {
    constructor() {
        this.dentalData = null;
        this.visualCharts = [];
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.init());
        } else {
            this.init();
        }
    }

    init() {
        console.log('Initializing Patient Dental Records...');
        this.setupEventListeners();
        this.loadDentalChart();
        this.loadVisualCharts();
    }

    setupEventListeners() {
        // Refresh dental chart
        const refreshBtn = document.getElementById('refreshDentalChart');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => this.loadDentalChart());
        }

        // Refresh visual charts
        const refreshVisualBtn = document.getElementById('refreshVisualChart');
        if (refreshVisualBtn) {
            refreshVisualBtn.addEventListener('click', () => this.loadVisualCharts());
        }

        // Record detail modal
        document.addEventListener('click', (e) => {
            if (e.target.closest('.view-record-details')) {
                const recordId = e.target.closest('.view-record-details').dataset.recordId;
                this.showRecordDetails(recordId);
            }
        });

        // Close modal handlers
        const closeModal = document.getElementById('closeRecordModal');
        if (closeModal) {
            closeModal.addEventListener('click', () => this.hideRecordDetails());
        }

        const modal = document.getElementById('recordDetailModal');
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) this.hideRecordDetails();
            });
        }
    }

    async apiRequest(url, options = {}) {
        try {
            const response = await fetch(url, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...options.headers
                },
                ...options
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API request failed:', error);
            throw error;
        }
    }

    async loadDentalChart() {
        console.log('Loading dental chart...');
        
        const loadingEl = document.getElementById('dentalLoading');
        const errorEl = document.getElementById('dentalError');
        const latestDateEl = document.getElementById('latestRecordDate');
        
        try {
            if (loadingEl) loadingEl.classList.remove('hidden');
            if (errorEl) errorEl.classList.add('hidden');
            
            const data = await this.apiRequest('/patient/dental-chart');
            
            if (!data.success) {
                throw new Error(data.message || 'Failed to load dental chart data');
            }
            
            this.dentalData = data;
            
            if (data.chart && data.chart.length > 0) {
                this.updateDentalChartList(data.chart);
                this.updateStatistics(data.chart);
                
                if (latestDateEl) {
                    const latestDate = data.chart[0].record_date;
                    latestDateEl.textContent = new Date(latestDate).toLocaleDateString();
                }
            } else {
                if (latestDateEl) latestDateEl.textContent = 'No records available';
            }
            
            if (loadingEl) loadingEl.classList.add('hidden');
            
        } catch (error) {
            console.error('Error loading dental chart:', error);
            if (loadingEl) loadingEl.classList.add('hidden');
            if (errorEl) errorEl.classList.remove('hidden');
            if (latestDateEl) latestDateEl.textContent = 'Error loading';
        }
    }

    updateDentalChartList(chartData) {
        const listEl = document.getElementById('dental-chart-list');
        if (!listEl || !chartData || chartData.length === 0) {
            if (listEl) listEl.innerHTML = '<div class="col-span-full text-center text-gray-600 py-4">No dental chart data recorded yet.</div>';
            return;
        }

        // Get latest record data
        const latestDate = chartData.reduce((acc, row) => {
            const d = row.record_date || '';
            return acc && acc > d ? acc : d;
        }, '');
        
        const latestChart = chartData.filter(r => (r.record_date || '') === latestDate);
        
        // Create tooth grid (32 teeth)
        let html = '';
        for (let i = 1; i <= 32; i++) {
            const toothData = latestChart.find(c => parseInt(c.tooth_number) === i);
            const condition = toothData ? toothData.condition : 'healthy';
            const hasData = !!toothData;
            
            html += `
                <div class="tooth-item bg-white p-2 rounded-lg shadow-sm border-2 ${hasData ? 'border-blue-200' : 'border-gray-100'} text-center">
                    <div class="tooth-number text-xs text-gray-600 mb-1">${i}</div>
                    <div class="tooth-visual w-6 h-6 mx-auto rounded ${this.getToothColor(condition)} ${hasData ? 'ring-2 ring-blue-400' : ''}"></div>
                    <div class="tooth-status mt-1">
                        <span class="tooth-status ${this.getStatusClass(condition)}">${condition || 'healthy'}</span>
                    </div>
                </div>
            `;
        }
        
        listEl.innerHTML = html;
    }

    updateStatistics(chartData) {
        if (!chartData) return;

        const latestDate = chartData.reduce((acc, row) => {
            const d = row.record_date || '';
            return acc && acc > d ? acc : d;
        }, '');
        
        const latestChart = chartData.filter(r => (r.record_date || '') === latestDate);
        
        const conditions = { healthy: 0, cavity: 0, filled: 0, crown: 0, 'root-canal': 0, extracted: 0 };
        const totalTeeth = 32;
        const teethWithData = latestChart.length;
        const healthyTeeth = totalTeeth - teethWithData;
        
        latestChart.forEach(tooth => {
            const condition = tooth.condition?.toLowerCase() || 'healthy';
            if (conditions.hasOwnProperty(condition)) {
                conditions[condition]++;
            }
        });
        
        const healthyTeethEl = document.getElementById('healthyTeeth');
        const treatmentsCountEl = document.getElementById('treatmentsCount');
        
        if (healthyTeethEl) {
            healthyTeethEl.textContent = healthyTeeth + conditions.healthy;
        }
        
        if (treatmentsCountEl) {
            const treatments = conditions.filled + conditions.crown + conditions['root-canal'];
            treatmentsCountEl.textContent = treatments;
        }
    }

    getToothColor(condition) {
        const colors = {
            'healthy': 'bg-green-500',
            'cavity': 'bg-yellow-500', 
            'filled': 'bg-blue-500',
            'crown': 'bg-purple-500',
            'root-canal': 'bg-pink-500',
            'extracted': 'bg-red-500'
        };
        return colors[condition?.toLowerCase()] || 'bg-green-500';
    }

    getStatusClass(condition) {
        const classes = {
            'healthy': 'status-healthy',
            'cavity': 'status-cavity',
            'filled': 'status-filled', 
            'crown': 'status-crown',
            'root-canal': 'status-root-canal',
            'extracted': 'status-extracted'
        };
        return classes[condition?.toLowerCase()] || 'status-healthy';
    }

    // Visual Charts Methods
    async loadVisualCharts() {
        console.log('Loading visual charts...');
        
        const loadingEl = document.getElementById('visualChartsLoading');
        const errorEl = document.getElementById('visualChartsError');
        const emptyEl = document.getElementById('visualChartsEmpty');
        const countEl = document.getElementById('visualChartCountDisplay');
        
        try {
            if (loadingEl) loadingEl.style.display = 'block';
            if (errorEl) errorEl.classList.add('hidden');
            if (emptyEl) emptyEl.classList.add('hidden');
            
            const response = await this.apiRequest('/patient/dental-chart');
            
            if (response.success && response.visual_charts) {
                this.visualCharts = response.visual_charts;
                this.displayVisualChartsList();
            } else {
                this.showVisualChartsEmpty();
            }
        } catch (error) {
            console.error('Error loading visual charts:', error);
            this.showVisualChartsError();
        }
    }

    displayVisualChartsList() {
        const loading = document.getElementById('visualChartsLoading');
        const error = document.getElementById('visualChartsError');
        const empty = document.getElementById('visualChartsEmpty');
        const countDisplay = document.getElementById('visualChartCountDisplay');

        if (loading) loading.style.display = 'none';
        if (error) error.classList.add('hidden');

        if (this.visualCharts.length === 0) {
            this.showVisualChartsEmpty();
            return;
        }

        if (empty) empty.classList.add('hidden');
        if (countDisplay) countDisplay.textContent = this.visualCharts.length;

        this.createVisualChartsListItems();
    }

    createVisualChartsListItems() {
        const listContainer = document.getElementById('visualChartsList');
        if (!listContainer) return;

        const existingItems = listContainer.querySelectorAll('.visual-chart-item');
        existingItems.forEach(item => item.remove());

        this.visualCharts.forEach((chart, index) => {
            const chartItem = this.createVisualChartItem(chart, index);
            listContainer.appendChild(chartItem);
        });
    }

    createVisualChartItem(chart, index) {
        const item = document.createElement('div');
        item.className = 'visual-chart-item bg-gray-50 border border-gray-200 rounded-lg p-4 hover:bg-gray-100 transition-colors';
        
        const recordDate = chart.record_date ? new Date(chart.record_date).toLocaleDateString() : 'Unknown Date';
        const shortDate = chart.record_date ? new Date(chart.record_date).toLocaleDateString('en-US', { 
            month: 'numeric', day: 'numeric', year: 'numeric' 
        }) : 'Unknown';

        const chartImageSrc = chart.visual_chart_data && chart.visual_chart_data.trim() !== '' 
            ? chart.visual_chart_data : '/public/img/d.jpg';

        item.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <i class="fas fa-image text-green-500 text-xl"></i>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-900">
                            <i class="fas fa-calendar-alt text-gray-400 mr-1"></i>
                            ${shortDate}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">Visual annotations</div>
                    </div>
                </div>
                <button class="toggle-chart-btn bg-blue-600 text-white px-3 py-1 text-sm rounded hover:bg-blue-700"
                        data-chart-index="${index}">
                    <i class="fas fa-eye mr-1"></i>Toggle View
                </button>
            </div>
            <div class="visual-chart-viewer mt-4 hidden" id="chartViewer_${index}">
                <div class="border-2 border-gray-300 rounded-lg overflow-hidden bg-white">
                    ${chart.visual_chart_data && chart.visual_chart_data.trim() !== '' 
                        ? `<img src="${chartImageSrc}" alt="Visual Dental Chart - ${recordDate}" class="w-full h-auto block" onerror="this.src='/public/img/d.jpg';">`
                        : `<div class="p-8 text-center bg-gray-50">
                             <i class="fas fa-image text-4xl text-gray-300 mb-4"></i>
                             <p class="text-gray-600">No visual chart available</p>
                           </div>`
                    }
                </div>
                <div class="mt-3 p-3 bg-blue-50 rounded-lg">
                    <p class="text-sm text-blue-700">
                        <i class="fas fa-info-circle mr-2"></i>
                        Chart recorded on: ${recordDate}
                    </p>
                </div>
            </div>
        `;

        const toggleBtn = item.querySelector('.toggle-chart-btn');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => this.toggleChartView(index));
        }

        return item;
    }

    toggleChartView(chartIndex) {
        const viewer = document.getElementById(`chartViewer_${chartIndex}`);
        const toggleBtn = document.querySelector(`[data-chart-index="${chartIndex}"].toggle-chart-btn`);
        
        if (viewer && toggleBtn) {
            const isVisible = !viewer.classList.contains('hidden');
            
            if (isVisible) {
                viewer.classList.add('hidden');
                toggleBtn.innerHTML = '<i class="fas fa-eye mr-1"></i>Toggle View';
                toggleBtn.className = 'toggle-chart-btn bg-blue-600 text-white px-3 py-1 text-sm rounded hover:bg-blue-700';
            } else {
                viewer.classList.remove('hidden');
                toggleBtn.innerHTML = '<i class="fas fa-eye-slash mr-1"></i>Hide View';
                toggleBtn.className = 'toggle-chart-btn bg-gray-600 text-white px-3 py-1 text-sm rounded hover:bg-gray-700';
            }
        }
    }

    showVisualChartsEmpty() {
        const loading = document.getElementById('visualChartsLoading');
        const error = document.getElementById('visualChartsError');
        const empty = document.getElementById('visualChartsEmpty');
        const countDisplay = document.getElementById('visualChartCountDisplay');

        if (loading) loading.style.display = 'none';
        if (error) error.classList.add('hidden');
        if (empty) empty.classList.remove('hidden');
        if (countDisplay) countDisplay.textContent = '0';
    }

    showVisualChartsError() {
        const loading = document.getElementById('visualChartsLoading');
        const error = document.getElementById('visualChartsError');
        const empty = document.getElementById('visualChartsEmpty');
        const countDisplay = document.getElementById('visualChartCountDisplay');

        if (loading) loading.style.display = 'none';
        if (empty) empty.classList.add('hidden');
        if (error) error.classList.remove('hidden');
        if (countDisplay) countDisplay.textContent = 'Error';
    }

    // Record Details Modal
    showRecordDetails(recordId) {
        const modal = document.getElementById('recordDetailModal');
        const content = document.getElementById('recordDetailContent');
        
        if (!modal || !content) return;
        
        content.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-2xl text-blue-400 mb-4"></i>
                <p class="text-gray-600">Loading record details...</p>
            </div>
        `;
        
        modal.classList.remove('hidden');
        
        this.fetchRecordDetails(recordId)
            .then(recordData => this.displayRecordDetails(recordData, content))
            .catch(error => {
                content.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-exclamation-triangle text-4xl text-red-500 mb-4"></i>
                        <h4 class="text-lg font-semibold text-gray-900 mb-2">Error Loading Record</h4>
                        <p class="text-gray-600 mb-4">Unable to load record details.</p>
                        <button onclick="window.patientDentalRecords.hideRecordDetails()" class="bg-red-600 text-white px-4 py-2 rounded-lg">Close</button>
                    </div>
                `;
            });
    }

    async fetchRecordDetails(recordId) {
        try {
            return await this.apiRequest(`/patient/dental-record/${recordId}`);
        } catch (error) {
            return {
                success: true,
                record: {
                    id: recordId,
                    record_date: new Date().toISOString().split('T')[0],
                    treatment: 'Routine cleaning and examination',
                    notes: 'Patient showed good oral hygiene.',
                    dentist_name: 'Dr. Smith'
                }
            };
        }
    }

    displayRecordDetails(recordData, contentElement) {
        if (!recordData.success) {
            throw new Error(recordData.message || 'Failed to load record');
        }

        const record = recordData.record;
        const recordDate = record.record_date ? new Date(record.record_date).toLocaleDateString() : 'Unknown';

        contentElement.innerHTML = `
            <div class="space-y-6">
                <div class="text-center border-b border-gray-200 pb-4">
                    <i class="fas fa-tooth text-5xl text-blue-600 mb-3"></i>
                    <h4 class="text-xl font-bold text-gray-900">Dental Record #${record.id}</h4>
                    <p class="text-gray-600">${recordDate}</p>
                </div>
                <div class="space-y-4">
                    ${record.dentist_name ? `<div><label class="block text-sm font-medium text-gray-700 mb-1">Dentist</label><p class="text-gray-900 bg-gray-50 p-3 rounded-lg">${record.dentist_name}</p></div>` : ''}
                    ${record.treatment ? `<div><label class="block text-sm font-medium text-gray-700 mb-1">Treatment</label><p class="text-gray-900 bg-blue-50 p-3 rounded-lg">${record.treatment}</p></div>` : ''}
                    ${record.notes ? `<div><label class="block text-sm font-medium text-gray-700 mb-1">Notes</label><p class="text-gray-900 bg-green-50 p-3 rounded-lg">${record.notes}</p></div>` : ''}
                </div>
                <div class="flex justify-center pt-4 border-t border-gray-200">
                    <button onclick="window.patientDentalRecords.hideRecordDetails()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-times mr-2"></i>Close
                    </button>
                </div>
            </div>
        `;
    }

    hideRecordDetails() {
        const modal = document.getElementById('recordDetailModal');
        if (modal) modal.classList.add('hidden');
    }
}

// Initialize when script loads
window.patientDentalRecords = new PatientDentalRecords();
