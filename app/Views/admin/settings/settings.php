<?php
$title = 'Settings - Perfect Smile';
// Load page header (CSS/scripts) and admin sidebar/topbar to match site layout
echo view('templates/header', ['title' => $title]);
echo view('templates/sidebar', ['user' => $user ?? session('user') ?? []]);
?>

<div class="main-content" data-sidebar-offset>
    <?php echo view('templates/topbar', ['user' => $user ?? session('user') ?? []]); ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Enhanced Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">System Settings</h1>
                <p class="text-lg text-gray-600">Configure system preferences and notification templates</p>
            </div>
            <div class="flex items-center space-x-2 text-sm text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>Last updated: <?= date('M j, Y') ?></span>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300">
                <?php
                // Load the appointment message templates
                $templates = (function(){
                    $cfg = config('AppointmentMessages');
                    return $cfg->templates ?? [];
                })();
                ?>
                
                <!-- Header Section -->
                <div class="p-6 border-b bg-gradient-to-r from-blue-50 to-indigo-50">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Appointment Configuration</h2>
                    <p class="text-gray-600">Manage message templates and grace period settings for your appointment system</p>
                </div>

                <!-- Enhanced Flash Messages -->
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="bg-green-50 border-l-4 border-green-400 p-4 m-6 rounded-lg flex items-center animate-pulse">
                        <svg class="w-5 h-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-green-700"><?= esc(session()->getFlashdata('success')) ?></span>
                    </div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 m-6 rounded-lg flex items-center animate-pulse">
                        <svg class="w-5 h-5 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-red-700"><?= esc(session()->getFlashdata('error')) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Enhanced Tab Navigation -->
                <div class="px-6">
                    <nav class="flex space-x-1 bg-gray-100 p-1 rounded-xl" aria-label="Tabs">
                        <button data-tab="templates" class="tab-btn flex-1 px-6 py-3 rounded-lg font-semibold text-sm flex items-center justify-center transition-all duration-200 bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-md transform -translate-y-0.5">
                            <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                </svg>
                            </div>
                            Message Templates
                        </button>
                        <button data-tab="grace" class="tab-btn flex-1 px-6 py-3 rounded-lg font-semibold text-sm flex items-center justify-center transition-all duration-200 text-gray-600 hover:bg-gray-200 hover:text-gray-800 hover:transform hover:-translate-y-0.5">
                            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            Grace Period Settings
                        </button>
                    </nav>
                </div>

                <!-- Templates Tab -->
                <div id="tab-templates" class="tab-panel p-6 transition-opacity duration-300 opacity-100">
                    <form method="post" action="<?= site_url('admin/message-templates/save') ?>">
                        <?= csrf_field() ?>
                        
                        <!-- Removed per-template cards: use single editable preview bound to dropdown -->
                        <?php foreach ($templates as $key => $val): ?>
                            <input type="hidden" name="<?= esc($key) ?>" value="<?= esc($val) ?>" class="template-hidden-input" data-key="<?= esc($key) ?>">
                        <?php endforeach; ?>

                        <!-- Dropdown-driven preview/editor -->
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 mb-6 border border-gray-200">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Live Preview
                                </h3>

                                <div class="flex items-center space-x-3">
                                    <select id="templateDropdown" class="border-2 border-gray-200 rounded-lg px-4 py-2 text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200">
                                        <option value="">-- Select template --</option>
                                        <?php foreach ($templates as $k => $v): ?>
                                            <option value="<?= esc($k) ?>"><?= ucfirst(str_replace('_',' ',esc($k))) ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                    <button type="button" id="editToggle" class="px-3 py-2 bg-blue-600 text-white rounded text-sm">Edit</button>
                                </div>
                            </div>

                            <div class="bg-white border-2 border-dashed border-gray-300 rounded-lg p-6 min-h-20 hover:border-purple-400 transition-all duration-200">
                                <div id="previewContent" class="text-gray-800 w-full">Preview will appear here when you select a template...</div>
                            </div>

                            <div class="mt-4 bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm text-amber-800">
                                <strong>Available placeholders:</strong>
                                <ul class="list-disc ml-5 mt-2">
                                    <li><code>{when}</code> — formatted appointment date and time (e.g. "on September 20, 2025 at 08:00").</li>
                                    <li><code>{grace}</code> — grace period minutes (numeric).</li>
                                    <li><code>{appointment_length}</code> — human-readable appointment length including grace (e.g. "Your appointment is 4h20m including grace."). Optional but recommended for patient templates.</li>
                                    <li><code>{adjusted_time}</code> — used in adjusted_note templates to show new time when FCFS moved the appointment.</li>
                                </ul>
                            </div>

                            <textarea id="templateEditor" class="mt-4 hidden w-full border-2 border-gray-200 rounded-lg p-3" rows="6" placeholder="Edit selected template..."></textarea>
                        </div>

                        <!-- Save Button -->
                        <div class="sticky bottom-6 z-10">
                            <button type="submit" class="w-full sm:w-auto bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-8 py-4 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Save Templates
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Grace Period Tab -->
                <div id="tab-grace" class="tab-panel p-6 hidden transition-opacity duration-300 opacity-0">
                    <form method="post" action="<?= site_url('admin/grace-periods/save') ?>">
                        <?= csrf_field() ?>
                        
                        <div class="space-y-8">
                            <!-- Default Grace Period -->
                            <div class="bg-white border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-all duration-200">
                                <div class="flex items-center mb-6">
                                    <div class="w-12 h-12 bg-gradient-to-br from-orange-100 to-orange-200 rounded-lg flex items-center justify-center mr-4">
                                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-semibold text-gray-900">Default Grace Period</h3>
                                        <p class="text-sm text-gray-600">Applies to all appointments unless overridden by service-specific settings</p>
                                    </div>
                                </div>
                                
                                <?php
                                $gracePath = WRITEPATH . 'grace_periods.json';
                                $graceDefault = 15;
                                $graceData = [];
                                if (is_file($gracePath)) {
                                    $g = @json_decode(file_get_contents($gracePath), true);
                                    if (is_array($g)) $graceData = $g;
                                }
                                $defaultGrace = $graceData['default'] ?? $graceDefault;
                                ?>
                                
                                <div class="flex items-center space-x-4 bg-gray-50 rounded-lg p-4">
                                    <input type="number" name="default_grace" value="<?= esc($defaultGrace) ?>" 
                                           class="border-2 border-gray-200 rounded-lg px-4 py-3 w-24 text-center font-semibold text-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200" 
                                           min="0" max="120" />
                                    <span class="text-gray-700 font-medium text-lg">minutes</span>
                                    <div class="flex-1 text-right">
                                        <span class="bg-orange-100 text-orange-800 px-3 py-1 rounded-full text-sm font-medium">Global Default</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Service-Based Grace Periods removed: simplified to Default Grace only -->
                        </div>
                        
                        <!-- Save Button -->
                        <div class="sticky bottom-6 z-10">
                            <button type="submit" class="w-full sm:w-auto bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-8 py-4 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Save Grace Periods
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Enhanced tab switching with smooth transitions
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function(){
        // Remove active state from all buttons
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('bg-gradient-to-r', 'from-blue-500', 'to-blue-600', 'text-white', 'shadow-md', 'transform', '-translate-y-0.5');
            b.classList.add('text-gray-600', 'hover:bg-gray-200', 'hover:text-gray-800', 'hover:transform', 'hover:-translate-y-0.5');
        });
        
        // Hide all panels
        document.querySelectorAll('.tab-panel').forEach(p => {
            p.classList.add('hidden', 'opacity-0');
            p.classList.remove('opacity-100');
        });
        
        // Add active state to clicked button
        this.classList.remove('text-gray-600', 'hover:bg-gray-200', 'hover:text-gray-800', 'hover:transform', 'hover:-translate-y-0.5');
        this.classList.add('bg-gradient-to-r', 'from-blue-500', 'to-blue-600', 'text-white', 'shadow-md', 'transform', '-translate-y-0.5');
        
        // Show corresponding panel with animation
        const id = 'tab-' + this.getAttribute('data-tab');
        const panel = document.getElementById(id);
        panel.classList.remove('hidden');
        
        // Trigger animation
        setTimeout(() => {
            panel.classList.remove('opacity-0');
            panel.classList.add('opacity-100');
        }, 50);
    });
});

// Enhanced preview functionality
function renderPreview(role) {
    const el = document.getElementById(role);
    if (!el) return;
    
    const raw = el.value;
    if (!raw.trim()) {
        document.getElementById('previewContent').innerHTML = 
            '<span class="text-gray-400 italic flex items-center"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>No content to preview</span>';
        return;
    }
    
    const when = new Date().toLocaleString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    const grace = document.querySelector('input[name="default_grace"]')?.value || '15';
    const adjusted = new Date(Date.now() + 30*60000).toLocaleString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    let rendered = raw
        .replace(/{when}/g, `<span class="bg-blue-100 text-blue-800 px-2 py-1 rounded font-medium">${when}</span>`)
        .replace(/{grace}/g, `<span class="bg-orange-100 text-orange-800 px-2 py-1 rounded font-medium">${grace} minutes</span>`)
        .replace(/{adjusted_time}/g, `<span class="bg-green-100 text-green-800 px-2 py-1 rounded font-medium">${adjusted}</span>`);
    
    document.getElementById('previewContent').innerHTML = rendered;
}

// Initialize preview
const previewSelect = document.getElementById('previewRole');
if (previewSelect) {
    previewSelect.addEventListener('change', (e) => renderPreview(e.target.value));
    renderPreview(previewSelect.value);
}

// Real-time preview updates
document.querySelectorAll('textarea').forEach(textarea => {
    textarea.addEventListener('input', () => {
        const role = previewSelect?.value;
        if (role === textarea.id) {
            renderPreview(role);
        }
    });
    
    // Auto-resize textarea
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 200) + 'px';
    });
});

// Initialize first tab as active
document.querySelector('.tab-btn[data-tab="templates"]')?.classList.add('active');
</script>

<script>
// Role collapsible panels + inline editor wiring
document.querySelectorAll('.role-toggle').forEach(btn => {
    btn.addEventListener('click', function(){
        const target = document.getElementById(this.getAttribute('data-target'));
        if (!target) return;
        target.classList.toggle('hidden');
    });
});

// Load initial previews from hidden inputs
document.querySelectorAll('.template-hidden-input').forEach(inp => {
    const key = inp.getAttribute('data-key');
    const previewEl = document.getElementById('preview-' + key);
    if (previewEl) {
        renderPreviewRawToElement(inp.value, previewEl);
    }
    // Also populate any editor textarea for this key (hidden by default)
    document.querySelectorAll('.editor[data-role="' + key + '"]').forEach(t => t.value = inp.value);
});
// Dropdown-driven editor and preview wiring
const dropdown = document.getElementById('templateDropdown');
const editor = document.getElementById('templateEditor');
const editBtn = document.getElementById('editToggle');

function loadSelectedTemplate() {
    const key = dropdown.value;
    if (!key) {
        document.getElementById('previewContent').innerHTML = 'Preview will appear here when you select a template...';
        editor.classList.add('hidden');
        return;
    }
    const hidden = document.querySelector('.template-hidden-input[data-key="' + key + '"]');
    const raw = hidden ? hidden.value : '';
    renderPreviewRawToElement(raw, document.getElementById('previewContent'));
    editor.value = raw;
    editor.classList.add('hidden');
    editBtn.textContent = 'Edit';
}

dropdown?.addEventListener('change', loadSelectedTemplate);

editBtn?.addEventListener('click', function(){
    if (editor.classList.contains('hidden')) {
        if (!dropdown.value) return; // nothing selected
        editor.classList.remove('hidden');
        editor.focus();
        this.textContent = 'Close';
    } else {
        editor.classList.add('hidden');
        this.textContent = 'Edit';
    }
});

editor?.addEventListener('input', function(){
    const key = dropdown.value;
    if (!key) return;
    const hidden = document.querySelector('.template-hidden-input[data-key="' + key + '"]');
    if (hidden) hidden.value = this.value;
    renderPreviewRawToElement(this.value, document.getElementById('previewContent'));
});

// Initialize dropdown to first template if needed
if (dropdown && !dropdown.value) {
    // Do not auto-select; leave blank so user chooses role explicitly
}

// Helper to render raw template into a specific element
function renderPreviewRawToElement(raw, el) {
    if (!raw || !raw.trim()) {
        el.innerHTML = '<span class="text-gray-400 italic">No content to preview</span>';
        return;
    }

    const when = new Date().toLocaleString('en-US', { weekday:'long', year:'numeric', month:'long', day:'numeric', hour:'2-digit', minute:'2-digit' });
    const grace = document.querySelector('input[name="default_grace"]')?.value || '15';
    const adjusted = new Date(Date.now() + 30*60000).toLocaleString('en-US', { weekday:'long', year:'numeric', month:'long', day:'numeric', hour:'2-digit', minute:'2-digit' });

    let rendered = raw
        .replace(/{when}/g, `<span class="bg-blue-100 text-blue-800 px-2 py-1 rounded font-medium">${when}</span>`)
        .replace(/{grace}/g, `<span class="bg-orange-100 text-orange-800 px-2 py-1 rounded font-medium">${grace} minutes</span>`)
        .replace(/{adjusted_time}/g, `<span class="bg-green-100 text-green-800 px-2 py-1 rounded font-medium">${adjusted}</span>`);

    el.innerHTML = rendered;
}
</script>

<?php
// End of enhanced settings view
?>