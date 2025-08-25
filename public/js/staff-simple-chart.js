// Lightweight staff chart: simple polling and rendering using Chart.js
(function(){
    // prefer window.STAFF_STATS_BASE provided by the view; fall back to staff/stats
    if (!window.STAFF_STATS_BASE) window.STAFF_STATS_BASE = '/staff/stats';
    const canvas = document.getElementById('staffTotalsChart');
    if (!canvas) return;

    const metricSelect = document.getElementById('chartSelectorTop');
    const scopeSelect = document.getElementById('statsScope');
    const ctx = canvas.getContext('2d');
    let chart = null;
    const MAX_POINTS = 30;
    let labels = [];
    let data = [];
    const STORAGE_KEY = 'staff_simple_history_v1';
    // store separate series per metric
    const METRIC_KEYS = { patients: 'patients', appointments: 'appointments', treatments: 'treatments' };

    function restoreHistory(metric) {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return;
            const obj = JSON.parse(raw);
            if (!obj || !obj[metric]) return;
            const arr = obj[metric];
            labels = arr.map(s => s.t);
            data = arr.map(s => s.v);
        } catch (e) { console && console.warn && console.warn('restoreHistory error', e); }
    }

    function saveHistory(metric) {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            const obj = raw ? JSON.parse(raw) : {};
            obj[metric] = labels.map((t,i) => ({ t: t, v: data[i] })).slice(-MAX_POINTS);
            localStorage.setItem(STORAGE_KEY, JSON.stringify(obj));
        } catch (e) { /* ignore */ }
    }

    function initChart() {
        if (chart) return;
    // try to initialize from persisted history first for current metric
    const metric = metricSelect ? metricSelect.value : 'patients';
    if (!labels.length || !data.length) restoreHistory(metric);
        chart = new Chart(ctx, {
            type: 'line',
            data: { labels: labels, datasets: [{ label: 'Count', data: data, borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.12)', tension: 0.4, fill: true, pointRadius: 2 }] },
            options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { beginAtZero: true } } }
        });
    }

    function pushSample(label, value) {
        labels.push(label);
        data.push(value);
        while (labels.length > MAX_POINTS) { labels.shift(); data.shift(); }
        if (!chart) initChart(); else chart.update();
    saveHistory(metricSelect ? metricSelect.value : 'patients');
    }

    function resetChart() {
        labels = [];
        data = [];
        if (chart) { chart.data.labels = []; chart.data.datasets[0].data = []; chart.update(); }
    }

    function updateUIFromTotals(json, value) {
        const setText = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = (v === null || v === undefined) ? '—' : v; };
        setText('patientTotal', value);
        setText('avgPerDayTop', json.avgPerDay ?? '—');
        setText('peakDayTop', json.peakDay ?? '—');
        setText('nextAppointmentText', json.nextAppointment ?? '—');
        const legendEl = document.getElementById('statusLegend');
        if (legendEl) {
            legendEl.innerHTML = '';
            const statusCounts = json.statusCounts || {};
            const labels = Object.keys(statusCounts);
            const counts = labels.map(k => statusCounts[k]);
            const colors = ['#c7b8ff','#e9d5ff','#d6bcfa','#c4b5fd','#f3e8ff','#e0c3ff'];
            labels.forEach((lbl, idx) => {
                const row = document.createElement('div');
                row.className = 'flex items-center gap-2 mb-1';
                row.innerHTML = `<span style="display:inline-block;width:12px;height:12px;background:${colors[idx % colors.length]};border-radius:2px;"></span><span class="ml-2 font-medium">${lbl}</span><span class="ml-auto text-gray-600">${counts[idx] || 0}</span>`;
                legendEl.appendChild(row);
            });
        }
    }

    function computeAndDisplayAvgPeak(){
        if (!labels.length || !data.length) return;
        const sum = data.reduce((a,b)=>a+b,0);
        const avg = (sum / data.length).toFixed(1);
        const peakIdx = data.indexOf(Math.max(...data));
        const peakLabel = labels[peakIdx] || '—';
        const avgEl = document.getElementById('avgPerDayTop'); if (avgEl) avgEl.textContent = avg;
        const peakEl = document.getElementById('peakDayTop'); if (peakEl) peakEl.textContent = peakLabel;
    }

    async function fetchStats(){
        try {
            // Build URL using base endpoint and add either scope or branch_id
            const base = window.STAFF_STATS_BASE || '/staff/stats';
            let url = base;
            const scopeVal = scopeSelect ? scopeSelect.value : null;
            if (scopeVal) {
                if (scopeVal === 'all') {
                    url += '?scope=all';
                } else if (scopeVal.indexOf('branch:') === 0) {
                    const branchId = scopeVal.split(':')[1];
                    url += '?branch_id=' + encodeURIComponent(branchId);
                } else if (scopeVal === 'this' || scopeVal === 'mine') {
                    url += '?scope=mine';
                }
            }
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) throw new Error('network');
            const json = await res.json();

            // helper to get last known value for current metric
            const currentMetric = metricSelect ? metricSelect.value : 'patients';
            const lastKnown = (data && data.length) ? data[data.length - 1] : null;

            // preferred rich shape: { success: true, totals: { total_patients, total_appointments, total_treatments }, ... }
            if (json && json.success && json.totals) {
                const totals = json.totals;
                let value = 0;
                if (currentMetric === 'patients') value = totals.total_patients ?? 0;
                else if (currentMetric === 'appointments') value = totals.total_appointments ?? 0;
                else if (currentMetric === 'treatments') value = totals.total_treatments ?? 0;

                // Always refresh UI numbers/legend but only append a chart sample when the value changed
                updateUIFromTotals(json, value);
                if (lastKnown === null || lastKnown !== value) {
                    const now = new Date().toLocaleTimeString();
                    pushSample(now, value);
                    computeAndDisplayAvgPeak();
                }
                return;
            }

            // fallback: accept { labels:[], counts:[], patientCounts:[], treatmentCounts:[] }
            if (json && Array.isArray(json.labels)){
                // determine which array to use based on metric
                let incomingArr = null;
                if (currentMetric === 'patients' && Array.isArray(json.patientCounts)) incomingArr = json.patientCounts;
                else if (currentMetric === 'treatments' && Array.isArray(json.treatmentCounts)) incomingArr = json.treatmentCounts;
                else if (Array.isArray(json.counts)) incomingArr = json.counts; // appointments fallback
                // if we still have no array, abort this branch
                if (!Array.isArray(incomingArr)) {
                    // nothing we can plot from this payload
                } else {
                    const incoming = incomingArr.slice(-MAX_POINTS);
                    const incomingLast = incoming.length ? incoming[incoming.length - 1] : null;

                // if last value unchanged, refresh UI only
                if (lastKnown !== null && incomingLast !== null && lastKnown === incomingLast) {
                    // set labels for display but do not append history
                    labels = json.labels.slice(-MAX_POINTS);
                    if (!chart) initChart(); else { chart.data.labels = labels; /* keep existing data */ }
                    computeAndDisplayAvgPeak();
                    updateUIFromTotals(json, incomingLast);
                    return;
                }

                    // otherwise replace series
                    labels = json.labels.slice(-MAX_POINTS);
                    data = incoming;
                    if (!chart) initChart(); else { chart.data.labels = labels; chart.data.datasets[0].data = data; chart.update(); }
                    computeAndDisplayAvgPeak();
                    updateUIFromTotals(json, data.slice(-1)[0]);
                    saveHistory(currentMetric);
                    return;
                }
            }

            // final fallback: single-sample shape { total: number }
            if (json && typeof json.total === 'number'){
                const value = json.total;
                updateUIFromTotals(json, value);
                if (lastKnown === null || lastKnown !== value) {
                    pushSample(new Date().toLocaleTimeString(), value);
                    computeAndDisplayAvgPeak();
                }
                return;
            }

        } catch (e) {
            console && console.warn && console.warn('staff-simple-chart fetch error', e);
        }
    }

    // wire selector changes
    if (metricSelect) metricSelect.addEventListener('change', function(){ resetChart(); fetchStats(); });
    if (scopeSelect) scopeSelect.addEventListener('change', function(){ resetChart(); fetchStats(); });

    // Totals history modal handlers
    const showHistoryBtn = document.getElementById('showTotalsHistoryBtn');
    const historyModal = document.getElementById('totalsHistoryModal');
    const historyContent = document.getElementById('totalsHistoryContent');
    const closeHistoryBtn = document.getElementById('closeTotalsHistoryBtn');
    async function populateHistory(metric){
        try {
            historyContent.innerHTML = '';
            // Try server-side canonical history first (richer /staff/stats endpoint)
            if (window.STAFF_STATS_URL && window.STAFF_STATS_URL.indexOf('/staff/stats') !== -1) {
                try {
                    // Build history URL with same branch selection logic
                    const base2 = window.STAFF_STATS_BASE || '/staff/stats';
                    let url = base2;
                    const sVal = scopeSelect ? scopeSelect.value : null;
                    if (sVal) {
                        if (sVal === 'all') url += '?scope=all';
                        else if (sVal.indexOf('branch:') === 0) url += '?branch_id=' + encodeURIComponent(sVal.split(':')[1]);
                        else url += '?scope=mine';
                    }
                    const r = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    if (r.ok) {
                        const j = await r.json();
                        // prefer timeseries labels/counts if present
                        if (Array.isArray(j.labels) && Array.isArray(j.counts)) {
                            const rows = j.labels.map((lbl, i) => `<div class="flex justify-between py-1 border-b"><div>${lbl}</div><div>${j.counts[i] ?? 0}</div></div>`).join('');
                            historyContent.innerHTML = rows || 'No history available';
                            return;
                        }
                    }
                } catch (e) { /* fall back to localStorage */ }
            }

            const raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) { historyContent.textContent = 'No history available'; return; }
            const obj = JSON.parse(raw);
            const arr = (obj && obj[metric]) ? obj[metric] : [];
            if (!arr.length) { historyContent.textContent = 'No history available'; return; }
            const rows = arr.map(s => `<div class="flex justify-between py-1 border-b"><div>${s.t}</div><div>${s.v}</div></div>`).join('');
            historyContent.innerHTML = rows;
        } catch (e) { historyContent.textContent = 'Error loading history'; }
    }
    if (showHistoryBtn && historyModal && historyContent && closeHistoryBtn) {
        showHistoryBtn.addEventListener('click', function(){
            const metric = metricSelect ? metricSelect.value : 'patients';
            populateHistory(metric);
            historyModal.classList.remove('hidden');
            historyModal.classList.add('flex');
        });
        closeHistoryBtn.addEventListener('click', function(){ historyModal.classList.add('hidden'); historyModal.classList.remove('flex'); });
    }

    // on metric change restore corresponding history
    if (metricSelect) metricSelect.addEventListener('change', function(){ resetChart(); restoreHistory(this.value); if (chart) { chart.data.labels = labels; chart.data.datasets[0].data = data; chart.update(); } });

    // initial fetch and polling
    fetchStats();
    setInterval(fetchStats, 25 * 1000);
})();
