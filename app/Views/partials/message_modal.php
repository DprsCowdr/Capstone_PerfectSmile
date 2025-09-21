<?php // Reusable message modal partial - included server-side so markup is always present in the page ?>
<div id="globalMessageModalRoot" class="hidden fixed inset-0 z-[12000] flex items-center justify-center" aria-hidden="true">
  <div class="modal-overlay absolute inset-0 bg-black/30 backdrop-blur-sm" data-role="overlay"></div>

  <div class="modal-stack w-full max-w-3xl mx-4">
  <!-- single panel used to render the active stack item -->
  <div class="modal-panel bg-white rounded-lg shadow-xl border-l-4 border-purple-400 overflow-hidden transform transition duration-200 opacity-0 translate-y-2" role="dialog" aria-modal="true" aria-labelledby="globalModalTitle" aria-describedby="globalModalDesc" tabindex="-1">
      <div class="p-4">
        <div class="flex items-start justify-between gap-4">
          <div class="flex-1">
            <h3 id="globalModalTitle" class="text-lg font-semibold text-purple-700">Title</h3>
            <div id="globalModalDesc" class="mt-2 text-sm text-slate-700">Message</div>
          </div>
          <button type="button" class="modal-close text-slate-500 hover:text-slate-800 ml-4" aria-label="Close">&times;</button>
        </div>
        <div id="globalModalActions" class="mt-4 text-right">
          <button type="button" class="modal-ok px-4 py-2 bg-purple-600 text-white rounded">OK</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- The partial provides a DOM node the JS can use: window._modalRoot -->
<script>
  // Expose modal root for script usage (script will be placed where partial is included)
  (function(){
    try {
      // Avoid double-initialization
      if (window._modalInited) return;
      window._modalInited = true;
      window._modalRoot = document.getElementById('globalMessageModalRoot');

      // modal stack and helpers
      window._modalStack = window._modalStack || [];

      function renderTop() {
        const root = window._modalRoot;
        if (!root) return;
        const panel = root.querySelector('.modal-panel');
        const titleEl = root.querySelector('#globalModalTitle');
        const descEl = root.querySelector('#globalModalDesc');
        const actionsEl = root.querySelector('#globalModalActions');

        const top = window._modalStack.length ? window._modalStack[window._modalStack.length-1] : null;
        if (!top) {
          // When hiding the modal, ensure no focused element remains inside the modal root
          try {
            if (document.activeElement && root.contains(document.activeElement)) {
              // Try to restore focus to previously focused element if available
              const last = window._lastModalFocus && window._lastModalFocus instanceof HTMLElement ? window._lastModalFocus : null;
              if (last && typeof last.focus === 'function') {
                last.focus();
              } else {
                // fallback: blur the active element and move focus to body
                try { document.activeElement.blur(); } catch(e){}
                try { document.body.focus(); } catch(e){}
              }
            }
          } catch(e){}

          // Use inert to hide and prevent focus; remove aria-hidden to avoid hiding a focused descendant
          try {
            root.inert = true;
          } catch(e) {
            // fallback for browsers without inert support: add aria-hidden
            root.setAttribute('aria-hidden', 'true');
          }
          root.classList.add('hidden');
          panel.classList.remove('opacity-100');
          panel.classList.add('opacity-0','translate-y-2');
          return;
        }

  // Showing modal: remove inert so assistive tech can access it
  try { root.inert = false; } catch(e) { root.removeAttribute('aria-hidden'); }
        root.classList.remove('hidden');
        titleEl.textContent = top.title || 'Message';
        // allow HTML content (string or DOM node) when provided
        if (top.html) {
          // clear existing
          descEl.innerHTML = '';
          if (typeof top.html === 'string') {
            descEl.innerHTML = top.html;
          } else if (top.html instanceof Node) {
            descEl.appendChild(top.html);
          } else {
            // fallback to message
            descEl.textContent = top.message || '';
          }
        } else {
          // plain text fallback
          descEl.textContent = top.message || '';
        }

        // theme classes
        panel.classList.remove('border-green-500','border-red-500','border-yellow-400','border-blue-500');
        panel.classList.add('border-l-4','border-purple-400');
        titleEl.classList.remove('text-green-700','text-red-700','text-yellow-700','text-blue-700');
        titleEl.classList.add('text-purple-700');

        // Populate action buttons unless caller asked to suppress actions
        actionsEl.innerHTML = '';
        if (!top.suppressActions) {
          if (top.type === 'confirm') {
          const yes = document.createElement('button');
          yes.className = 'px-4 py-2 bg-purple-600 text-white rounded mr-2';
          yes.textContent = top.yesText || 'Yes';
          yes.onclick = () => { try { top.onYes && top.onYes(); } catch(e){}; popModal(); };
          const no = document.createElement('button');
          no.className = 'px-4 py-2 bg-gray-200 text-gray-700 rounded';
          no.textContent = top.noText || 'No';
          no.onclick = () => { try { top.onNo && top.onNo(); } catch(e){}; popModal(); };
          actionsEl.appendChild(no);
          actionsEl.appendChild(yes);
          } else {
          const ok = document.createElement('button');
          ok.className = 'modal-ok px-4 py-2 bg-purple-600 text-white rounded';
          ok.textContent = top.okText || 'OK';
          ok.onclick = () => { try { top.onOk && top.onOk(); } catch(e){}; popModal(); };
          actionsEl.appendChild(ok);
          }
        }

        panel.classList.remove('opacity-0','translate-y-2');
        panel.classList.add('opacity-100');
        panel.style.transition = 'opacity 220ms ease, transform 220ms ease';
        panel.style.transform = 'translateY(0)';

        // remember what element had focus before opening the modal so we can restore later
        try { window._lastModalFocus = document.activeElement instanceof HTMLElement ? document.activeElement : null; } catch(e) { window._lastModalFocus = null; }
        // move focus into the modal for keyboard/AT users
        try {
          const closeBtn = panel.querySelector('.modal-close');
          if (closeBtn && typeof closeBtn.focus === 'function') closeBtn.focus();
          else panel.focus();
        } catch(e){}
      }

      function pushModal(opts) { 
        try { if (console && console.debug) console.debug('[modal] push', opts); } catch(e){}
        // record what had focus before opening
        try { opts = opts || {}; opts._prevActive = document.activeElement instanceof HTMLElement ? document.activeElement : null; } catch(e) { opts = opts || {}; opts._prevActive = null; }
        window._modalStack.push(opts); renderTop(); 
      }
      function popModal() { 
        try { if (console && console.debug) console.debug('[modal] pop'); } catch(e){}
        const popped = window._modalStack.pop();
        renderTop(); 
        // After popping, try to restore focus to previous element (if any)
        try {
          const prev = popped && popped._prevActive && popped._prevActive instanceof HTMLElement ? popped._prevActive : (window._lastModalFocus && window._lastModalFocus instanceof HTMLElement ? window._lastModalFocus : null);
          if (prev && typeof prev.focus === 'function') prev.focus();
          else {
            // make body briefly focusable as a fallback
            const body = document.body;
            const prevTab = body.getAttribute('tabindex');
            body.setAttribute('tabindex', '-1');
            try { body.focus(); } catch(e){}
            if (prevTab === null) body.removeAttribute('tabindex'); else body.setAttribute('tabindex', prevTab);
          }
        } catch(e){}
      }

      // expose globals
      window.pushModal = window.pushModal || pushModal;
      window.popModal = window.popModal || popModal;
      window.showMessageModal = window.showMessageModal || function(message, title = 'Message', type = 'info') { pushModal({ message, title, type }); };
      window.showConfirmModal = window.showConfirmModal || function(message, title = 'Confirm', onYes, onNo, yesText='Yes', noText='No') { pushModal({ type: 'confirm', message, title, onYes, onNo, yesText, noText }); };

      // wire overlay and close button
      document.addEventListener('click', function(e){
        const root = window._modalRoot; if (!root) return;
        if (e.target.matches('.modal-close') || e.target.matches('.modal-overlay')) { popModal(); }
      });
      document.addEventListener('keydown', function(e){ if (e.key === 'Escape') popModal(); });

      // initial render
      renderTop();
    } catch(e) { console.error('modal partial init failed', e); }
  })();
</script>