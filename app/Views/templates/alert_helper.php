<style>
/* Inline invoice alert styles + fade animation (shared) */
.invoice-alert-container { position: fixed; top: 1.5rem; right: 1.5rem; z-index: 60; max-width: 20rem; }
.invoice-alert { margin-bottom: .5rem; padding: .75rem 1rem; border-radius: .5rem; box-shadow: 0 6px 18px rgba(15,23,42,0.08); display:flex; align-items:flex-start; gap:.5rem; opacity:0; transform: translateY(-8px); transition: opacity .28s ease, transform .28s ease; }
.invoice-alert.show { opacity:1; transform: translateY(0); }
.invoice-alert.hide { opacity:0; transform: translateY(-8px); }
.invoice-alert .invoice-msg { flex:1; font-size: .875rem; }
.invoice-alert .invoice-close { background: transparent; border: none; font-size: 1.25rem; line-height: 1; cursor: pointer; opacity: .85; }
.invoice-alert.info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a; }
.invoice-alert.success { background: #ecfdf5; border: 1px solid #bbf7d0; color: #065f46; }
.invoice-alert.warning { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
.invoice-alert.error { background: #fff1f2; border: 1px solid #fecaca; color: #9f1239; }
</style>

<script>
// Shared inline alert helper with fade-in/out animation
if (typeof window.showInvoiceAlert !== 'function') {
    window.showInvoiceAlert = function(message, type = 'info', timeout = 5000) {
        var containerId = 'invoice-alert-container';
        var container = document.getElementById(containerId);

        if (!container) {
            container = document.createElement('div');
            container.id = containerId;
            container.className = 'invoice-alert-container';
            document.body.appendChild(container);
        }

        var el = document.createElement('div');
        el.className = 'invoice-alert ' + (type || 'info');

        var msg = document.createElement('div');
        msg.className = 'invoice-msg';
        msg.textContent = String(message);

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'invoice-close';
        btn.setAttribute('aria-label', 'Dismiss');
        btn.innerHTML = '&times;';

        el.appendChild(msg);
        el.appendChild(btn);

        var removeEl = function() {
            el.classList.remove('show');
            el.classList.add('hide');
            el.addEventListener('transitionend', function te() {
                if (el.parentNode) el.parentNode.removeChild(el);
                el.removeEventListener('transitionend', te);
            });
        };

        btn.addEventListener('click', function () { removeEl(); });

        container.appendChild(el);
        requestAnimationFrame(function() { el.classList.add('show'); });

        if (timeout > 0) {
            setTimeout(function () { removeEl(); }, timeout);
        }
    };
}
</script>
