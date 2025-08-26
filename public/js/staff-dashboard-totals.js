(function () {
    // Poll staff stats endpoint; prefer STAFF_STATS_URL if provided (richer timeseries)
    const endpoint = window.STAFF_STATS_URL || (window.BASE_URL ? window.BASE_URL + 'staff/totals' : (window.location.origin + '/staff/totals'));
    const ids = {
        patients: 'staff-total-patients',
        todayAppointments: 'staff-total-today-appointments',
        pendingApprovals: 'staff-total-pending-approvals',
        dentists: 'staff-total-dentists',
        treatments: 'staff-total-treatments'
    };

    function safeText(id, text) {
        const el = document.getElementById(id);
        if (!el) return;
        // If numeric, animate change
        const newVal = (typeof text === 'number' || /^\d+$/.test(String(text))) ? Number(text) : text;
        if (typeof newVal === 'number') {
            animateNumber(el, newVal);
        } else {
            el.textContent = text;
        }
    }

    // animate a numeric element from its current value to target over 600ms
    function animateNumber(el, target) {
        const start = Number(el.textContent.replace(/[^0-9.-]/g, '')) || 0;
        const duration = 600;
        const startTime = performance.now();
        function step(now) {
            const t = Math.min(1, (now - startTime) / duration);
            const eased = t < 0.5 ? 2*t*t : -1 + (4 - 2*t)*t; // easeInOutQuad-like
            const current = Math.round(start + (target - start) * eased);
            el.textContent = current;
            if (t < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    // Chart setup (lazy - only if canvas exists and Chart lib available)
    let totalsChart = null;
    // keep a small history of recent samples (maxSamples)
    const maxSamples = 12; // ~5 minutes at 25s interval
    let history = {
        labels: [],
        patients: [],
        appointments: [],
        treatments: []
    };

    const STORAGE_KEY = 'staffTotals_history_v1';

    function restoreHistory() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return;
            const parsed = JSON.parse(raw);
            if (parsed && parsed.labels && Array.isArray(parsed.labels)) {
                history.labels = parsed.labels.slice(-maxSamples);
                history.patients = (parsed.patients || []).slice(-maxSamples).map(v => Number(v)||0);
                history.appointments = (parsed.appointments || []).slice(-maxSamples).map(v => Number(v)||0);
                history.treatments = (parsed.treatments || []).slice(-maxSamples).map(v => Number(v)||0);
            }
        } catch (e) { console.warn('Failed to restore staff totals history', e); }
    }

    function saveHistory() {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(history));
        } catch (e) { console.warn('Failed to save staff totals history', e); }
    }

    function shiftHistory(label, p, a, t) {
        history.labels.push(label);
        history.patients.push(p);
        history.appointments.push(a);
        history.treatments.push(t);
        while (history.labels.length > maxSamples) {
            history.labels.shift(); history.patients.shift(); history.appointments.shift(); history.treatments.shift();
        }
    }

    function initChartIfNeeded() {
        const canvas = document.getElementById('staffTotalsChart');
        if (!canvas) return;
        // Prefer bundled Chart.js if available, otherwise try global Chart
        const ChartLib = window.Chart || (window.Chart && window.Chart.default) || null;
        // also check vendor path
        if (!ChartLib && typeof require === 'function') {
            try { ChartLib = require('/public/vendor/chart.js/Chart.min.js'); } catch(e) { /* ignore */ }
        }
        if (!ChartLib && !window.Chart && document.querySelector('script[src*="chart.js"]')) {
            // Chart.js likely loaded via script tag in view; wait a short time and retry
            return setTimeout(initChartIfNeeded, 300);
        }

        const ctx = canvas.getContext('2d');
        try {
                // Use a single primary dataset like dentist dashboard for consistent appearance
                totalsChart = new (window.Chart || ChartLib).Chart(ctx, {
                type: 'line',
                data: {
                    labels: history.labels.length ? history.labels : [],
                    datasets: [{
                        label: 'Appointments',
                        data: history.appointments.slice(),
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139,92,246,0.12)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true } },
                    plugins: { legend: { display: false } }
                }
            });
        } catch (e) {
            console.error('Failed to initialize totals chart', e);
            totalsChart = null;
        }
    }

    function fetchAndUpdate() {
        fetch(endpoint + (endpoint.indexOf('?') === -1 ? '' : ''), { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                if (!data || (data.success === false)) return;
                // Accept either { success:true, totals: {...} } or the richer staff/stats payload used by dentist
                const t = data.totals || data || {};
                safeText(ids.patients, t.total_patients ?? '0');
                safeText(ids.todayAppointments, t.total_appointments ?? '0');
                safeText(ids.treatments, t.total_treatments ?? '0');
                safeText(ids.dentists, t.total_dentists ?? document.getElementById(ids.dentists)?.textContent);

                // init chart if needed and update
                // If server provided time-series payload (labels/counts/etc), use it; otherwise append current totals snapshot
                const nowLabel = new Date().toLocaleTimeString();
                if (Array.isArray(data.labels) && (Array.isArray(data.counts) || Array.isArray(data.patientCounts))) {
                    // prefer server labels and counts
                    history.labels = data.labels.slice(-maxSamples);
                    history.appointments = (data.counts || []).slice(-maxSamples).map(v => Number(v)||0);
                    history.patients = (data.patientCounts || []).slice(-maxSamples).map(v => Number(v)||0);
                    // treatments may be provided in totals only
                    history.treatments = history.treatments.slice();
                    saveHistory();
                } else {
                    shiftHistory(nowLabel, t.total_patients ?? 0, t.total_appointments ?? 0, t.total_treatments ?? 0);
                    saveHistory();
                }
                if (!totalsChart) initChartIfNeeded();
                if (totalsChart) {
                    // set labels
                    totalsChart.data.labels = history.labels.slice();

                    // Determine primary data based on selector and update single dataset to match dentist behavior
                    const selector = document.getElementById('chartSelectorTop');
                    const choice = selector ? selector.value : 'appointments';
                    const mapArr = { patients: history.patients.slice(), appointments: history.appointments.slice(), treatments: history.treatments.slice() };
                    const primaryData = mapArr[choice] || history.appointments.slice();
                    totalsChart.data.datasets[0].data = primaryData;
                    totalsChart.data.datasets[0].label = choice === 'patients' ? 'Patients' : (choice === 'treatments' ? 'Treatments' : 'Appointments');
                    // Keep dentist styling for primary series
                    totalsChart.data.datasets[0].borderColor = '#8b5cf6';
                    totalsChart.data.datasets[0].backgroundColor = 'rgba(139,92,246,0.12)';
                    totalsChart.data.datasets[0].tension = 0.4;
                    totalsChart.data.datasets[0].fill = true;
                    totalsChart.data.datasets[0].pointRadius = 3;
                    totalsChart.update();

                    // Update avg, peak and recent total for visible series
                    const arr = primaryData.map(v => Number(v) || 0);
                    const numeric = arr.map(v => Number(v) || 0);
                    const avg = numeric.length ? (numeric.reduce((a,b)=>a+b,0)/numeric.length).toFixed(1) : '—';
                    const peakIdx = numeric.length ? numeric.indexOf(Math.max(...numeric)) : -1;
                    const peakLabel = history.labels[peakIdx] || '—';
                    const patientTotalEl = document.getElementById('patientTotal');
                    const avgEl = document.getElementById('avgPerDayTop');
                    const peakEl = document.getElementById('peakDayTop');
                    if (avgEl) avgEl.textContent = avg;
                    if (peakEl) peakEl.textContent = peakLabel;
                    if (patientTotalEl) patientTotalEl.textContent = (t.total_patients ?? data.patientTotal ?? document.getElementById(ids.patients)?.textContent ?? '—');

                    // If server provided nextAppointment or statusCounts, populate those areas
                    if (data.nextAppointment) {
                        const na = Array.isArray(data.nextAppointment) ? data.nextAppointment[0] : data.nextAppointment;
                        if (na && na.datetime) {
                            const d = new Date(na.datetime);
                            const label = (na.patient_name || 'Patient') + ' — ' + d.toLocaleString() + (na.service_name ? (' ('+na.service_name+')') : '');
                            const badge = document.getElementById('nextAppointmentBadge');
                            const txt = document.getElementById('nextAppointmentText');
                            if (badge) badge.classList.remove('hidden');
                            if (txt) txt.textContent = label;
                        }
                    }
                    if (data.statusCounts) {
                        const legendEl = document.getElementById('statusLegend');
                        if (legendEl) {
                            legendEl.innerHTML = '';
                            const labelsStatus = Object.keys(data.statusCounts);
                            const countsStatus = labelsStatus.map(k => data.statusCounts[k]);
                            const colors = ['#c7b8ff','#e9d5ff','#d6bcfa','#c4b5fd','#f3e8ff','#e0c3ff'];
                            labelsStatus.forEach((lbl, idx) => {
                                const count = countsStatus[idx] || 0;
                                const color = colors[idx % colors.length];
                                const row = document.createElement('div');
                                row.className = 'flex items-center gap-2 mb-1';
                                row.innerHTML = `<span style="display:inline-block;width:12px;height:12px;background:${color};border-radius:2px;"></span><span class="ml-2 font-medium">${lbl}</span><span class="ml-auto text-gray-600">${count}</span>`;
                                legendEl.appendChild(row);
                            });
                        }
                    }
                }
                    // update recent values display
                    try {
                        const recentEl = document.getElementById('recentValues');
                        if (recentEl) {
                            const sel = document.getElementById('chartSelectorTop');
                            const choice = sel ? sel.value : 'appointments';
                            const map = { patients: history.patients, appointments: history.appointments, treatments: history.treatments };
                            const arr = (map[choice] || []).slice(-5).map(v => Number(v)||0);
                            recentEl.textContent = arr.length ? arr.join(' · ') : '—';
                        }
                    } catch(e) {}
            })
            .catch(err => {
                console.error('Error fetching staff totals', err);
            });
    }

    // allow selector to trigger immediate refresh and chart update
    document.addEventListener('DOMContentLoaded', function(){
    // restore persisted history before first render
    try { restoreHistory(); } catch(e) {}

    // stats scope (mine/branch/clinic) should trigger immediate fetch
    const scopeEl = document.getElementById('statsScope');
    if (scopeEl) scopeEl.addEventListener('change', function(){ try { fetchAndUpdate(); } catch(e){console.warn(e);} });

        const sel = document.getElementById('chartSelectorTop');
        if (sel) sel.addEventListener('change', function(){
            // force immediate fetch to update chart visibility/stats
            try { fetchAndUpdate(); } catch(e) { console.warn(e); }
        });
    });

    // Initial fetch after a short delay to allow page to render
    setTimeout(function(){ try { fetchAndUpdate(); } catch(e){console.error(e);} }, 1000);
    // Poll
    setInterval(fetchAndUpdate, 25000);
})();
