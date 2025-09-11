<style>
/* Simple prompt modal helper */
#psm-prompt-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.45); display: none; align-items: center; justify-content: center; z-index: 70; }
#psm-prompt { background: #fff; border-radius: 8px; padding: 16px; width: 100%; max-width: 480px; box-shadow: 0 10px 30px rgba(2,6,23,0.15); }
#psm-prompt .psm-title { font-weight: 600; margin-bottom: 8px; }
#psm-prompt .psm-input { width: 100%; padding: 8px 10px; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 10px; }
#psm-prompt .psm-actions { display:flex; gap:8px; justify-content: flex-end; }
#psm-prompt .psm-btn { padding: 8px 12px; border-radius: 6px; cursor:pointer; border: none; }
#psm-prompt .psm-btn.cancel { background: #f3f4f6; color:#111827 }
#psm-prompt .psm-btn.ok { background: #2563eb; color: white }
</style>

<div id="psm-prompt-overlay" role="dialog" aria-modal="true" aria-hidden="true">
    <div id="psm-prompt">
        <div class="psm-title" id="psm-prompt-title"></div>
        <input id="psm-prompt-input" class="psm-input" type="text" />
        <div class="psm-actions">
            <button id="psm-cancel" class="psm-btn cancel">Cancel</button>
            <button id="psm-ok" class="psm-btn ok">OK</button>
        </div>
    </div>
</div>

<script>
// Promise-based prompt modal
if (typeof window.showPrompt !== 'function') {
    window.showPrompt = function(message, placeholder = '', defaultValue = '') {
        return new Promise((resolve) => {
            var overlay = document.getElementById('psm-prompt-overlay');
            var title = document.getElementById('psm-prompt-title');
            var input = document.getElementById('psm-prompt-input');
            var btnOk = document.getElementById('psm-ok');
            var btnCancel = document.getElementById('psm-cancel');

            title.textContent = message || '';
            input.value = defaultValue || '';
            input.placeholder = placeholder || '';

            function cleanup() {
                overlay.style.display = 'none';
                overlay.setAttribute('aria-hidden', 'true');
                btnOk.removeEventListener('click', onOk);
                btnCancel.removeEventListener('click', onCancel);
                input.removeEventListener('keydown', onKey);
            }

            function onOk() {
                var val = input.value;
                cleanup();
                resolve(val);
            }

            function onCancel() {
                cleanup();
                resolve(null);
            }

            function onKey(e) {
                if (e.key === 'Enter') onOk();
                if (e.key === 'Escape') onCancel();
            }

            btnOk.addEventListener('click', onOk);
            btnCancel.addEventListener('click', onCancel);
            input.addEventListener('keydown', onKey);

            overlay.style.display = 'flex';
            overlay.setAttribute('aria-hidden', 'false');
            setTimeout(() => input.focus(), 50);
        });
    };
}
</script>
