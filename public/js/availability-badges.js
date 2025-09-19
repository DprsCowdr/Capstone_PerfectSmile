// availability-badges.js
// Small helper: query availability for today for listed dentists and toggle .unavailable-badge
(function(){
  // Lightweight helper to debug badge behavior. Set window.AVAIL_BADGE_DEBUG = true to enable verbose logs.
  const debug = !!(typeof window !== 'undefined' && window.AVAIL_BADGE_DEBUG);

  async function fetchAvailabilityForDateRange(start, end){
    const base = (typeof window !== 'undefined' && window.baseUrl) ? window.baseUrl : '';
    const tryUrls = [];
    if (base) tryUrls.push(base + '/calendar/availability-events?start=' + encodeURIComponent(start) + '&end=' + encodeURIComponent(end));
    tryUrls.push('/calendar/availability-events?start=' + encodeURIComponent(start) + '&end=' + encodeURIComponent(end));

    let lastErr = null;
    for (const u of tryUrls){
      try {
        if (debug) console && console.debug && console.debug('[avail-badges] fetching', u);
        const r = await fetch(u, {credentials:'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' }});
        if (!r.ok) { lastErr = new Error('HTTP ' + r.status); continue; }
        const j = await r.json();
        return j && j.events ? j.events : [];
      } catch (e){ lastErr = e; }
    }
    if (debug) console && console.warn && console.warn('[avail-badges] fetch failed for all urls', lastErr);
    throw lastErr || new Error('Failed to fetch availability');
  }

  // Manila-local parser: similar behavior to getPHDate() used by calendar scripts
  function parsePHDate(s){
    if (!s) return null;
    if (s instanceof Date) return new Date(s.getTime());
    // If ISO with timezone, parse directly
    if (/[Tt].*(?:[Zz]|[+\-]\d{2}:\d{2})$/.test(s)) {
      const d = new Date(s);
      return isNaN(d.getTime()) ? null : d;
    }
    // Try YYYY-MM-DD or YYYY-MM-DD HH:mm:ss
    const m = String(s).trim().match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}):?(\d{2})?)?$/);
    if (m) {
      const y = parseInt(m[1],10), mo = parseInt(m[2],10) - 1, d = parseInt(m[3],10);
      const hh = parseInt(m[4] || '0',10), mm = parseInt(m[5] || '0',10), ss = parseInt(m[6] || '0',10);
      const utcMs = Date.UTC(y, mo, d, hh, mm, ss) - (8 * 60 * 60 * 1000); // Manila -> UTC
      return new Date(utcMs);
    }
    const fallback = new Date(s);
    return isNaN(fallback.getTime()) ? null : fallback;
  }

  async function markBadges(){
    try{
      const rows = Array.from(document.querySelectorAll('[data-dentist-id], [data-dentist]'));
      if (!rows.length){ if (debug) console && console.debug && console.debug('[avail-badges] no dentist rows found'); return; }

      // We'll fetch availability per distinct date used by rows to avoid cross-month bleed.
      // Collect target dates from rows (data-date or today's date fallback)
      const today = new Date();
      const defaultDateStr = today.toISOString().slice(0,10);
      const rowDates = new Set();
      rows.forEach(r => {
        const explicit = r.getAttribute('data-date') || r.getAttribute('data-day') || (r.dataset && (r.dataset.date || r.dataset.day));
        rowDates.add(explicit ? String(explicit).trim() : defaultDateStr);
      });

      // Fetch availability for each date and merge
      let events = [];
      for (const dateStr of rowDates) {
        try {
          const dayEvents = await fetchAvailabilityForDateRange(dateStr, dateStr);
          events = events.concat(dayEvents || []);
        } catch (err) {
          if (debug) console && console.warn && console.warn('[avail-badges] fetch error', err);
        }
      }

      if (debug) console && console.debug && console.debug('[avail-badges] found events', events.length, events);

      rows.forEach(r => {
        const id = r.getAttribute('data-dentist-id') || r.getAttribute('data-dentist') || (r.dataset && (r.dataset.dentistId || r.dataset.dentist));
        if (!id) return;
        // Determine the target date for this row (avoid using outer 's')
        const targetDate = r.getAttribute('data-date') || r.getAttribute('data-day') || (r.dataset && (r.dataset.date || r.dataset.day)) || defaultDateStr;

        // Only mark as unavailable if there exists an event for this dentist that intersects the date
        const has = events.some(ev => {
          const evUser = ev.user_id || ev.userId || ev.user;
          if (!evUser || String(evUser) !== String(id)) return false;
          // Parse start/end into Date objects using Manila-aware parser
          const evStart = parsePHDate(ev.start);
          const evEnd = parsePHDate(ev.end);
          if (!evStart || isNaN(evStart.getTime()) || !evEnd || isNaN(evEnd.getTime())) return false;
          const dayStart = parsePHDate(String(targetDate) + 'T00:00:00');
          const dayEnd = parsePHDate(String(targetDate) + 'T23:59:59');
          const intersects = !(evEnd < dayStart || evStart > dayEnd);
          if (debug) console && console.debug && console.debug('[avail-badges] check', id, s, 'ev', ev.id || ev, ev.start, ev.end, 'intersects=', intersects);
          return intersects;
        });

        // Friendly badge UI
        let badge = r.querySelector('.availability-badge');
        if (has) {
          // compute friendly label using first matching event time window
          const firstEv = events.find(ev => String(ev.user_id) === String(id) && (parsePHDate(ev.start) <= parsePHDate(String(targetDate) + 'T23:59:59') && parsePHDate(ev.end) >= parsePHDate(String(targetDate) + 'T00:00:00')) );
          let label = 'Unavailable';
          let cls = 'bg-red-100 text-red-700';
          if (firstEv) {
            // New behaviour: backend no longer expands recurring rules. We'll treat
            // explicit "working_hours" events as availability (green), and any
            // other event types as blocking/time-off (orange).
            try {
              if (String(firstEv.type) === 'working_hours') {
                const st = parsePHDate(firstEv.start);
                const en = parsePHDate(firstEv.end);
                if (st && en) {
                  label = `✅ ${('' + st.getHours()).padStart(2,'0')}:${('' + st.getMinutes()).padStart(2,'0')} - ${('' + en.getHours()).padStart(2,'0')}:${('' + en.getMinutes()).padStart(2,'0')}`;
                } else {
                  label = 'Available';
                }
                cls = 'bg-green-100 text-green-700';
              } else {
                // explicit block or other types
                label = `🔒 ${new Date(firstEv.start).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})} - ${new Date(firstEv.end).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}`;
                cls = 'bg-orange-100 text-orange-700';
              }
            } catch (err) {
              // Fallback labeling on any parse error
              label = 'Unavailable';
              cls = 'bg-red-100 text-red-700';
            }
          }

          if (!badge){
            badge = document.createElement('span');
            // larger, high-contrast and touch-friendly style for older/non-technical users
            badge.className = 'availability-badge ml-2 text-sm px-3 py-1 rounded-md';
            const preferred = r.querySelector('td') || r.querySelector('.name') || r.querySelector('div') || r;
            try { preferred && preferred.appendChild(badge); } catch(e){ r.appendChild(badge); }
          }
          // Set label and classes (plain-language, no emoji)
          badge.textContent = label;
          badge.className = 'availability-badge ml-2 text-sm px-3 py-1 rounded-md ' + cls;
          // Accessibility: set title/aria-label so screen-readers can speak it
          badge.setAttribute('title', label);
          badge.setAttribute('aria-label', label);
          if (debug) console && console.debug && console.debug('[avail-badges] set badge for', id, label, r);
        } else {
          if (badge){ badge.remove(); if (debug) console && console.debug && console.debug('[avail-badges] removed badge for', id); }
        }
      });
    }catch(e){ console && console.warn && console.warn('availability-badges error', e); }
  }

  // Observe for DOM changes to handle late-loaded tables (DataTables, async loads)
  let mo = null;
  function ensureObserver(){
    if (mo) return;
    try {
      mo = new MutationObserver((mutations)=>{
        // If new dentist rows/options were added, re-run markBadges
        let found = false;
        for (const m of mutations){
          if (m.addedNodes && m.addedNodes.length){
            for (const n of m.addedNodes){
              if (n.querySelector && (n.querySelector('[data-dentist-id], [data-dentist]') || n.matches && (n.matches('[data-dentist-id], [data-dentist]')))) { found = true; break; }
            }
          }
          if (found) break;
        }
        if (found) {
          if (debug) console && console.debug && console.debug('[avail-badges] DOM mutation detected, re-running markBadges');
          markBadges();
        }
      });

      mo.observe(document.body, { childList: true, subtree: true });
    } catch(e){ if (debug) console && console.warn && console.warn('[avail-badges] observer failed', e); }
  }

  // Run on DOMContentLoaded, load, and after availability changes
  try{
    document.addEventListener('DOMContentLoaded', function(){ markBadges(); ensureObserver(); });
    window.addEventListener('load', function(){ markBadges(); ensureObserver(); });
    window.addEventListener('availability:changed', function(){ markBadges(); });
  } catch(e){}
})();
