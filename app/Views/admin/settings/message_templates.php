<?php /** @var array $templates */ ?>
<div class="p-4 border rounded bg-white mt-4">
    <h2 class="text-xl font-semibold mb-3">Appointment Message Templates</h2>

    <!-- Include reusable server-side modal partial (lavender-themed) -->
    <?= view('partials/message_modal') ?>
    <script>
        // Expose templates to client JS for previewing in this admin page
        window.MESSAGE_TEMPLATES = <?= json_encode($templates ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>;
        // Minimal preview replacer used only in admin UI
        function renderTemplatePreview(key) {
            try {
                const tpl = (window.MESSAGE_TEMPLATES && window.MESSAGE_TEMPLATES[key]) ? window.MESSAGE_TEMPLATES[key] : '';
                if (!tpl) return 'Template not available';
                const now = new Date();
                const when = now.toLocaleString(undefined, { month: 'long', day: 'numeric', year: 'numeric' }) + ' at ' + now.toTimeString().substring(0,5);
                const replacements = {
                    '{when}': when,
                    '{grace}': 15,
                    '{adjusted_time}': ''
                };
                let out = tpl;
                Object.keys(replacements).forEach(k => { out = out.split(k).join(replacements[k]); });
                return out;
            } catch(e) { return 'Preview error'; }
        }

        function previewTemplate(key) {
            const msg = renderTemplatePreview(key);
            if (window.showMessageModal) return window.showMessageModal(msg, 'Preview');
            alert(msg);
        }
    </script>

    <div id="mtMessagesContainer">
        <!-- Loading state -->
        <div id="mtLoading" class="text-gray-500">Loading templates...</div>
    </div>

    <div class="mt-4">
        <button id="mtSaveBtn" class="px-4 py-2 bg-blue-600 text-white rounded">Save Templates</button>
    </div>

    <script>
        // CSRF token meta (CodeIgniter's default name)
        const csrfName = '<?= csrf_token() ?>';
        const csrfHash = '<?= csrf_hash() ?>';

        // Fetch templates via AJAX and render editable fields
        async function loadMessageTemplates() {
            const container = document.getElementById('mtMessagesContainer');
            const loading = document.getElementById('mtLoading');
            try {
                const res = await fetch('<?= site_url('admin/message-templates/ajax') ?>', { credentials: 'same-origin' });
                const data = await res.json();
                if (data.status !== 'ok') throw new Error('Failed to fetch');
                const templates = data.templates || {};
                loading.style.display = 'none';
                const list = document.createElement('div');
                list.className = 'space-y-6';
                Object.keys(templates).forEach(key => {
                    const block = document.createElement('div');
                    block.innerHTML = `
                        <label class="block font-semibold mb-1">${key.replace(/_/g,' ')}</label>
                        <textarea data-key="${key}" class="w-full border rounded p-2" rows="3">${templates[key] || ''}</textarea>
                        <p class="text-sm text-gray-600">Placeholders: {when}, {grace}, {adjusted_time}</p>
                        <div class="mt-1"><button class="mt-preview-btn px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">Preview</button></div>
                    `;
                    list.appendChild(block);
                });
                container.appendChild(list);

                // wire preview buttons
                container.querySelectorAll('.mt-preview-btn').forEach(btn => {
                    btn.addEventListener('click', function(e){
                        const ta = this.closest('div').querySelector('textarea');
                        const key = ta.getAttribute('data-key');
                        const message = ta.value;
                        // update window.MESSAGE_TEMPLATES for backward compatibility preview
                        window.MESSAGE_TEMPLATES = window.MESSAGE_TEMPLATES || {};
                        window.MESSAGE_TEMPLATES[key] = message;
                        if (typeof previewTemplate === 'function') return previewTemplate(key);
                        if (window.__fallbackShowToast) return window.__fallbackShowToast(message, 'info', 8000);
                        alert(message);
                    });
                });
            } catch (e) {
                loading.textContent = 'Failed to load templates.';
                console.error(e);
            }
        }

        // Save templates via AJAX
        async function saveMessageTemplates() {
            const container = document.getElementById('mtMessagesContainer');
            const textareas = container.querySelectorAll('textarea[data-key]');
            const payload = {};
            textareas.forEach(ta => payload[ta.getAttribute('data-key')] = ta.value);

            // Include CSRF token for CodeIgniter
            const formData = new FormData();
            Object.keys(payload).forEach(k => formData.append(k, payload[k]));
            formData.append(csrfName, csrfHash);

            const btn = document.getElementById('mtSaveBtn');
            btn.disabled = true; btn.textContent = 'Saving...';
            try {
                const res = await fetch('<?= site_url('admin/message-templates/save') ?>', {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                });
                const data = await res.json();
                if (data.status && data.status === 'ok') {
                    if (window.showMessageModal) window.showMessageModal('Templates saved', 'Saved', 'success');
                } else {
                    const msg = (data && data.message) ? data.message : 'Save failed';
                    if (window.showMessageModal) window.showMessageModal(msg, 'Error', 'error');
                }
            } catch (e) {
                console.error(e);
                if (window.showMessageModal) window.showMessageModal('Save failed (network)', 'Error', 'error');
            } finally {
                btn.disabled = false; btn.textContent = 'Save Templates';
            }
        }

        document.addEventListener('DOMContentLoaded', function(){
            loadMessageTemplates();
            document.getElementById('mtSaveBtn').addEventListener('click', saveMessageTemplates);
        });
    </script>
</div>