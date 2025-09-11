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
    let lastNextKey = null;
    const STORAGE_KEY = 'staff_simple_history_v1';
    let lastFetchedJson = null;
    // store separate series per metric
    const METRIC_KEYS = { patients: 'patients', appointments: 'appointments', treatments: 'treatments' };
    // currency formatting helper
    function formatCurrency(v){
        try { return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'USD', maximumFractionDigits: 2 }).format(Number(v || 0)); } catch(e) { return (Number(v || 0)).toFixed(2); }
    }
    let triedAdminProxy = false;

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

    function formatNextAppointment(nextObj){
        if (!nextObj) return 'No appointment';
        // backend may return { id, patient_name, datetime }
        if (typeof nextObj === 'string') return nextObj;
        try {
            // parse server datetime reliably (convert 'YYYY-MM-DD HH:mm:ss' to ISO)
            const parseDate = s => {
                if (!s) return null;
                // replace space with T and, if no timezone, treat as local by leaving as-is
                const iso = s.indexOf('T') === -1 ? s.replace(' ', 'T') : s;
                const d = new Date(iso);
                if (isNaN(d.getTime())) {
                    // fallback: try replacing space and appending Z
                    const d2 = new Date(iso + 'Z');
                    return isNaN(d2.getTime()) ? null : d2;
                }
                return d;
            };
            const dt = nextObj.datetime ? parseDate(nextObj.datetime) : null;
            const name = nextObj.patient_name || nextObj.patient || 'Unknown';
            if (!dt) return name;
            const time = dt.toLocaleString();
            return name + ' — ' + time;
        } catch (e) {
            return JSON.stringify(nextObj);
        }
    }

    function updateUIFromTotals(json, value) {
        const setText = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = (v === null || v === undefined) ? '—' : v; };
        setText('patientTotal', value);
        // if revenue present, show formatted totals
        if (document.getElementById('patientTotal') && (metricSelect && metricSelect.value === 'revenue')) {
            // display the total_revenue if provided else the numeric value
            const tot = (json && json.totals && typeof json.totals.total_revenue !== 'undefined') ? json.totals.total_revenue : value;
            setText('patientTotal', formatCurrency(tot));
        }
        // Compute average/peak from available client series when possible to ensure consistency with chart
        try {
            const avgEl = document.getElementById('avgPerDayTop');
            const peakEl = document.getElementById('peakDayTop');
            // prefer server-provided average if present; otherwise estimate from series
            let avgText = (json && typeof json.avgPerDay !== 'undefined' && json.avgPerDay !== null) ? json.avgPerDay : null;
            if ((avgText === null || avgText === undefined) && data && data.length) {
                const est = (data.reduce((a,b)=>a+b,0) / data.length).toFixed(1);
                avgText = isNaN(est) ? '—' : est;
            }
            if (avgEl) { avgEl.textContent = (avgText === null || avgText === undefined) ? '—' : avgText; console.debug && console.debug('staff-simple-chart avg updated', avgText); }

            // compute peak label from labels/data when available
            let peakText = null;
            if (labels && labels.length && data && data.length) {
                const idx = data.indexOf(Math.max(...data));
                peakText = labels[idx] || '—';
            }
            if ((peakText === null || peakText === undefined) && json && typeof json.peakDay !== 'undefined') {
                peakText = json.peakDay;
            }
            if (peakEl) { peakEl.textContent = (peakText === null || peakText === undefined) ? '—' : peakText; console.debug && console.debug('staff-simple-chart peak updated', peakText); }
        } catch(e) { /* ignore UI extras on failure */ }
        // Format next appointment object safely
        const nextText = formatNextAppointment(json.nextAppointment);
        setText('nextAppointmentText', nextText);
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
        // Highlight next appointment area if it's missed (older than grace window)
        try {
            const grace = (window.STAFF_NEXT_APPT_GRACE !== undefined) ? Number(window.STAFF_NEXT_APPT_GRACE) : null;
            const next = json.nextAppointment;
            const nextBadge = document.getElementById('nextAppointmentBadge');
            const nextTextEl = document.getElementById('nextAppointmentText');
            if (!next || !next.datetime) {
                if (nextBadge) { nextBadge.classList.add('hidden'); nextBadge.textContent = 'Refresh'; }
                if (nextTextEl) nextTextEl.textContent = 'No appointment';
            } else if (grace !== null) {
                const apptTime = new Date(next.datetime);
                const now = new Date();
                const diffMinutes = (now - apptTime) / 60000;
                if (diffMinutes > grace) {
                    // missed
                    if (nextBadge) nextBadge.classList.remove('hidden');
                    if (nextBadge) nextBadge.textContent = 'Missed';
                    if (nextTextEl) nextTextEl.classList.add('text-red-600');
                } else {
                    if (nextBadge) nextBadge.classList.remove('hidden');
                    if (nextBadge) nextBadge.textContent = 'Upcoming';
                    if (nextTextEl) nextTextEl.classList.remove('text-red-600');
                }
            }
        } catch (e) { /* ignore UI extras on failure */ }
        // show debug dump if enabled
        try {
            lastFetchedJson = json;
            if (window.STAFF_DEBUG) {
                const dump = document.getElementById('staffDebugDump');
                const wrap = document.getElementById('staffDebugWrap');
                if (dump && wrap) { dump.textContent = JSON.stringify(json, null, 2); dump.classList.remove('hidden'); wrap.classList.remove('hidden'); }
            }
        } catch(e){}
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
            // prefer explicit selector value; if none and server provided STAFF_SELECTED_BRANCH, use that
            const scopeVal = scopeSelect ? scopeSelect.value : null;
            const preferredBranch = (window.STAFF_SELECTED_BRANCH !== undefined && window.STAFF_SELECTED_BRANCH !== null) ? String(window.STAFF_SELECTED_BRANCH) : null;
            if (scopeVal) {
                if (scopeVal === 'all') {
                    url += '?scope=all';
                } else if (scopeVal.indexOf('branch:') === 0) {
                    const branchId = scopeVal.split(':')[1];
                    url += '?branch_id=' + encodeURIComponent(branchId);
                } else if (scopeVal === 'this' || scopeVal === 'mine') {
                    url += '?scope=mine';
                }
            } else if (preferredBranch) {
                // no selector chosen but admin selected a branch: prefer it
                url += '?branch_id=' + encodeURIComponent(preferredBranch);
                }
                // If current user is admin and ADMIN_PREVIEW_STATS is available, proactively use admin proxy
                if (typeof window.CURRENT_USER_TYPE !== 'undefined' && window.CURRENT_USER_TYPE === 'admin' && typeof window.ADMIN_PREVIEW_STATS !== 'undefined') {
                    // derive branch_id from explicit url params or server-provided STAFF_SELECTED_BRANCH
                    const search = new URLSearchParams(url.split('?')[1] || '');
                    const b = search.get('branch_id') || (typeof window.STAFF_SELECTED_BRANCH !== 'undefined' && window.STAFF_SELECTED_BRANCH !== null ? String(window.STAFF_SELECTED_BRANCH) : null);
                    url = window.ADMIN_PREVIEW_STATS + (b ? ('?branch_id=' + encodeURIComponent(b)) : '');
                }
            // cache-bust and avoid browser caching
            let json = null;
            const sep = url.indexOf('?') === -1 ? '?' : '&';
            const urlB = url + sep + '_=' + Date.now();
            console.debug && console.debug('staff-simple-chart fetching', urlB);
            const res = await fetch(urlB, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, cache: 'no-store', credentials: 'same-origin' });
            if (!res.ok) {
                // try to surface JSON error message for 4xx responses
                let body = null;
                try { body = await res.json(); } catch(e) { body = await res.text().catch(()=>null); }
                console.warn('staff-simple-chart non-ok response', res.status, body);

                // If we're an admin and the staff endpoint returned 403, retry using the admin preview proxy once
                try {
                    if (res.status === 403 && !triedAdminProxy && typeof window.CURRENT_USER_TYPE !== 'undefined' && window.CURRENT_USER_TYPE === 'admin' && typeof window.ADMIN_PREVIEW_STATS !== 'undefined') {
                        triedAdminProxy = true;
                        // derive branch_id from the previously-built url (before cache bust)
                        const params = new URLSearchParams(url.split('?')[1] || '');
                        const b = params.get('branch_id') || (window.STAFF_SELECTED_BRANCH ? String(window.STAFF_SELECTED_BRANCH) : null);
                        let adminUrl = window.ADMIN_PREVIEW_STATS;
                        if (b) adminUrl += '?branch_id=' + encodeURIComponent(b);
                        const sep2 = adminUrl.indexOf('?') === -1 ? '?' : '&';
                        const adminUrlB = adminUrl + sep2 + '_=' + Date.now();
                        console.debug && console.debug('staff-simple-chart retrying via admin proxy', adminUrlB);
                        const res2 = await fetch(adminUrlB, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, cache: 'no-store', credentials: 'same-origin' });
                        if (!res2.ok) {
                            let body2 = null;
                            try { body2 = await res2.json(); } catch(e) { body2 = await res2.text().catch(()=>null); }
                            console.warn('staff-simple-chart admin-proxy non-ok response', res2.status, body2);
                            return;
                        }
                        const json2 = await res2.json();
                        // proceed with the rest of the success path by assigning json and falling through
                        console.debug && console.debug('staff-simple-chart admin-proxy response', json2);
                        lastFetchedJson = json2;
                        // assign proxied json into the shared json variable for downstream processing
                        json = json2;
                        // reset triedAdminProxy for next polling cycle
                        triedAdminProxy = false;
                        // continue to normal processing below by not returning here
                    } else {
                        return; // bail without throwing so polling continues
                    }
                } catch (e) {
                    console.warn('staff-simple-chart proxy retry error', e);
                    return;
                }
            }
            if (json === null) {
                json = await res.json();
                console.debug && console.debug('staff-simple-chart response', json);
                // cache last fetched
                lastFetchedJson = json;
            } else {
                console.debug && console.debug('staff-simple-chart proxied response', json);
            }

            // determine next-appointment key so we can detect changes and force UI update
            const nextObj = json.nextAppointment || (json.totals && json.totals.nextAppointment) || null;
            const nextKey = nextObj ? ((nextObj.id || '') + '|' + (nextObj.datetime || '') + '|' + (nextObj.patient_name || '')) : 'none';

            // helper to get last known value for current metric
            const currentMetric = metricSelect ? metricSelect.value : 'patients';
            const lastKnownRaw = (data && data.length) ? data[data.length - 1] : null;
            const lastKnown = (lastKnownRaw !== null && lastKnownRaw !== undefined) ? Number(lastKnownRaw) : null;

            // preferred rich shape: { success: true, totals: { total_patients, total_appointments, total_treatments }, ... }
            if (json && json.success && json.totals) {
                const totals = json.totals;
                let value = 0;
                if (currentMetric === 'patients') value = totals.total_patients ?? 0;
                else if (currentMetric === 'appointments') value = totals.total_appointments ?? 0;
                else if (currentMetric === 'treatments') value = totals.total_treatments ?? 0;

                // Always refresh UI numbers/legend; force update next-appointment if it changed
                updateUIFromTotals(json, value);
                // If server returned richer timeseries (labels/counts/patientCounts), update the chart series too
                try {
                    const serverLabels = Array.isArray(json.labels) ? json.labels.slice(-MAX_POINTS) : null;
                    let serverArr = null;
                    if (currentMetric === 'patients' && Array.isArray(json.patientCounts)) serverArr = json.patientCounts.slice(-MAX_POINTS);
                    else if (currentMetric === 'treatments' && Array.isArray(json.treatmentCounts)) serverArr = json.treatmentCounts.slice(-MAX_POINTS);
                    else if (currentMetric === 'revenue' && Array.isArray(json.revenueTotals)) serverArr = json.revenueTotals.slice(-MAX_POINTS);
                    else if (Array.isArray(json.counts)) serverArr = json.counts.slice(-MAX_POINTS);

                    if (serverArr && serverLabels) {
                        // normalize numeric array
                        const numeric = serverArr.map(v => Number(v || 0));
                        labels = serverLabels;
                        // pad/slice to match labels
                        if (numeric.length < labels.length) {
                            const pad = new Array(labels.length - numeric.length).fill(0);
                            data = pad.concat(numeric);
                        } else {
                            data = numeric.slice(-labels.length);
                        }
                        if (!chart) initChart(); else { chart.data.labels = labels; chart.data.datasets[0].data = data; chart.update(); }
                        computeAndDisplayAvgPeak();
                        saveHistory(currentMetric);
                    }
                } catch(e) { console && console.warn && console.warn('staff-simple-chart timeseries update failed', e); }
                // provide sensible fallbacks for avg/peak when backend omitted them
                try {
                    const avgEl = document.getElementById('avgPerDayTop');
                    const peakEl = document.getElementById('peakDayTop');
                    if (avgEl && (json.avgPerDay === undefined || json.avgPerDay === null)) {
                        // estimate average per day over a week as a fallback
                        const estAvg = ((totals.total_patients || 0) / 7).toFixed(1);
                        avgEl.textContent = isNaN(estAvg) ? '—' : estAvg;
                    }
                    if (peakEl && (json.peakDay === undefined || json.peakDay === null)) {
                        // if we have recent labels/data compute a peak label, otherwise leave as dash
                        if (labels && labels.length && data && data.length) {
                            const idx = data.indexOf(Math.max(...data));
                            peakEl.textContent = labels[idx] || '—';
                        }
                    }
                } catch(e) {}
                if (lastNextKey !== nextKey) {
                    // ensure next appointment UI updates even if totals didn't change
                    lastNextKey = nextKey;
                    updateUIFromTotals(json, value);
                }
                if (lastKnown === null || lastKnown !== Number(value)) {
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
                else if (currentMetric === 'revenue' && Array.isArray(json.revenueTotals)) incomingArr = json.revenueTotals;
                else if (Array.isArray(json.counts)) incomingArr = json.counts; // appointments fallback
                // if we still have no array, abort this branch
                if (!Array.isArray(incomingArr)) {
                    // nothing we can plot from this payload
                } else {
                    // normalize incoming to numbers and trim to MAX_POINTS
                    const incomingRaw = incomingArr.slice(-MAX_POINTS);
                        const incoming = incomingRaw.map(v => Number(v || 0));
                        const incomingLast = incoming.length ? incoming[incoming.length - 1] : null;

                // if last value unchanged, refresh UI only
                if (lastKnown !== null && incomingLast !== null && lastKnown === Number(incomingLast)) {
                    // Replace labels and data from server even if last value matches local sample.
                    labels = json.labels.slice(-MAX_POINTS);
                    data = incoming.slice(-labels.length);
                    if (!chart) initChart(); else { chart.data.labels = labels; chart.data.datasets[0].data = data; chart.update(); }
                    computeAndDisplayAvgPeak();
                    updateUIFromTotals(json, incomingLast);
                    saveHistory(currentMetric);
                    return;
                }

                    // otherwise replace series
                    // ensure we have labels; if not, build last N day labels
                    labels = Array.isArray(json.labels) && json.labels.length ? json.labels.slice(-MAX_POINTS) : (function(){
                        const tmp = []; for (let i=6;i>=0;i--){ const d = new Date(); d.setDate(d.getDate()-i); tmp.push(d.toLocaleDateString(undefined,{weekday:'short', month:'short', day:'numeric'})); } return tmp.slice(-MAX_POINTS);
                    })();
                    // ensure incoming numeric array and parity
                    const numeric = incoming.map(v => Number(v || 0));
                    // pad or slice to match labels length
                    if (numeric.length < labels.length) {
                        const pad = new Array(labels.length - numeric.length).fill(0);
                        data = pad.concat(numeric);
                    } else {
                        data = numeric.slice(-labels.length);
                    }
                    if (!chart) initChart(); else { chart.data.labels = labels; chart.data.datasets[0].data = data; chart.update(); }
                    computeAndDisplayAvgPeak();
                    // Force update next appointment UI when it changes
                    if (lastNextKey !== nextKey) {
                        lastNextKey = nextKey;
                    }
                    // For revenue metric, format total appropriately
                    const lastVal = data.slice(-1)[0];
                    if (currentMetric === 'revenue') updateUIFromTotals(json, lastVal);
                    else updateUIFromTotals(json, lastVal);
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
    if (metricSelect) metricSelect.addEventListener('change', function(){ resetChart(); restoreHistory(this.value); fetchStats(); });
    if (scopeSelect) scopeSelect.addEventListener('change', function(){
        // Clear persisted history entirely for the new scope to avoid stale local samples
        try { localStorage.removeItem(STORAGE_KEY); } catch(e){}
        // also force next-appointment refresh
        lastNextKey = null;
        resetChart(); fetchStats();
    });
    // If server provided a pre-selected branch, update the UI selector to reflect it (if present)
    if (typeof window.STAFF_SELECTED_BRANCH !== 'undefined' && window.STAFF_SELECTED_BRANCH !== null && scopeSelect) {
        // try to select the matching option in the select (defensive: scopeSelect may not be a <select>)
        const optVal = 'branch:' + String(window.STAFF_SELECTED_BRANCH);
        let opt = null;
        try {
            // prefer HTMLSelectElement.options when present; fallback to querySelectorAll
            const opts = scopeSelect.options ? Array.from(scopeSelect.options) : (scopeSelect.querySelectorAll ? Array.from(scopeSelect.querySelectorAll('option')) : []);
            opt = opts.find(o => o.value === optVal);
        } catch (e) {
            // last-resort: iterate NodeList or HTMLCollection defensively
            if (scopeSelect && scopeSelect.querySelectorAll) {
                const nodeList = scopeSelect.querySelectorAll('option');
                for (let i = 0; i < nodeList.length; i++) {
                    if (nodeList[i].value === optVal) { opt = nodeList[i]; break; }
                }
            }
        }
        if (opt) scopeSelect.value = optVal;
    }

    // Make badge clickable to force-refresh
    const badge = document.getElementById('nextAppointmentBadge');
    if (badge) badge.addEventListener('click', function(){
        // trigger an immediate fetch and show debug
        fetchStats();
        if (window.STAFF_DEBUG && lastFetchedJson) {
            const dump = document.getElementById('staffDebugDump'); if (dump) dump.textContent = JSON.stringify(lastFetchedJson, null, 2);
        }
    });

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
    // Clear any stale persisted history for initial load to prefer server canonical data
    try { localStorage.removeItem(STORAGE_KEY); } catch(e){}
    fetchStats();
    // poll more frequently so next-appointment updates arrive faster when new bookings are made
    setInterval(fetchStats, 10 * 1000);
})();
