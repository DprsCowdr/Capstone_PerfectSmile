// calendar-dentist.js - dentist-specific calendar handlers (lightweight shell)
(function(){
  window.calendarDentist = window.calendarDentist || {};
  const baseUrl = window.baseUrl || '';

  function getCsrfToken(){
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : null;
  }

  async function postForm(url, data){
    const headers = {'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8', 'X-Requested-With':'XMLHttpRequest'};
    const csrf = getCsrfToken(); if(csrf) headers['X-CSRF-TOKEN'] = csrf;
    const body = new URLSearchParams();
    Object.keys(data || {}).forEach(k => { if (data[k] !== undefined && data[k] !== null) body.append(k, data[k]); });
    const res = await fetch((window.baseUrl || '') + url, { method: 'POST', headers, body: body.toString(), credentials: 'same-origin' });
    const text = await res.text(); try { return JSON.parse(text); } catch(e) { return text; }
  }

  function initDentistHandlers(){
    console.debug('[calendar-dentist] init');
    // Placeholder: dentist-specific behaviors (personal schedule, availability toggles) go here
  }

  window.calendarDentist = { init: initDentistHandlers };
  if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', window.calendarDentist.init);
  else window.calendarDentist.init();
})();
