<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Availability</title>
    <!-- Use project's compiled CSS in production instead of CDN Tailwind -->
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/admin.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .gradient-bg { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
        }
        .gradient-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .hover-lift {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .form-input {
            transition: all 0.3s ease;
            border: 2px solid #e5e7eb;
        }
        .form-input:focus {
            border-color: #667eea;
            transform: translateY(-1px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .btn-danger {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .btn-danger:hover {
            background: linear-gradient(135deg, #e084ec 0%, #e6495d 100%);
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-weight: 600;
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <?= view('templates/sidebar', ['user' => $user ?? null]) ?>

    <!-- Opt into sidebar offset for this page only -->
    <div data-sidebar-offset class="flex-1 flex flex-col min-h-screen bg-white">
        <!-- Enhanced Header -->
        <header class="bg-white/80 backdrop-blur-xl shadow-sm border-b border-gray-200/50 sticky top-0 z-10">
            <div class="px-6 py-6">
                <div class="flex items-center justify-between">
                    <!-- Mobile sidebar toggle -->
                    <button id="sidebarToggleTop" class="lg:hidden mr-4 p-2 rounded-md text-gray-600 hover:bg-gray-100">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="flex items-center space-x-4">
                        <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-lg">
                            <i class="fas fa-calendar-alt text-white text-lg"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Manage Availability</h1>
                            <p class="mt-1 text-sm text-gray-600">Configure your working hours and manage time off</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="hidden md:flex items-center space-x-2 text-sm text-gray-500">
                            <i class="far fa-clock"></i>
                            <span id="currentTime"></span>
                        </div>
                        <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center shadow-md">
                            <span class="text-white font-semibold text-sm">DR</span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content (constrained to match dentist dashboard alignment) -->
        <main class="p-6 space-y-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Quick Actions Card -->
            <div class="glass rounded-2xl p-6 shadow-xl hover-lift fade-in">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                    <div class="flex items-center space-x-4">
                        <div class="h-12 w-12 rounded-xl gradient-card flex items-center justify-center shadow-md">
                            <i class="fas fa-plus text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Quick Actions</h3>
                            <p class="text-sm text-gray-600">Create time blocks for days off, sick leave, or emergencies</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button id="openCreateModal" class="btn-primary text-white font-semibold py-3 px-6 rounded-xl shadow-lg hover-lift flex items-center space-x-2">
                            <i class="fas fa-plus"></i>
                            <span>Create Time Block</span>
                        </button>
                        <button onclick="loadList()" class="bg-white/80 hover:bg-white text-gray-700 font-medium py-3 px-6 rounded-xl border border-gray-200 shadow-md hover-lift flex items-center space-x-2">
                            <i class="fas fa-sync-alt"></i>
                            <span>Refresh</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Current Schedule Card -->
            <div class="glass rounded-2xl shadow-xl overflow-hidden fade-in">
                <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center">
                                <i class="fas fa-calendar-check text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold">Current Schedule</h3>
                                <p class="text-blue-100">Your existing availability settings</p>
                            </div>
                        </div>
                        <div class="hidden md:block">
                            <div class="text-right">
                                <div class="text-3xl font-bold" id="totalBlocks">-</div>
                                <div class="text-sm text-blue-100">Total Blocks</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    <div id="availabilityListPage" class="min-h-[300px]">
                        <!-- Loading state -->
                        <div class="flex flex-col items-center justify-center py-16">
                            <div class="relative">
                                <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-200 border-t-blue-600"></div>
                            </div>
                            <span class="mt-4 text-gray-600 font-medium">Loading your availability...</span>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </main>
    </div>

    <!-- Enhanced Modal -->
    <div id="availabilityModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 transform transition-all">
            <div class="gradient-bg text-white p-6 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="h-10 w-10 rounded-xl bg-white/20 flex items-center justify-center">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <h3 id="modalTitle" class="text-xl font-bold">Create Time Block</h3>
                    </div>
                    <button id="closeModal" class="text-white/80 hover:text-white text-2xl leading-none">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <form id="availabilityModalForm" method="post" class="p-6 space-y-6">
                <input type="hidden" name="id" value="">
                <?= csrf_field() ?>
                
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-tag mr-2"></i>Type
                        </label>
                        <!-- Combo input: datalist provides dropdown options but allows free input -->
                        <input name="type" list="typeOptions" class="form-input w-full rounded-xl p-4 text-gray-900 font-medium" placeholder="e.g. day_off or Custom label" required>
                        <datalist id="typeOptions">
                            <option value="day_off">🏖️ Day Off</option>
                            <option value="sick_leave">🤒 Sick Leave</option>
                            <option value="emergency">🚨 Emergency</option>
                            <option value="urgent">⚡ Urgent</option>
                        </datalist>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-play mr-2"></i>Start Date & Time
                            </label>
                            <input name="start" type="datetime-local" class="form-input w-full rounded-xl p-4" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-stop mr-2"></i>End Date & Time
                            </label>
                            <input name="end" type="datetime-local" class="form-input w-full rounded-xl p-4" required>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-sticky-note mr-2"></i>Notes
                        </label>
                        <textarea name="notes" class="form-input w-full rounded-xl p-4 resize-none" rows="3" placeholder="Add any additional details..."></textarea>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
                    <button type="button" id="cancelModal" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-6 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="btn-danger text-white font-semibold py-3 px-6 rounded-xl shadow-lg hover-lift">
                        <i class="fas fa-save mr-2"></i>Save Block
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Notification Container -->
    <div id="notificationContainer" class="fixed top-4 right-4 z-60 space-y-2"></div>

    <script>
        // Real availability array (populated from the server)
        let availabilityData = [];
        // Safe base URL (bootstrap provided in header.php)
        const base = (typeof window !== 'undefined' && window.baseUrl) ? window.baseUrl : '';

        // Update current time
        function updateCurrentTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: true 
            });
            const timeElement = document.getElementById('currentTime');
            if (timeElement) {
                timeElement.textContent = timeString;
            }
        }

        // Update time every minute
        setInterval(updateCurrentTime, 60000);
        updateCurrentTime();

        function getTypeIcon(type) {
            const icons = {
                'day_off': '🏖️',
                'sick_leave': '🤒',
                'emergency': '🚨',
                'urgent': '⚡'
            };
            return icons[type] || '📅';
        }

        function getTypeColor(type) {
            const colors = {
                'day_off': 'bg-blue-100 text-blue-800',
                'sick_leave': 'bg-yellow-100 text-yellow-800',
                'emergency': 'bg-red-100 text-red-800',
                'urgent': 'bg-orange-100 text-orange-800'
            };
            return colors[type] || 'bg-gray-100 text-gray-800';
        }

        function formatDateTime(datetime) {
            const date = new Date(datetime.replace(' ', 'T'));
            return date.toLocaleDateString('en-US', {
                weekday: 'short',
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function createAvailabilityCard(item) {
            const typeColor = getTypeColor(item.type);
            const icon = getTypeIcon(item.type);
            const startFormatted = formatDateTime(item.start_datetime);
            const endFormatted = formatDateTime(item.end_datetime);
            
            return `
                <div class="bg-white rounded-xl shadow-md hover-lift p-6 border border-gray-100" data-id="${item.id}">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="text-2xl">${icon}</div>
                            <div>
                                <span class="status-badge ${typeColor}">${item.type.replace('_', ' ').toUpperCase()}</span>
                                <div class="text-sm text-gray-500 mt-1">ID: ${item.id}</div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button data-action="edit" data-id="${item.id}" class="text-blue-600 hover:text-blue-800 p-2 rounded-lg hover:bg-blue-50 transition-colors cursor-pointer" style="pointer-events: auto; position: relative; z-index: 10;">
                                <i class="fas fa-edit pointer-events-none"></i>
                            </button>
                            <button data-action="delete" data-id="${item.id}" class="text-red-600 hover:text-red-800 p-2 rounded-lg hover:bg-red-50 transition-colors cursor-pointer" style="pointer-events: auto; position: relative; z-index: 10;">
                                <i class="fas fa-trash pointer-events-none"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex items-center space-x-2 text-sm">
                            <i class="fas fa-play text-green-500"></i>
                            <span class="font-medium">Start:</span>
                            <span class="text-gray-600">${startFormatted}</span>
                        </div>
                        <div class="flex items-center space-x-2 text-sm">
                            <i class="fas fa-stop text-red-500"></i>
                            <span class="font-medium">End:</span>
                            <span class="text-gray-600">${endFormatted}</span>
                        </div>
                        ${item.notes ? `
                            <div class="flex items-start space-x-2 text-sm">
                                <i class="fas fa-sticky-note text-yellow-500 mt-0.5"></i>
                                <span class="font-medium">Notes:</span>
                                <span class="text-gray-600">${item.notes}</span>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        }

        async function loadList() {
            const container = document.getElementById('availabilityListPage');
            const totalBlocksElement = document.getElementById('totalBlocks');
            container.innerHTML = `
                <div class="flex flex-col items-center justify-center py-16">
                    <div class="relative">
                        <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-200 border-t-blue-600"></div>
                    </div>
                    <span class="mt-4 text-gray-600 font-medium">Loading your availability...</span>
                </div>
            `;

            try {
                const res = await fetch(base + '/dentist/availability/list', { credentials: 'same-origin' });
                if (!res.ok) {
                    showNotification('Failed to load availability: ' + res.status, 'error');
                    container.innerHTML = '';
                    return;
                }
                const j = await res.json();
                if (!j || !j.success) {
                    showNotification((j && j.message) ? j.message : 'Failed to load availability', 'error');
                    container.innerHTML = '';
                    return;
                }
                // Normalize availability items for consistent client usage
                availabilityData = (j.availability || []).map(item => {
                    const normalized = Object.assign({}, item);
                    // Ensure id is string for consistent lookups
                    if (normalized.id !== undefined && normalized.id !== null) normalized.id = String(normalized.id);
                    // Fallbacks for datetime fields (some DB rows may have start_time/end_time)
                    if ((!normalized.start_datetime || normalized.start_datetime === 'NULL') && normalized.start_time) {
                        normalized.start_datetime = normalized.start_time;
                    }
                    if ((!normalized.end_datetime || normalized.end_datetime === 'NULL') && normalized.end_time) {
                        normalized.end_datetime = normalized.end_time;
                    }
                    // Trim micro/seconds if present
                    if (normalized.start_datetime && normalized.start_datetime.length > 19) normalized.start_datetime = normalized.start_datetime.substring(0,19);
                    if (normalized.end_datetime && normalized.end_datetime.length > 19) normalized.end_datetime = normalized.end_datetime.substring(0,19);
                    return normalized;
                });
                if (!availabilityData.length) {
                    container.innerHTML = `
                        <div class="text-center py-16">
                            <div class="text-6xl mb-4">📅</div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">No availability blocks set</h3>
                            <p class="text-gray-600 mb-6">Create your first availability setting using the button above.</p>
                            <button onclick="document.getElementById('openCreateModal').click()" class="btn-primary text-white font-semibold py-3 px-6 rounded-xl shadow-lg hover-lift">
                                <i class="fas fa-plus mr-2"></i>Create First Block
                            </button>
                        </div>
                    `;
                    if (totalBlocksElement) totalBlocksElement.textContent = '0';
                } else {
                    container.innerHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                            ${availabilityData.map(createAvailabilityCard).join('')}
                        </div>
                    `;
                    if (totalBlocksElement) totalBlocksElement.textContent = availabilityData.length;
                }
            } catch (e) {
                console.error('loadList error', e);
                showNotification('Error loading availability', 'error');
                container.innerHTML = '';
            }
        }

        function editAvailability(id) {
            console.log('editAvailability called with ID:', id);
            console.log('availabilityData:', availabilityData);
            // Normalize id to string for flexible comparison
            const needle = (id === null || id === undefined) ? id : String(id);

            // Robust finder: accept numeric/string mismatches and alternate id keys
            const item = availabilityData.find(a => {
                if (!a) return false;
                // Common id keys
                const candidates = [a.id, a.availability_id, a._id, a.ID].filter(x => x !== undefined && x !== null);
                return candidates.some(c => String(c) === needle);
            });

            console.log('Found item after robust search:', item);
            if (!item) {
                console.log('No item found for ID:', id);
                return;
            }
            
            const modal = document.getElementById('availabilityModal');
            const form = document.getElementById('availabilityModalForm');
            const title = document.getElementById('modalTitle');
            
            console.log('Modal elements found:', { modal, form, title });
            
            title.innerHTML = '<i class="fas fa-edit mr-2"></i>Edit Time Block';
            form.querySelector('input[name="id"]').value = item.id;
            // Support both input[list] and legacy select for type
            const typeField = form.querySelector('input[name="type"]') || form.querySelector('select[name="type"]');
            if (typeField) typeField.value = item.type;
            // Populate datetime-local inputs without seconds (YYYY-MM-DDTHH:MM)
            try {
                const startVal = (item.start_datetime || '').replace(' ', 'T').substring(0,16);
                const endVal = (item.end_datetime || '').replace(' ', 'T').substring(0,16);
                form.querySelector('input[name="start"]').value = startVal;
                form.querySelector('input[name="end"]').value = endVal;
            } catch(e) {
                form.querySelector('input[name="start"]').value = item.start_datetime.replace(' ', 'T');
                form.querySelector('input[name="end"]').value = item.end_datetime.replace(' ', 'T');
            }
            form.querySelector('textarea[name="notes"]').value = item.notes || '';
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            console.log('Modal should now be visible');
        }

        async function deleteAvailability(id) {
            if (!confirm('Are you sure you want to delete this availability block?')) return;
            try {
                const body = new URLSearchParams();
                body.append('id', id);
                body.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
                const res = await fetch(base + '/dentist/availability/delete', { method: 'POST', body, credentials: 'same-origin' });
                const j = await res.json().catch(()=> null);
                if (!res.ok || !j || !j.success) {
                    showNotification((j && j.message) ? j.message : 'Failed to delete', 'error');
                    return;
                }
                showNotification('Availability block deleted successfully', 'success');
                loadList();
            } catch (e) {
                console.error('deleteAvailability error', e);
                showNotification('Error deleting availability', 'error');
            }
        }

        function showNotification(message, type = 'success') {
            const container = document.getElementById('notificationContainer');
            const notification = document.createElement('div');
            
            const colors = {
                success: 'bg-green-500 border-green-600',
                error: 'bg-red-500 border-red-600',
                info: 'bg-blue-500 border-blue-600',
                warning: 'bg-yellow-500 border-yellow-600'
            };
            
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle', 
                info: 'fa-info-circle',
                warning: 'fa-exclamation-triangle'
            };
            
            notification.className = `${colors[type]} text-white px-6 py-4 rounded-xl shadow-lg border-l-4 transform translate-x-full transition-transform duration-500 max-w-sm`;
            notification.innerHTML = `
                <div class="flex items-center space-x-3">
                    <i class="fas ${icons[type]} text-lg"></i>
                    <span class="font-medium">${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-white/80 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            container.appendChild(notification);
            
            // Animate in
            setTimeout(() => notification.classList.remove('translate-x-full'), 100);
            
            // Auto remove
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => notification.remove(), 500);
            }, 5000);
        }

        // Modal functionality
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('availabilityModal');
            const openBtn = document.getElementById('openCreateModal');
            const closeBtn = document.getElementById('closeModal');
            const cancelBtn = document.getElementById('cancelModal');
            const form = document.getElementById('availabilityModalForm');
            const title = document.getElementById('modalTitle');

            function openModal() {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                form.reset();
                form.querySelector('input[name="id"]').value = '';
            }

            openBtn.addEventListener('click', function() {
                title.innerHTML = '<i class="fas fa-plus mr-2"></i>Create Time Block';
                form.querySelector('input[name="id"]').value = '';
                openModal();
            });

            closeBtn.addEventListener('click', closeModal);
            cancelBtn.addEventListener('click', closeModal);

            // Close modal when clicking outside
            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeModal();
            });

            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = new FormData(form);
                let id = formData.get('id');
                const type = formData.get('type');
                let start = formData.get('start');
                let end = formData.get('end');

                // Debug: raw values before normalization
                console.log('Raw form values - start:', start, 'end:', end);

                // Client-side guard: ensure start/end present
                if (!start || !end) {
                    showNotification('Please provide both start and end times', 'error');
                    console.warn('Aborting submit: missing start or end', { start, end });
                    return;
                }
                const notes = formData.get('notes');

                if (new Date(start) >= new Date(end)) {
                    showNotification('End time must be after start time', 'error');
                    return;
                }

                // Normalize datetimes to 'YYYY-MM-DD HH:MM:SS'
                const normalizeDateTimeLocal = (val) => {
                    if (!val) return val;
                    // If contains T (from datetime-local), split
                    if (val.indexOf('T') !== -1) {
                        const [d, t] = val.split('T');
                        const parts = (t || '').split(':');
                        if (parts.length === 3) return d + ' ' + t; // already has seconds
                        if (parts.length === 2) return d + ' ' + parts[0].padStart(2,'0') + ':' + parts[1].padStart(2,'0') + ':00';
                        return d + ' ' + t + ':00';
                    }
                    // If already space-separated
                    const parts = val.split(' ');
                    if (parts.length === 2) {
                        const timeParts = parts[1].split(':');
                        if (timeParts.length === 3) return val;
                        if (timeParts.length === 2) return parts[0] + ' ' + timeParts[0].padStart(2,'0') + ':' + timeParts[1].padStart(2,'0') + ':00';
                    }
                    return val;
                };

                start = normalizeDateTimeLocal(start);
                end = normalizeDateTimeLocal(end);

                try {
                    const body = new URLSearchParams();
                    if (id) body.append('id', id);
                    body.append('type', type);
                    body.append('start', start);
                    body.append('end', end);
                    body.append('notes', notes);
                    body.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

                    // Append duplicate keys to satisfy different server expectations
                    // (some endpoints/models expect start_datetime/end_datetime)
                    body.append('start_datetime', start);
                    body.append('end_datetime', end);

                    // Debug: show payload in console for easier troubleshooting
                    try {
                        const debugObj = {};
                        for (const pair of body.entries()) {
                            debugObj[pair[0]] = pair[1];
                        }
                        console.log('Submitting availability payload:', debugObj);
                    } catch (e) { /* ignore */ }

                    const endpoint = id ? (base + '/dentist/availability/update') : (base + '/dentist/availability/create');
                    const res = await fetch(endpoint, { method: 'POST', body, credentials: 'same-origin' });
                    const j = await res.json().catch(()=> null);
                    if (!res.ok || !j || !j.success) {
                        showNotification((j && j.message) ? j.message : 'Failed to save availability', 'error');
                        return;
                    }

                    showNotification(j.message || (id ? 'Time block updated' : 'Time block created'), 'success');
                    closeModal();
                    loadList();
                } catch (err) {
                    console.error('availability form submit error', err);
                    showNotification('Error saving availability', 'error');
                }
            });

            // Initial load
            loadList();
        });

        // Event delegation for dynamically created edit/delete buttons
        // Ignore clicks that happen inside the open modal to avoid interference
        document.addEventListener('click', function(e) {
            const modal = document.getElementById('availabilityModal');
            if (modal && !modal.classList.contains('hidden') && modal.contains(e.target)) {
                // Click occurred inside the modal; ignore global availability handlers
                return;
            }

            const button = e.target.closest('button[data-action]');
            if (!button) return;

            e.preventDefault();
            e.stopPropagation();

            const action = button.getAttribute('data-action');
            const id = button.getAttribute('data-id');

            if (action === 'edit') {
                editAvailability(parseInt(id));
            } else if (action === 'delete') {
                deleteAvailability(parseInt(id));
            }
        });
    </script>
    
</body>
</html>