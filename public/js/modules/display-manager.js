/**
 * Display Manager - Handles all UI rendering and content display
 * Separates presentation logic from business logic
 */

class DisplayManager {
    constructor() {
        this.utilities = new RecordsUtilities();
    }

    // ==================== PATIENT INFO DISPLAY ====================

    displayPatientInfo(patient) {
        const content = this.generatePatientInfoHTML(patient);
        this.setModalContent(content);
    }

    generatePatientInfoHTML(patient) {
        return `
            <div class="bg-white text-[13px] leading-relaxed">
                ${this.generatePatientHeader(patient)}
                <div class="space-y-8">
                    ${this.generatePersonalInfoSection(patient)}
                </div>
            </div>`;
    }

    generatePatientHeader(patient) {
        return `
            <div class="mb-6 border border-gray-200 rounded-md p-4 bg-gray-50">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                            <i class="fas fa-user text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-gray-800 tracking-tight">${patient.name}</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Patient ID: ${patient.id}</p>
                            <div class="flex flex-wrap items-center gap-2 mt-2">
                                <span class="px-2 py-0.5 text-[11px] font-medium rounded-full ${patient.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'}">${patient.status || 'Active'}</span>
                                <span class="px-2 py-0.5 text-[11px] font-medium rounded-full bg-blue-100 text-blue-700"><i class="fas fa-calendar mr-1"></i>${patient.created_at ? new Date(patient.created_at).getFullYear() : 'N/A'}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="window.print()" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-medium rounded-md">Print</button>
                        <button onclick="recordsManager.exportPatientData(${patient.id})" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-[11px] font-medium rounded-md border border-blue-200">Export</button>
                    </div>
                </div>
            </div>`;
    }

    generatePersonalInfoSection(patient) {
        return `
            <section class="space-y-3">
                <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2"><i class="fas fa-id-card text-blue-500"></i>Personal</h3>
                <div class="border border-gray-200 rounded-md p-4 bg-white">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        ${this.generateInfoField('Full Name', patient.name)}
                        ${this.generateInfoField('Date of Birth', patient.date_of_birth ? new Date(patient.date_of_birth).toLocaleDateString() : 'N/A')}
                        ${this.generateInfoField('Age', patient.age ? `${patient.age} yrs` : 'N/A')}
                        ${this.generateInfoField('Email', patient.email)}
                        ${this.generateInfoField('Phone', patient.phone)}
                        ${this.generateInfoField('Gender', patient.gender ? patient.gender.charAt(0).toUpperCase() + patient.gender.slice(1) : 'N/A')}
                        ${this.generateInfoField('Occupation', patient.occupation)}
                        ${this.generateInfoField('Emergency Contact', patient.emergency_contact)}
                        ${this.generateInfoField('Insurance', patient.insurance_provider)}
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        ${this.generateInfoField('Address', patient.address, 'block')}
                    </div>
                </div>
            </section>`;
    }

    // REMOVED: generateMedicalHistorySection() - Medical history section removed from basic info view
    
    // REMOVED: generateDentalHistorySection() - Dental history section removed from basic info view
    
    // REMOVED: generateRecentRecordsSummary() - Visits summary removed from basic info view
    
    // REMOVED: generateNotesSection() - Notes section removed from basic info view

    generateInfoField(label, value, type = 'inline') {
        const val = value || 'N/A';
        if (type === 'block') {
            return `<div><p class="text-[11px] uppercase tracking-wide text-gray-500 mb-1">${label}</p><p class="text-[13px] text-gray-800">${val}</p></div>`;
        }
        return `<div><p class="text-[11px] uppercase tracking-wide text-gray-500 mb-1">${label}</p><p class="text-[13px] font-medium text-gray-800">${val}</p></div>`;
    }

    // ==================== DENTAL RECORDS DISPLAY ====================

    displayDentalRecords(records) {
        let content = '<div class="bg-white p-6">';
        content += '<div class="flex justify-between items-center mb-4">';
        content += '<h3 class="text-lg font-bold">Dental Records</h3>';
        if (records.length > 0) {
            content += `<button onclick="recordsManager.displayManager.printDentalRecords(${JSON.stringify(records).replace(/"/g, '&quot;')})" 
                            class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-colors flex items-center gap-1">
                        <i class="fas fa-print"></i>
                        Print Records
                    </button>`;
        }
        content += '</div>';
        
        if (records.length === 0) {
            content += '<p class="text-gray-500 text-center py-8">No dental records found</p>';
        } else {
            content += '<div class="space-y-4">';
            records.forEach(record => {
                content += this.generateDentalRecordCard(record);
            });
            content += '</div>';
        }
        
        content += '</div>';
        this.setModalContent(content);
    }

    generateDentalRecordCard(record) {
        return `
            <div class="border rounded-lg p-4">
                <div class="flex justify-between items-start mb-2">
                    <h4 class="font-semibold">${new Date(record.record_date).toLocaleDateString()}</h4>
                    <span class="text-sm text-gray-600">Dr. ${record.dentist_name}</span>
                </div>
                ${record.chief_complaint ? `<p class="text-sm mb-2"><strong>Chief Complaint:</strong> ${record.chief_complaint}</p>` : ''}
                ${record.treatment ? `<p class="text-sm mb-2"><strong>Treatment:</strong> ${record.treatment}</p>` : ''}
                ${record.notes ? `<p class="text-sm text-gray-600">${record.notes}</p>` : ''}
            </div>
        `;
    }

    // ==================== DENTAL CHART DISPLAY ====================

    displayDentalChart(chartResponse) {
        console.log('🎨 Displaying dental chart with response:', chartResponse);
        console.log('📊 Visual charts data:', chartResponse.visual_charts);
        
        const content = this.generateDentalChartHTML(chartResponse);
        this.setModalContent(content);
        
        // Initialize visual charts after content is loaded
        if (chartResponse.visual_charts && chartResponse.visual_charts.length > 0) {
            console.log('✅ Initializing visual charts:', chartResponse.visual_charts.length, 'charts found');
            setTimeout(() => {
                this.initializeVisualCharts(chartResponse.visual_charts);
            }, 100);
        } else {
            console.log('❌ No visual charts found in response');
        }
    }

    generateDentalChartHTML(chartResponse) {
        return `
        <div class="bg-white p-6">
            <h3 class="text-lg font-bold mb-4">
                <i class="fas fa-chart-line text-blue-500 mr-2"></i>
                Dental Chart
                <span class="text-sm font-normal text-gray-600 ml-2">(Interactive View)</span>
            </h3>
            
            ${this.generateVisualChartsSection(chartResponse)}
            
            <!-- Enhanced 3D Dental Model Viewer -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h4 class="font-semibold text-gray-800 mb-4 text-center">
                    <i class="fas fa-cube text-blue-500 mr-2"></i>
                    Interactive 3D Dental Model
                </h4>
                
                <div class="dental-3d-viewer relative" id="dentalModalViewer" style="height: 500px;">
                    <div class="model-loading text-center py-8" id="modalModelLoading">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2 text-blue-500"></i>
                        <p class="text-gray-600">Loading 3D Model...</p>
                    </div>
                    <div class="model-error hidden text-center py-8" id="modalModelError">
                        <i class="fas fa-exclamation-triangle text-red-500 text-2xl mb-2"></i>
                        <p class="text-red-600 mb-2">Failed to load 3D model</p>
                        <button onclick="recordsManager.dental3DManager.initModal3D()" class="px-3 py-1 bg-blue-500 text-white rounded text-sm hover:bg-blue-600">
                            Retry
                        </button>
                    </div>
                    <canvas class="dental-3d-canvas"></canvas>
                    
                    <!-- Enhanced Model Controls -->
                    <div class="model-controls" style="position: absolute; top: 10px; right: 10px; display: flex; flex-direction: column; gap: 8px;">
                        <button class="model-control-btn" onclick="recordsManager.dental3DManager.resetCamera()" title="Reset View" style="padding: 8px; background: rgba(0,0,0,0.7); color: white; border: none; border-radius: 4px; cursor: pointer;">
                            <i class="fas fa-home"></i>
                        </button>
                        <button class="model-control-btn" onclick="recordsManager.dental3DManager.toggleWireframe()" title="Toggle Wireframe" style="padding: 8px; background: rgba(0,0,0,0.7); color: white; border: none; border-radius: 4px; cursor: pointer;">
                            <i class="fas fa-border-all"></i>
                        </button>
                        <button class="model-control-btn" onclick="recordsManager.dental3DManager.toggleAutoRotate()" title="Auto Rotate" style="padding: 8px; background: rgba(0,0,0,0.7); color: white; border: none; border-radius: 4px; cursor: pointer;">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                
            </div>
            
        </div>
        `;
    }

    // REMOVED: generateColorLegend() - Color legend section removed from dental chart display
    
    // REMOVED: generateConditionsSummaryPanel() - Conditions summary panel removed from dental chart display
    
    // REMOVED: generateChartSummary() - Chart summary removed from dental chart display

    generateVisualChartsSection(chartResponse) {
        if (!chartResponse.visual_charts || chartResponse.visual_charts.length === 0) {
            return `
                <div class="bg-yellow-50 rounded-lg p-4 mb-6 border border-yellow-200">
                    <h4 class="font-semibold text-gray-800 mb-2 flex items-center">
                        <i class="fas fa-image text-yellow-500 mr-2"></i>
                        Visual Dental Charts
                    </h4>
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-info-circle text-yellow-500 mr-1"></i>
                        No visual chart annotations found for this patient.
                    </p>
                </div>
            `;
        }

        const visualChartsHTML = chartResponse.visual_charts.map((chart, index) => `
            <div class="border border-gray-200 rounded-lg overflow-hidden mb-4">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h5 class="text-sm font-semibold text-gray-700">
                            <i class="fas fa-calendar text-blue-500 mr-1"></i>
                            ${this.formatDate(chart.record_date)}
                        </h5>
                        <div class="flex gap-1">
                            <button onclick="this.parentElement.parentElement.nextElementSibling.classList.toggle('hidden')" 
                                    class="text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600">
                                <i class="fas fa-eye mr-1"></i>Toggle View
                            </button>
                            <button onclick="recordsManager.displayManager.printSingleChart(${JSON.stringify(chart).replace(/"/g, '&quot;')})" 
                                    class="text-xs bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600">
                                <i class="fas fa-print mr-1"></i>Print
                            </button>
                        </div>
                    </div>
                </div>
                <div class="hidden p-4 bg-white">
                    <div class="flex justify-center">
                        <div class="border-2 border-gray-200 rounded-lg overflow-hidden bg-white max-w-full relative">
                            <!-- Container for the visual chart -->
                            <div class="visual-chart-container" data-chart-data="${chart.visual_chart_data}" data-chart-index="${index}">
                                <canvas class="visual-chart-canvas" style="max-height: 600px; max-width: 100%; display: block;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 p-3 bg-green-50 rounded-lg">
                        <p class="text-xs text-green-700">
                            <i class="fas fa-info-circle mr-1"></i>
                            This visual chart shows the dentist's markings and annotations made during the examination on <strong>${this.formatDate(chart.record_date)}</strong>.
                        </p>
                    </div>
                </div>
            </div>
        `).join('');

        return `
            <div class="bg-green-50 rounded-lg p-4 mb-6 border border-green-200">
                <div class="flex justify-between items-center mb-3">
                    <h4 class="font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-image text-green-500 mr-2"></i>
                        Visual Dental Charts 
                        <span class="ml-2 text-xs bg-green-500 text-white px-2 py-1 rounded">${chartResponse.visual_charts.length}</span>
                    </h4>
                    <button onclick="recordsManager.displayManager.printVisualCharts(${JSON.stringify(chartResponse.visual_charts).replace(/"/g, '&quot;')})" 
                            class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-colors flex items-center gap-1">
                        <i class="fas fa-print"></i>
                        Print Charts
                    </button>
                </div>
                <p class="text-sm text-gray-600 mb-4">
                    <i class="fas fa-info-circle text-green-500 mr-1"></i>
                    Visual annotations and markings made by dentists during examinations.
                </p>
                ${visualChartsHTML}
            </div>
        `;
    }

    initializeVisualCharts(visualCharts) {
        console.log('🎨 Initializing visual charts:', visualCharts);
        
        const drawStrokes = (ctx, strokes) => {
            for (const s of (strokes || [])) {
                if (!s?.points?.length) continue;
                ctx.save();
                ctx.lineJoin = 'round';
                ctx.lineCap = 'round';
                ctx.lineWidth = Number(s.size) || 2;
                if (s.tool === 'eraser') {
                    ctx.globalCompositeOperation = 'destination-out';
                    ctx.strokeStyle = 'rgba(0,0,0,1)';
                } else {
                    ctx.globalCompositeOperation = 'source-over';
                    ctx.strokeStyle = s.color || '#ff0000';
                }
                ctx.beginPath();
                ctx.moveTo(s.points[0].x, s.points[0].y);
                for (let i = 1; i < s.points.length; i++) {
                    ctx.lineTo(s.points[i].x, s.points[i].y);
                }
                ctx.stroke();
                ctx.restore();
            }
        };

        visualCharts.forEach((chart, index) => {
            console.log(`🎨 Processing chart ${index}:`, chart);
            const container = document.querySelector(`[data-chart-index="${index}"]`);
            if (!container) {
                console.log(`❌ Container not found for chart ${index}`);
                return;
            }
            const canvas = container.querySelector('.visual-chart-canvas');
            if (!canvas) {
                console.log(`❌ Canvas not found for chart ${index}`);
                return;
            }
            const ctx = canvas.getContext('2d');
            const raw = chart.visual_chart_data;
            console.log(`📊 Chart ${index} raw data:`, raw ? raw.substring(0, 100) + '...' : 'null');
            if (!raw || !raw.trim()) { 
                console.log(`❌ No data for chart ${index}`);
                canvas.style.display = 'none'; 
                return; 
            }

            // JSON state?
            if (raw.trim().startsWith('{')) {
                console.log(`📊 Chart ${index} is JSON format`);
                try {
                    const state = JSON.parse(raw);
                    console.log(`📊 Chart ${index} parsed state:`, state);
                    canvas.width = state.width || 1000;
                    canvas.height = state.height || 600;
                    if (state.background) {
                        console.log(`📊 Chart ${index} loading background:`, state.background);
                        const bg = new Image();
                        bg.onload = () => {
                            console.log(`📊 Chart ${index} background loaded, drawing strokes`);
                            ctx.drawImage(bg, 0, 0, canvas.width, canvas.height);
                            drawStrokes(ctx, state.strokes);
                        };
                        bg.onerror = () => {
                            console.log(`❌ Chart ${index} background failed to load, trying to draw strokes without background`);
                            drawStrokes(ctx, state.strokes);
                        };
                        // Handle relative paths by prepending base URL if needed
                        let bgSrc = state.background;
                        if (bgSrc.startsWith('/img/')) {
                            bgSrc = window.location.origin + bgSrc;
                        }
                        // Handle absolute localhost URLs (convert to relative)
                        else if (bgSrc.startsWith('http://localhost:8080/')) {
                            bgSrc = bgSrc.replace('http://localhost:8080', '');
                            bgSrc = window.location.origin + bgSrc;
                        }
                        console.log(`📊 Chart ${index} final background URL:`, bgSrc);
                        bg.src = bgSrc;
                    } else {
                        console.log(`📊 Chart ${index} no background, drawing strokes only`);
                        drawStrokes(ctx, state.strokes);
                    }
                } catch (e) {
                    console.warn(`❌ Chart ${index} Invalid visual_chart_data JSON:`, e);
                    canvas.style.display = 'none';
                }
                return;
            }

            // Backward compatibility: data URL images
            if (raw.startsWith('data:image/')) {
                console.log(`📊 Chart ${index} is data URL format`);
                const chartImg = new Image();
                chartImg.onload = () => {
                    console.log(`📊 Chart ${index} data URL image loaded`);
                    canvas.width = chartImg.width;
                    canvas.height = chartImg.height;
                    this.renderVisualChart(ctx, chartImg, canvas);
                };
                chartImg.onerror = () => { 
                    console.log(`❌ Chart ${index} data URL image failed to load`);
                    canvas.style.display = 'none'; 
                };
                chartImg.src = raw;
                return;
            }

            // Unknown format
            console.log(`❌ Chart ${index} unknown format:`, raw.substring(0, 50));
            canvas.style.display = 'none';
        });
    }

    renderVisualChart(ctx, chartImg, canvas) {
        // Create temporary canvas to analyze the image
        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = chartImg.width;
        tempCanvas.height = chartImg.height;
        const tempCtx = tempCanvas.getContext('2d');
        tempCtx.drawImage(chartImg, 0, 0);
        
        const imageData = tempCtx.getImageData(0, 0, tempCanvas.width, tempCanvas.height);
        let hasBackground = false;
        
        // Sample pixels to detect if background is present
        for (let i = 0; i < imageData.data.length; i += 4 * 200) { // Sample every 200th pixel
            const r = imageData.data[i];
            const g = imageData.data[i + 1];
            const b = imageData.data[i + 2];
            const a = imageData.data[i + 3];
            
            // Check for beige/cream colors typical of dental chart background
            if (a > 200 && r > 200 && g > 200 && b > 180 && b < 220) {
                hasBackground = true;
                break;
            }
        }
        
        if (hasBackground) {
            // This is a composite image, display it directly
            ctx.drawImage(chartImg, 0, 0);
            console.log('Rendered composite visual chart');
        } else {
            // This is drawings-only, add background first
            const bgImg = new Image();
            bgImg.onload = () => {
                // Draw background first
                ctx.drawImage(bgImg, 0, 0, canvas.width, canvas.height);
                // Then draw the annotations on top
                ctx.drawImage(chartImg, 0, 0);
                console.log('Rendered visual chart with background added');
            };
            bgImg.onerror = () => {
                // Fallback: just draw the annotations without background
                ctx.drawImage(chartImg, 0, 0);
                console.warn('Failed to load background, showing annotations only');
            };
            bgImg.src = `${window.BASE_URL}/img/d.jpg`;
        }
    }

    // ==================== PRINT FUNCTIONALITY ====================

    printVisualCharts(visualCharts) {
        console.log('🖨️ Printing visual charts:', visualCharts);
        
        // Create print window
        const printWindow = window.open('', '_blank', 'width=800,height=600');
        
        // Get patient info from current modal
        const patientName = document.querySelector('.modal-panel h3')?.textContent || 'Patient';
        
        // Generate print content
        const printContent = this.generatePrintContent(visualCharts, patientName);
        
        printWindow.document.write(printContent);
        printWindow.document.close();
        
        // Wait for images to load then print
        printWindow.onload = function() {
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 1000);
        };
    }

    printSingleChart(chart) {
        console.log('🖨️ Printing single chart:', chart);
        
        // Create print window
        const printWindow = window.open('', '_blank', 'width=800,height=600');
        
        // Get patient info from current modal
        const patientName = document.querySelector('.modal-panel h3')?.textContent || 'Patient';
        
        // Generate print content for single chart
        const printContent = this.generateSingleChartPrintContent(chart, patientName);
        
        printWindow.document.write(printContent);
        printWindow.document.close();
        
        // Wait for images to load then print
        printWindow.onload = function() {
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 1000);
        };
    }

    generatePrintContent(visualCharts, patientName) {
        const latestChart = visualCharts[0]; // Get the most recent chart
        
        return `
<!DOCTYPE html>
<html>
<head>
    <title>Visual Dental Chart - ${patientName}</title>
    <style>
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
        
        body {
            font-family: Arial, sans-serif;
            background: white;
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
        
        .chart-image-container {
            margin: 2mm 0;
            text-align: center;
            height: 120mm;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .print-chart-canvas {
            max-width: 160mm;
            max-height: 115mm;
            border: 1px solid #ddd;
            border-radius: 2px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }
        
        .treatments-section {
            margin-top: 2mm;
            height: 130mm;
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
            max-height: 125mm;
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
    </style>
</head>
<body>
    <div class="a4-page">
        <div class="chart-header">
            <h1>Visual Dental Chart</h1>
            <div class="patient-info">
                <p><strong>Patient:</strong> ${patientName}</p>
                <p><strong>Date Printed:</strong> ${new Date().toLocaleDateString()}</p>
            </div>
        </div>

        <div class="chart-image-container">
            <div class="visual-chart-wrapper" 
                 data-chart-data='${JSON.stringify(latestChart.visual_chart_data)}'>
                <canvas id="print-chart-canvas" width="800" height="600" class="print-chart-canvas"></canvas>
            </div>
        </div>

        <div class="treatments-section">
            <h3>Treatment History</h3>
            <div class="treatment-list">
                ${visualCharts.map(chart => `
                    <div class="treatment-item">
                        <span class="treatment-date">${new Date(chart.record_date).toLocaleDateString()}:</span>
                        <span class="treatment-description">Visual dental examination with annotations</span>
                    </div>
                `).join('')}
            </div>
        </div>
    </div>

    <script>
        function renderPrintChart() {
            const canvas = document.getElementById('print-chart-canvas');
            if (!canvas) return;
            
            const ctx = canvas.getContext('2d');
            const wrapper = document.querySelector('.visual-chart-wrapper');
            const chartData = wrapper.getAttribute('data-chart-data');
            
            if (!chartData) return;
            
            try {
                const data = JSON.parse(chartData);
                
                // Load and draw background image
                const img = new Image();
                img.onload = function() {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    
                    // Draw strokes if they exist
                    if (data.strokes && Array.isArray(data.strokes)) {
                        drawStrokes(ctx, data.strokes);
                    }
                };
                img.onerror = function() {
                    // Try alternative URL if the first one failed
                    const altUrl = data.background.replace('localhost:8080', 'localhost:8081');
                    if (altUrl !== data.background) {
                        img.src = altUrl;
                    } else {
                        // Draw placeholder
                        ctx.fillStyle = '#f0f0f0';
                        ctx.fillRect(0, 0, canvas.width, canvas.height);
                        ctx.fillStyle = '#666';
                        ctx.font = '16px Arial';
                        ctx.textAlign = 'center';
                        ctx.fillText('Chart image not available', canvas.width/2, canvas.height/2);
                    }
                };
                img.src = data.background;
            } catch (error) {
                console.error('Error rendering visual chart:', error);
            }
        }
        
        function drawStrokes(ctx, strokes) {
            for (const stroke of strokes) {
                if (!stroke || !Array.isArray(stroke.points) || stroke.points.length === 0) continue;
                
                ctx.save();
                ctx.lineJoin = 'round';
                ctx.lineCap = 'round';
                ctx.lineWidth = Number(stroke.size) || 2;
                
                if (stroke.tool === 'eraser') {
                    ctx.globalCompositeOperation = 'destination-out';
                    ctx.strokeStyle = 'rgba(0,0,0,1)';
                } else {
                    ctx.globalCompositeOperation = 'source-over';
                    ctx.strokeStyle = stroke.color || '#ff0000';
                }
                
                ctx.beginPath();
                ctx.moveTo(stroke.points[0].x, stroke.points[0].y);
                for (let i = 1; i < stroke.points.length; i++) {
                    ctx.lineTo(stroke.points[i].x, stroke.points[i].y);
                }
                ctx.stroke();
                ctx.restore();
            }
        }
        
        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            renderPrintChart();
        });
    </script>
</body>
</html>
        `;
    }

    generateSingleChartPrintContent(chart, patientName) {
        return `
<!DOCTYPE html>
<html>
<head>
    <title>Visual Dental Chart - ${patientName}</title>
    <style>
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
        
        body {
            font-family: Arial, sans-serif;
            background: white;
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
        
        .chart-image-container {
            margin: 2mm 0;
            text-align: center;
            height: 200mm;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .print-chart-canvas {
            max-width: 180mm;
            max-height: 195mm;
            border: 1px solid #ddd;
            border-radius: 2px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }
        
        .chart-info {
            margin-top: 2mm;
            text-align: center;
            font-size: 8pt;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="a4-page">
        <div class="chart-header">
            <h1>Visual Dental Chart</h1>
            <div class="patient-info">
                <p><strong>Patient:</strong> ${patientName}</p>
                <p><strong>Examination Date:</strong> ${new Date(chart.record_date).toLocaleDateString()}</p>
                <p><strong>Date Printed:</strong> ${new Date().toLocaleDateString()}</p>
            </div>
        </div>

        <div class="chart-image-container">
            <div class="visual-chart-wrapper" 
                 data-chart-data='${JSON.stringify(chart.visual_chart_data)}'>
                <canvas id="print-chart-canvas" width="800" height="600" class="print-chart-canvas"></canvas>
            </div>
        </div>

        <div class="chart-info">
            <p>Visual dental examination chart with dentist annotations and markings</p>
        </div>
    </div>

    <script>
        function renderPrintChart() {
            const canvas = document.getElementById('print-chart-canvas');
            if (!canvas) return;
            
            const ctx = canvas.getContext('2d');
            const wrapper = document.querySelector('.visual-chart-wrapper');
            const chartData = wrapper.getAttribute('data-chart-data');
            
            if (!chartData) return;
            
            try {
                const data = JSON.parse(chartData);
                
                // Load and draw background image
                const img = new Image();
                img.onload = function() {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    
                    // Draw strokes if they exist
                    if (data.strokes && Array.isArray(data.strokes)) {
                        drawStrokes(ctx, data.strokes);
                    }
                };
                img.onerror = function() {
                    // Try alternative URL if the first one failed
                    const altUrl = data.background.replace('localhost:8080', 'localhost:8081');
                    if (altUrl !== data.background) {
                        img.src = altUrl;
                    } else {
                        // Draw placeholder
                        ctx.fillStyle = '#f0f0f0';
                        ctx.fillRect(0, 0, canvas.width, canvas.height);
                        ctx.fillStyle = '#666';
                        ctx.font = '16px Arial';
                        ctx.textAlign = 'center';
                        ctx.fillText('Chart image not available', canvas.width/2, canvas.height/2);
                    }
                };
                img.src = data.background;
            } catch (error) {
                console.error('Error rendering visual chart:', error);
            }
        }
        
        function drawStrokes(ctx, strokes) {
            for (const stroke of strokes) {
                if (!stroke || !Array.isArray(stroke.points) || stroke.points.length === 0) continue;
                
                ctx.save();
                ctx.lineJoin = 'round';
                ctx.lineCap = 'round';
                ctx.lineWidth = Number(stroke.size) || 2;
                
                if (stroke.tool === 'eraser') {
                    ctx.globalCompositeOperation = 'destination-out';
                    ctx.strokeStyle = 'rgba(0,0,0,1)';
                } else {
                    ctx.globalCompositeOperation = 'source-over';
                    ctx.strokeStyle = stroke.color || '#ff0000';
                }
                
                ctx.beginPath();
                ctx.moveTo(stroke.points[0].x, stroke.points[0].y);
                for (let i = 1; i < stroke.points.length; i++) {
                    ctx.lineTo(stroke.points[i].x, stroke.points[i].y);
                }
                ctx.stroke();
                ctx.restore();
            }
        }
        
        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            renderPrintChart();
        });
    </script>
</body>
</html>
        `;
    }

    printDentalRecords(records) {
        console.log('🖨️ Printing dental records:', records);
        
        // Create print window
        const printWindow = window.open('', '_blank', 'width=800,height=600');
        
        // Get patient info from current modal
        const patientName = document.querySelector('.modal-panel h3')?.textContent || 'Patient';
        
        // Generate print content for dental records
        const printContent = this.generateDentalRecordsPrintContent(records, patientName);
        
        printWindow.document.write(printContent);
        printWindow.document.close();
        
        // Wait for content to load then print
        printWindow.onload = function() {
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 1000);
        };
    }

    generateDentalRecordsPrintContent(records, patientName) {
        return `
<!DOCTYPE html>
<html>
<head>
    <title>Dental Records - ${patientName}</title>
    <style>
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
        
        body {
            font-family: Arial, sans-serif;
            background: white;
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
        
        .header {
            text-align: center;
            margin-bottom: 3mm;
            border-bottom: 1px solid #333;
            padding-bottom: 1mm;
        }
        
        .header h1 {
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
        
        .records-section {
            margin-top: 2mm;
            height: 250mm;
            overflow: hidden;
        }
        
        .records-section h3 {
            font-size: 9pt;
            font-weight: bold;
            color: #333;
            margin: 0 0 1mm 0;
            border-bottom: 1px solid #ddd;
            padding-bottom: 0.5mm;
        }
        
        .record-item {
            margin-bottom: 2mm;
            padding: 1mm;
            border: 1px solid #ddd;
            border-radius: 1px;
            break-inside: avoid;
        }
        
        .record-date {
            font-weight: bold;
            color: #007bff;
            font-size: 8pt;
        }
        
        .record-dentist {
            font-size: 6pt;
            color: #666;
            margin-bottom: 1mm;
        }
        
        .record-content {
            font-size: 6pt;
            line-height: 1.1;
        }
        
        .record-content p {
            margin: 0.5mm 0;
        }
        
        .record-content strong {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="a4-page">
        <div class="header">
            <h1>Dental Records</h1>
            <div class="patient-info">
                <p><strong>Patient:</strong> ${patientName}</p>
                <p><strong>Date Printed:</strong> ${new Date().toLocaleDateString()}</p>
            </div>
        </div>

        <div class="records-section">
            <h3>Treatment History</h3>
            ${records.map(record => `
                <div class="record-item">
                    <div class="record-date">${new Date(record.record_date).toLocaleDateString()}</div>
                    <div class="record-dentist">Dr. ${record.dentist_name || 'Unknown'}</div>
                    <div class="record-content">
                        ${record.chief_complaint ? `<p><strong>Chief Complaint:</strong> ${record.chief_complaint}</p>` : ''}
                        ${record.treatment ? `<p><strong>Treatment:</strong> ${record.treatment}</p>` : ''}
                        ${record.notes ? `<p><strong>Notes:</strong> ${record.notes}</p>` : ''}
                    </div>
                </div>
            `).join('')}
        </div>
    </div>
</body>
</html>
        `;
    }

    // ==================== OTHER DISPLAY METHODS ====================

    displayAppointments(appointmentData) {
        const content = this.generateAppointmentsHTML(appointmentData);
        this.setModalContent(content);
    }

    displayTreatments(treatmentData) {
        const content = this.generateTreatmentsHTML(treatmentData);
        this.setModalContent(content);
    }

    displayMedicalRecords(medicalData) {
        const content = this.generateMedicalRecordsHTML(medicalData);
        this.setModalContent(content);
    }

    displayInvoiceHistory(invoiceData) {
        const content = this.generateInvoiceHistoryHTML(invoiceData);
        this.setModalContent(content);
    }

    displayPrescriptions(prescriptionData) {
        const content = this.generatePrescriptionsHTML(prescriptionData);
        this.setModalContent(content);
    }

    // ==================== HELPER METHODS ====================

    setModalContent(content) {
        const modalContent = document.getElementById('modalContent');
        if (modalContent) {
            modalContent.innerHTML = content;
        }
    }

    formatDate(dateString) {
        if (!dateString) return 'N/A';
        try {
            return new Date(dateString).toLocaleDateString();
        } catch (error) {
            console.error('Error formatting date:', error);
            return 'Invalid Date';
        }
    }

    // Placeholder methods for appointments, treatments, and medical records
    // These can be implemented based on your specific requirements
    generateAppointmentsHTML(appointmentData) {
        console.log('📋 Generating appointments HTML with data:', appointmentData);
        
        // Handle both old and new data formats
        let presentAppointments = [];
        let pastAppointments = [];
        let totalAppointments = 0;
        
        if (appointmentData && appointmentData.success) {
            // Check for categorized format (new)
            if (appointmentData.present_appointments !== undefined || appointmentData.past_appointments !== undefined) {
                presentAppointments = appointmentData.present_appointments || [];
                pastAppointments = appointmentData.past_appointments || [];
                totalAppointments = appointmentData.total_appointments || (presentAppointments.length + pastAppointments.length);
            }
            // Check for flat appointments array (old format)
            else if (appointmentData.appointments && Array.isArray(appointmentData.appointments)) {
                const appointments = appointmentData.appointments;
                const currentDateTime = new Date().toISOString();
                
                presentAppointments = appointments.filter(apt => {
                    const aptDateTime = apt.appointment_datetime || `${apt.appointment_date} ${apt.appointment_time}`;
                    return aptDateTime >= currentDateTime;
                });
                
                pastAppointments = appointments.filter(apt => {
                    const aptDateTime = apt.appointment_datetime || `${apt.appointment_date} ${apt.appointment_time}`;
                    return aptDateTime < currentDateTime;
                });
                
                totalAppointments = appointments.length;
            }
        }
        
        // If no appointments found
        if (totalAppointments === 0) {
            return `
                <div class="bg-white p-6">
                    <h3 class="text-lg font-bold mb-4">
                        <i class="fas fa-calendar text-blue-600 mr-2"></i>Appointments
                    </h3>
                    <div class="text-center py-8">
                        <i class="far fa-calendar-alt text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 text-sm">No appointments found for this patient.</p>
                        <p class="text-gray-400 text-xs mt-2">Appointments will appear here once scheduled.</p>
                    </div>
                </div>
            `;
        }

        // Helper function for status styling (similar to patientsTable.js)
        const getStatusClass = (status) => {
            switch(status?.toLowerCase()) {
                case 'completed':
                    return 'bg-green-100 text-green-800';
                case 'confirmed':
                case 'scheduled':
                    return 'bg-blue-100 text-blue-800';
                case 'pending':
                case 'pending_approval':
                    return 'bg-yellow-100 text-yellow-800';
                case 'ongoing':
                    return 'bg-purple-100 text-purple-800';
                case 'cancelled':
                    return 'bg-red-100 text-red-800';
                case 'no_show':
                    return 'bg-orange-100 text-orange-800';
                default:
                    return 'bg-gray-100 text-gray-800';
            }
        };

        // Helper function to format appointment card (enhanced version of patientsTable.js approach)
        const formatAppointmentCard = (appointment, isUpcoming = true) => {
            const date = appointment.appointment_datetime 
                ? new Date(appointment.appointment_datetime).toLocaleDateString('en-US', { 
                    weekday: 'short', 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                })
                : (appointment.appointment_date ? new Date(appointment.appointment_date).toLocaleDateString() : 'Date not specified');
                
            const time = appointment.appointment_datetime 
                ? new Date(appointment.appointment_datetime).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})
                : (appointment.appointment_time || 'Time not specified');
                
            const statusClass = getStatusClass(appointment.status);
            const cardBorderClass = isUpcoming ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-gray-50';
            
            return `
                <div class="border ${cardBorderClass} rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-calendar text-blue-600"></i>
                            <h4 class="font-semibold text-gray-800">Appointment #${appointment.id}</h4>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full font-medium ${statusClass}">
                            ${appointment.status || 'Scheduled'}
                        </span>
                    </div>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2 text-gray-700">
                            <i class="fas fa-clock text-gray-400 w-4"></i>
                            <span><strong>Date:</strong> ${date} at ${time}</span>
                        </div>
                        
                        ${appointment.appointment_type ? `
                            <div class="flex items-center gap-2 text-gray-700">
                                <i class="fas fa-tag text-gray-400 w-4"></i>
                                <span><strong>Type:</strong> ${appointment.appointment_type.charAt(0).toUpperCase() + appointment.appointment_type.slice(1)}</span>
                            </div>
                        ` : ''}
                        
                        ${appointment.branch_name ? `
                            <div class="flex items-center gap-2 text-gray-700">
                                <i class="fas fa-building text-gray-400 w-4"></i>
                                <span><strong>Branch:</strong> ${appointment.branch_name}</span>
                            </div>
                        ` : ''}
                        
                        ${appointment.dentist_name ? `
                            <div class="flex items-center gap-2 text-gray-700">
                                <i class="fas fa-user-md text-gray-400 w-4"></i>
                                <span><strong>Dentist:</strong> Dr. ${appointment.dentist_name}</span>
                            </div>
                        ` : ''}
                        
                        ${appointment.remarks ? `
                            <div class="flex items-start gap-2 text-gray-700 mt-3 pt-2 border-t border-gray-200">
                                <i class="fas fa-comment text-gray-400 w-4 mt-0.5"></i>
                                <span><strong>Notes:</strong> ${appointment.remarks}</span>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        };

        return `
            <div class="bg-white p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-800">
                        <i class="fas fa-calendar text-blue-600 mr-2"></i>Appointments
                    </h3>
                    <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                        ${totalAppointments} total
                    </span>
                </div>
                
                ${presentAppointments.length > 0 ? `
                    <div class="mb-6">
                        <h4 class="text-md font-semibold text-green-700 mb-4 flex items-center gap-2">
                            <i class="fas fa-clock text-green-600"></i>
                            Upcoming Appointments
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full">
                                ${presentAppointments.length}
                            </span>
                        </h4>
                        <div class="space-y-3">
                            ${presentAppointments.map(appointment => formatAppointmentCard(appointment, true)).join('')}
                        </div>
                    </div>
                ` : ''}

                ${pastAppointments.length > 0 ? `
                    <div class="mb-4">
                        <h4 class="text-md font-semibold text-gray-700 mb-4 flex items-center gap-2">
                            <i class="fas fa-history text-gray-600"></i>
                            Past Appointments
                            <span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-full">
                                ${pastAppointments.length}
                            </span>
                        </h4>
                        <div class="space-y-3 max-h-80 overflow-y-auto">
                            ${pastAppointments.map(appointment => formatAppointmentCard(appointment, false)).join('')}
                        </div>
                    </div>
                ` : ''}
                
                ${presentAppointments.length === 0 && pastAppointments.length === 0 ? `
                    <div class="text-center py-8">
                        <i class="far fa-calendar-alt text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 text-sm">No appointments found for this patient.</p>
                        <p class="text-gray-400 text-xs mt-2">Appointments will appear here once scheduled.</p>
                    </div>
                ` : ''}
            </div>
        `;
    }

    generateTreatmentsHTML(treatmentData) {
        console.log('🦷 Generating treatments HTML with data:', treatmentData);
        
        if (!treatmentData || !treatmentData.treatments || treatmentData.treatments.length === 0) {
            return `
                <div class="bg-white p-6">
                    <h3 class="text-lg font-bold mb-4">
                        <i class="fas fa-procedures text-purple-600 mr-2"></i>Treatments
                    </h3>
                    <p class="text-gray-500">No treatments found for this patient.</p>
                </div>
            `;
        }

        const treatments = treatmentData.treatments;
        const totalTreatments = treatmentData.total_treatments || treatments.length;

        return `
            <div class="bg-white p-6">
                <h3 class="text-lg font-bold mb-4">
                    <i class="fas fa-procedures text-purple-600 mr-2"></i>Treatments
                    <span class="text-sm font-normal text-gray-500">(${totalTreatments} total)</span>
                </h3>
                
                <div class="space-y-4">
                    ${treatments.map(treatment => `
                        <div class="border border-purple-200 rounded-lg p-4 bg-purple-50">
                            <div class="flex justify-between items-start mb-2">
                                <div class="font-medium text-gray-900">${treatment.treatment_name || 'Treatment'}</div>
                                <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded-full">
                                    ${treatment.status || 'Completed'}
                                </span>
                            </div>
                            <div class="text-sm text-gray-600 mb-2">
                                <i class="fas fa-calendar mr-1"></i>Date: ${this.utilities.formatDate(treatment.treatment_date)}
                                ${treatment.tooth_number ? `<i class="fas fa-tooth ml-3 mr-1"></i>Tooth #${treatment.tooth_number}` : ''}
                            </div>
                            ${treatment.description ? `
                                <div class="text-sm text-gray-700 mb-2">
                                    <i class="fas fa-file-text mr-1"></i>Description: ${treatment.description}
                                </div>
                            ` : ''}
                            <div class="text-sm text-gray-600">
                                <i class="fas fa-user-md mr-1"></i>Dr. ${treatment.dentist_name || 'Not specified'}
                                ${treatment.cost ? `<i class="fas fa-dollar-sign ml-3 mr-1"></i>$${treatment.cost}` : ''}
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    generateMedicalRecordsHTML(medicalData) {
        console.log('🏥 Generating medical records HTML with data:', medicalData);
        
        return `
            <div class="bg-white p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold">
                        <i class="fas fa-file-medical text-red-600 mr-2"></i>Medical History
                    </h3>
                    <button onclick="window.recordsManager?.closePatientRecordsModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form class="space-y-6">
                    <!-- Dental History Section -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="text-md font-semibold text-blue-700 mb-4">Dental History</h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Previous Dentist (Optional):</label>
                                <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" 
                                       placeholder="nabuakkk">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Last Dental Visit Date (Optional):</label>
                                <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" 
                                       value="2025-08-12">
                            </div>
                        </div>
                    </div>

                    <!-- Medical History Section -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="text-md font-semibold text-green-700 mb-4">Medical History (All fields optional)</h4>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3 mb-4">
                            <p class="text-sm text-yellow-800">
                                <i class="fas fa-info-circle mr-1"></i>
                                <strong>For Staff Convenience:</strong> All medical history fields are optional. You can leave any field blank if the patient doesn't have the information, doesn't know the answer, or prefers not to answer.
                            </p>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Name of Physician (Optional):</label>
                                <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" 
                                       placeholder="BRandon">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Specialty (Optional):</label>
                                <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" 
                                       placeholder="BRandon">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Office Telephone Number (Optional):</label>
                                <input type="tel" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" 
                                       placeholder="908098089809809">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Office Address (Optional):</label>
                                <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" rows="2" 
                                          placeholder="BRandon"></textarea>
                            </div>

                            <!-- Health Questions in Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Are you in good health? (Optional)</label>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input type="radio" name="good_health" value="yes" class="mr-2">
                                            <span class="text-sm">Yes</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="good_health" value="no" class="mr-2">
                                            <span class="text-sm">No</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="good_health" value="skip" class="mr-2" checked>
                                            <span class="text-sm">Skip</span>
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Are you under medical treatment now? (Optional)</label>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input type="radio" name="medical_treatment" value="yes" class="mr-2">
                                            <span class="text-sm">Yes</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="medical_treatment" value="no" class="mr-2">
                                            <span class="text-sm">No</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="medical_treatment" value="skip" class="mr-2" checked>
                                            <span class="text-sm">Skip</span>
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Have you ever had a serious illness or surgical operation?</label>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input type="radio" name="serious_illness" value="yes" class="mr-2">
                                            <span class="text-sm">Yes</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="serious_illness" value="no" class="mr-2">
                                            <span class="text-sm">No</span>
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Have you ever been hospitalized?</label>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input type="radio" name="hospitalized" value="yes" class="mr-2">
                                            <span class="text-sm">Yes</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="hospitalized" value="no" class="mr-2">
                                            <span class="text-sm">No</span>
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Do you use tobacco products? (Optional)</label>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input type="radio" name="tobacco" value="yes" class="mr-2">
                                            <span class="text-sm">Yes</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="tobacco" value="no" class="mr-2">
                                            <span class="text-sm">No</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="tobacco" value="skip" class="mr-2" checked>
                                            <span class="text-sm">Skip</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Blood Pressure (mmHg) (Optional):</label>
                                <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" 
                                       placeholder="e.g., 120/80 (leave blank if unknown)">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Allergies (Optional):</label>
                                <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" rows="2" 
                                          placeholder="Specify any allergies (leave blank if none)"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- For Women Only Section -->
                    <div class="border border-pink-200 rounded-lg p-4 bg-pink-50">
                        <h4 class="text-md font-semibold text-pink-700 mb-4">For Women Only (All fields optional)</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Are you pregnant? (Optional)</label>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="radio" name="pregnant" value="yes" class="mr-2">
                                        <span class="text-sm">Yes</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="pregnant" value="no" class="mr-2">
                                        <span class="text-sm">No</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="pregnant" value="na" class="mr-2" checked>
                                        <span class="text-sm">N/A</span>
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Are you nursing? (Optional)</label>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="radio" name="nursing" value="yes" class="mr-2">
                                        <span class="text-sm">Yes</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="nursing" value="no" class="mr-2">
                                        <span class="text-sm">No</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="nursing" value="na" class="mr-2" checked>
                                        <span class="text-sm">N/A</span>
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Are you taking birth control pills? (Optional)</label>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="radio" name="birth_control" value="yes" class="mr-2">
                                        <span class="text-sm">Yes</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="birth_control" value="no" class="mr-2">
                                        <span class="text-sm">No</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="birth_control" value="na" class="mr-2" checked>
                                        <span class="text-sm">N/A</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Medical Conditions Section -->
                    <div class="border border-red-200 rounded-lg p-4">
                        <h4 class="text-md font-semibold text-red-700 mb-4">Medical Conditions (All optional)</h4>
                        <p class="text-sm text-gray-600 mb-4">Do you have or have you ever had any of the following? (Check all that apply - leave blank if none or unknown)</p>
                        
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                            ${[
                                'High blood pressure', 'Low blood pressure', 'Epilepsy/Convulsion', 'AIDS or HIV infection',
                                'Sexually transmitted disease', 'Stomach trouble/Ulcers', 'Fainting Seizure', 'Rapid weight loss',
                                'Radiation Therapy', 'Joint replacement/implant', 'Heart surgery', 'Heart attack',
                                'Thyroid problem', 'Heart disease', 'Heart murmur', 'Hepatitis/Liver disease',
                                'Rheumatic fever', 'Hay fever/Allergies', 'Respiratory problem', 'Hepatitis/Jaundice',
                                'Tuberculosis', 'Swollen ankles', 'Kidney disease', 'Diabetes',
                                'Chest pain', 'Stroke', 'Cancer/Tumors', 'Anemia',
                                'Angina', 'Asthma', 'Emphysema', 'Bleeding problem',
                                'Blood disease', 'Head injuries', 'Arthritis/Rheumatism'
                            ].map(condition => `
                                <label class="flex items-center text-sm">
                                    <input type="checkbox" name="medical_conditions[]" value="${condition}" class="mr-2">
                                    <span>${condition}</span>
                                </label>
                            `).join('')}
                        </div>
                        
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Others (specify) (Optional):</label>
                            <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" rows="2" 
                                      placeholder="BRandonBRandonBRandonBRandonBRandon"></textarea>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button type="button" onclick="window.recordsManager?.closePatientRecordsModal()" 
                                class="px-4 py-2 text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>Save Medical History
                        </button>
                    </div>
                </form>
            </div>
        `;
    }

    generateInvoiceHistoryHTML(invoiceData) {
        console.log('💰 Generating invoice history HTML with data:', invoiceData);
        
        if (!invoiceData || !invoiceData.invoices || invoiceData.invoices.length === 0) {
            return `
                <div class="bg-white p-6">
                    <h3 class="text-lg font-bold mb-4">
                        <i class="fas fa-file-invoice text-green-600 mr-2"></i>Invoices
                    </h3>
                    <p class="text-gray-500">No invoices found for this patient.</p>
                </div>
            `;
        }

        const invoices = invoiceData.invoices || [];

        return `
            <div class="bg-white p-6">
                <h3 class="text-lg font-bold mb-4">
                    <i class="fas fa-file-invoice text-green-600 mr-2"></i>Invoices
                </h3>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Procedure</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total Amount</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Discount</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Final Amount</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${invoices.map(invoice => `
                                <tr class="border-t">
                                    <td class="px-4 py-2 text-sm font-medium text-gray-900">${invoice.invoice_number || 'N/A'}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">${invoice.procedure_name || 'General Service'}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">₱${parseFloat(invoice.total_amount || 0).toFixed(2)}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">₱${parseFloat(invoice.discount || 0).toFixed(2)}</td>
                                    <td class="px-4 py-2 text-sm font-medium text-green-600">₱${parseFloat(invoice.final_amount || 0).toFixed(2)}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">${this.formatDate(invoice.created_at)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    generatePrescriptionsHTML(prescriptionData) {
        console.log('💊 Generating prescriptions HTML with data:', prescriptionData);
        
        if (!prescriptionData || !prescriptionData.prescriptions || prescriptionData.prescriptions.length === 0) {
            return `
                <div class="bg-white p-6">
                    <h3 class="text-lg font-bold mb-4">
                        <i class="fas fa-prescription text-purple-600 mr-2"></i>Prescriptions
                    </h3>
                    <p class="text-gray-500">No prescriptions found for this patient.</p>
                </div>
            `;
        }

        const prescriptions = prescriptionData.prescriptions || [];

        return `
            <div class="bg-white p-6">
                <h3 class="text-lg font-bold mb-4">
                    <i class="fas fa-prescription text-purple-600 mr-2"></i>Prescriptions
                </h3>
                
                <div class="space-y-4">
                    ${prescriptions.map(prescription => `
                        <div class="border border-purple-200 rounded-lg p-4 bg-purple-50">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h4 class="text-md font-semibold text-purple-800">
                                        Prescription #${prescription.id}
                                    </h4>
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-user-md mr-1"></i>Dr. ${prescription.dentist_name || 'Unknown'}
                                        ${prescription.license_no ? `(License: ${prescription.license_no})` : ''}
                                    </p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full ${prescription.status === 'active' ? 'bg-green-100 text-green-800' : 
                                prescription.status === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'}">
                                    ${prescription.status || 'Unknown'}
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <strong class="text-gray-700">Issue Date:</strong>
                                    <span class="text-gray-600">${this.formatDate(prescription.issue_date)}</span>
                                </div>
                                ${prescription.next_appointment ? `
                                    <div>
                                        <strong class="text-gray-700">Next Appointment:</strong>
                                        <span class="text-gray-600">${this.formatDate(prescription.next_appointment)}</span>
                                    </div>
                                ` : ''}
                                ${prescription.ptr_no ? `
                                    <div>
                                        <strong class="text-gray-700">PTR No:</strong>
                                        <span class="text-gray-600">${prescription.ptr_no}</span>
                                    </div>
                                ` : ''}
                            </div>
                            
                            ${prescription.notes ? `
                                <div class="mt-3">
                                    <strong class="text-gray-700">Notes:</strong>
                                    <p class="text-gray-600 mt-1">${prescription.notes}</p>
                                </div>
                            ` : ''}
                            
                            ${prescription.signature_url ? `
                                <div class="mt-3">
                                    <strong class="text-gray-700">Digital Signature:</strong>
                                    <div class="mt-1">
                                        <img src="${prescription.signature_url}" alt="Doctor's Signature" class="max-w-xs border rounded">
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    // ==================== TOOTH DETAILS MODAL ====================

    showToothDetailsModal(toothNumber, toothData, toothName) {
        try {
            const displayName = toothName || `Tooth ${toothNumber}`;
            const detailsHtml = this.generateToothDetailsHTML(toothNumber, toothData, displayName);
            
            // Add to body
            document.body.insertAdjacentHTML('beforeend', detailsHtml);
        } catch (error) {
            console.error('Error showing tooth details:', error);
            this.showErrorModal('Error Loading Details', 'Unable to load tooth details. Please try again.');
        }
    }

    generateToothDetailsHTML(toothNumber, toothData, displayName) {
        if (!Array.isArray(toothData) || toothData.length === 0) {
            return this.generateEmptyToothDetailsHTML(toothNumber, displayName);
        }

        return `
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                    ${this.generateToothDetailsHeader(toothNumber, displayName)}
                    ${this.generateToothDetailsContent(toothNumber, toothData, displayName)}
                    ${this.generateToothDetailsFooter()}
                </div>
            </div>
        `;
    }

    generateEmptyToothDetailsHTML(toothNumber, displayName) {
        return `
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                    ${this.generateToothDetailsHeader(toothNumber, displayName)}
                    <div class="p-6">
                        <div class="text-center py-8">
                            <i class="fas fa-search text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-500 text-lg">No records found for this tooth.</p>
                            <p class="text-gray-400 text-sm mt-2">This tooth has not been examined yet.</p>
                        </div>
                    </div>
                    ${this.generateToothDetailsFooter()}
                </div>
            </div>
        `;
    }

    generateToothDetailsHeader(toothNumber, displayName) {
        return `
            <div class="sticky top-0 bg-white border-b p-4 flex justify-between items-center">
                <h3 class="text-xl font-bold flex items-center">
                    <i class="fas fa-tooth text-blue-500 mr-3"></i>
                    <div class="flex flex-col">
                        <span>${displayName}</span>
                        <span class="text-sm text-gray-600 font-normal">Tooth #${toothNumber} - Complete History</span>
                    </div>
                </h3>
                <button onclick="this.closest('.fixed').remove()" class="text-gray-500 hover:text-gray-700 text-xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
    }

    generateToothDetailsContent(toothNumber, toothData, displayName) {
        // Implementation would go here - similar to the original but broken down
        return `<div class="p-6"><!-- Tooth details content --></div>`;
    }

    generateToothDetailsFooter() {
        return `
            <div class="sticky bottom-0 bg-gray-50 border-t p-4 flex justify-end">
                <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors">
                    <i class="fas fa-times mr-2"></i>Close
                </button>
            </div>
        `;
    }

    showErrorModal(title, message) {
        const errorModal = `
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                    <div class="text-center">
                        <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-4"></i>
                        <h3 class="text-lg font-bold text-red-800 mb-2">${title}</h3>
                        <p class="text-gray-600 mb-4">${message}</p>
                        <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', errorModal);
    }
}

// Export for use
window.DisplayManager = DisplayManager;
