// Scoped JS for dentist dashboard only
// Keeps sidebar fixed + content offset behavior local to pages that opt-in
(function(){
    'use strict';

    function applyDentistSidebarOffset() {
        try {
            var root = document.querySelector('.dentist-dashboard-root');
            var sidebar = document.getElementById('sidebar');
            if (!root || !sidebar) return;

            var shouldActivate = window.innerWidth >= 1024 && root.hasAttribute('data-sidebar-offset');
            if (shouldActivate) {
                sidebar.classList.add('sidebar-fixed');
                root.classList.add('with-sidebar-offset-active');
            } else {
                sidebar.classList.remove('sidebar-fixed');
                root.classList.remove('with-sidebar-offset-active');
            }
        } catch (err) {
            console && console.warn && console.warn('dentist-dashboard.js apply error', err);
        }
    }

    function guardFetchStats() {
        // Small runtime logger to capture accidental scroll/focus triggers during page load
        // This is non-invasive: it only logs stack traces when focus/scrollIntoView is called.
        // Remove or comment out if noisy.
        var origFocus = Element.prototype.focus;
        Element.prototype.focus = function() {
            try {
                console && console.log && console.log('Element.focus called on', this, new Error().stack);
            } catch(e){}
            return origFocus.apply(this, arguments);
        };

        var origScrollIntoView = Element.prototype.scrollIntoView;
        Element.prototype.scrollIntoView = function() {
            try {
                console && console.log && console.log('scrollIntoView called on', this, new Error().stack);
            } catch(e){}
            return origScrollIntoView.apply(this, arguments);
        };
    }

    // Run on load + resize
    window.addEventListener('load', function(){
        applyDentistSidebarOffset();
        guardFetchStats();
    });
    window.addEventListener('resize', applyDentistSidebarOffset);
})();

// Chart and stats polling logic (moved from inline view)
(function(){
    const statsUrl = window.DENTIST_STATS_URL || (window.location.origin + '/dentist/stats');
    let appointmentsChart = null;
    let statusChart = null;
    const REFRESH_INTERVAL_MS = 15000;
    let autoRefreshTimer = null;

    function getCanvasContext(id) {
        const el = document.getElementById(id);
        return el && el.getContext ? el.getContext('2d') : null;
    }

    async function fetchStats() {
        try {
            const scopeEl = document.getElementById('statsScope');
            const scope = scopeEl ? scopeEl.value : 'mine';
            const url = statsUrl + (scope ? ('?scope=' + encodeURIComponent(scope)) : '');
            const res = await fetch(url, { credentials: 'same-origin' });
            if (!res.ok) throw new Error('Failed to fetch stats: ' + res.status);
            const data = await res.json();
            console.log('dentist/stats payload:', data);

            // next appointment
            const nextEl = document.getElementById('nextAppointment');
            if (nextEl) {
                const na = data.nextAppointment;
                let next = null;
                if (Array.isArray(na) && na.length > 0) next = na[0];
                else if (na && typeof na === 'object' && na.datetime) next = na;

                if (next) {
                    const d = new Date(next.datetime);
                    let label = (next.patient_name || 'Patient') + ' — ' + d.toLocaleString();
                    if (next.service_name) label += ' (' + next.service_name + ')';
                    const badge = document.getElementById('nextAppointmentBadge');
                    const txt = document.getElementById('nextAppointmentText');
                    if (badge) badge.classList.remove('hidden');
                    if (txt) txt.textContent = label;
                } else {
                    const badge = document.getElementById('nextAppointmentBadge');
                    const txt = document.getElementById('nextAppointmentText');
                    if (badge) badge.classList.add('hidden');
                    if (txt) txt.textContent = 'No upcoming appointments';
                }
            }

            const labels = Array.isArray(data.labels) ? data.labels.slice() : [];
            const counts = Array.isArray(data.counts) ? data.counts.slice() : [];
            let patientCounts = Array.isArray(data.patientCounts) ? data.patientCounts.slice() : [];

            // Ensure patientCounts aligns with labels length. If it doesn't, normalize/pad to avoid Chart.js rendering issues.
            if (labels.length && patientCounts.length !== labels.length) {
                console && console.warn && console.warn('dentist-dashboard: patientCounts length mismatch, normalizing to labels length');
                const normalized = [];
                for (let i = 0; i < labels.length; i++) {
                    normalized.push(Number(patientCounts[i]) || 0);
                }
                patientCounts = normalized;
            }

            const avg = counts.length ? (counts.reduce((a,b)=>a+b,0)/counts.length).toFixed(1) : 0;
            const avgEl = document.getElementById('avgPerDayTop') || document.getElementById('avgPerDay');
            if (avgEl) avgEl.textContent = avg;
            const peakIdx = counts.length ? counts.indexOf(Math.max(...counts)) : -1;
            const peakEl = document.getElementById('peakDayTop') || document.getElementById('peakDay');
            if (peakEl) peakEl.textContent = labels[peakIdx] || '—';

            if (typeof data.patientTotal !== 'undefined') {
                const pEl = document.getElementById('patientTotal');
                if (pEl) pEl.textContent = data.patientTotal;
            }

            const currentChart = document.getElementById('chartSelectorTop') ? document.getElementById('chartSelectorTop').value : 'appointments';
            let primaryData = currentChart === 'patients' ? patientCounts : counts;
            // Make sure primaryData has same length as labels (pad with zeros if necessary)
            if (labels.length && primaryData.length !== labels.length) {
                console && console.warn && console.warn('dentist-dashboard: primaryData length mismatch, padding to labels length');
                const padded = [];
                for (let i = 0; i < labels.length; i++) padded.push(Number(primaryData[i]) || 0);
                primaryData = padded;
            }
            const primaryLabel = currentChart === 'patients' ? 'Patients' : 'Appointments';

            // appointments chart
            const ctx = getCanvasContext('appointmentsChartTop') || getCanvasContext('appointmentsChart');
            if (!ctx) {
                console.warn('appointments canvas not found; skipping chart render');
            } else {
                if (appointmentsChart) {
                    appointmentsChart.data.labels = labels;
                    appointmentsChart.data.datasets[0].data = primaryData;
                    appointmentsChart.update();
                } else {
                    appointmentsChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: primaryLabel,
                                data: primaryData,
                                borderColor: '#8b5cf6',
                                backgroundColor: 'rgba(139,92,246,0.12)',
                                tension: 0.4,
                                fill: true,
                                pointRadius: 3
                            }]
                        },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
                    });
                }
            }

            // status chart
            const sc = getCanvasContext('statusChartTop') || getCanvasContext('statusChart');
            const statusCounts = data.statusCounts || {};
            const labelsStatus = Object.keys(statusCounts);
            const countsStatus = labelsStatus.map(k => statusCounts[k]);
            // build legend
            const legendEl = document.getElementById('statusLegend');
            if (legendEl) {
                legendEl.innerHTML = '';
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
            if (sc) {
                if (statusChart) {
                    statusChart.data.labels = labelsStatus;
                    statusChart.data.datasets[0].data = countsStatus;
                    statusChart.update();
                } else {
                    statusChart = new Chart(sc, { type: 'doughnut', data: { labels: labelsStatus, datasets: [{ data: countsStatus, backgroundColor: ['#c7b8ff','#e9d5ff','#d6bcfa','#c4b5fd','#f3e8ff','#e0c3ff'] }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, cutout: '65%' } });
                }
            }

        } catch (err) {
            console.error('Stats load error', err);
        }
    }

    window.addEventListener('load', function() {
        fetchStats();
        if (autoRefreshTimer) clearInterval(autoRefreshTimer);
        autoRefreshTimer = setInterval(fetchStats, REFRESH_INTERVAL_MS);

        const statsScopeEl = document.getElementById('statsScope');
        if (statsScopeEl) statsScopeEl.addEventListener('change', function(){ fetchStats(); if (autoRefreshTimer) { clearInterval(autoRefreshTimer); autoRefreshTimer = setInterval(fetchStats, REFRESH_INTERVAL_MS); } });
        const chartSelectorTop = document.getElementById('chartSelectorTop');
        if (chartSelectorTop) chartSelectorTop.addEventListener('change', function(){ fetchStats(); if (autoRefreshTimer) { clearInterval(autoRefreshTimer); autoRefreshTimer = setInterval(fetchStats, REFRESH_INTERVAL_MS); } });
    });
})();
