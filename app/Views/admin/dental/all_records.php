<?= view('templates/header') ?>

<!-- Meta tag for base URL (for JavaScript) -->
<meta name="base-url" content="<?= base_url() ?>">

<!-- Three.js Library for 3D Dental Model -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>

<!-- 3D Dental Viewer Styles and Scripts -->
<link rel="stylesheet" href="<?= base_url('css/dental-3d-viewer.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/records-management.css') ?>">
<script src="<?= base_url('js/dental-3d-viewer.js') ?>"></script>

<!-- Modular Records Management System -->
<script src="<?= base_url('js/modules/records-utilities.js') ?>"></script>
<script src="<?= base_url('js/modules/modal-controller.js') ?>"></script>
<script src="<?= base_url('js/modules/data-loader.js') ?>"></script>
<script src="<?= base_url('js/modules/display-manager.js') ?>"></script>
<script src="<?= base_url('js/modules/dental-3d-manager.js') ?>"></script>
<script src="<?= base_url('js/modules/conditions-analyzer.js') ?>"></script>
<script src="<?= base_url('js/modules/records-manager.js') ?>"></script>

<script>
// Set base URL for JavaScript modules
window.BASE_URL = '<?= base_url() ?>';
</script>

<div class="flex min-h-screen bg-gray-100">
    <!-- Include existing sidebar -->
    <?= view('templates/sidebar') ?>

    <!-- Main Content Area -->
    <div class="flex-1 lg:ml-0 p-6">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Records Management</h1>
            <p class="text-gray-600">Comprehensive dental records management system</p>
        </div>

        <!-- Main Content Area - Full Width Records Table -->
        <div class="bg-white shadow rounded-lg">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center mb-4">
                    <h2 class="text-lg font-bold text-gray-800">Recent Records</h2>
                </div>
                
                <!-- Advanced Search Bar -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex flex-col md:flex-row gap-4">
                        <!-- Main Search Input -->
                        <div class="flex-1 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   id="recordsSearchInput" 
                                   class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
                                   placeholder="Search by patient name, email, phone, date, type, or allergies...">
                        </div>
                        
                        <!-- Search Filters -->
                        <div class="flex gap-2">
                            <select id="statusFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-sm">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            
                            <select id="typeFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-sm">
                                <option value="">All Types</option>
                                <option value="general">General</option>
                                <option value="checkup">Checkup</option>
                                <option value="treatment">Treatment</option>
                                <option value="emergency">Emergency</option>
                            </select>
                            
                            <button id="clearSearch" class="px-3 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 text-sm transition-colors">
                                <i class="fas fa-times mr-1"></i>Clear
                            </button>
                        </div>
                    </div>
                    
                    <!-- Search Results Summary -->
                    <div id="searchSummary" class="mt-3 text-sm text-gray-600 hidden">
                        <i class="fas fa-info-circle mr-1"></i>
                        <span id="searchResultsCount">0</span> records found
                        <span id="searchTermDisplay"></span>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="full-width-table min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($records)): ?>
                            <?php foreach ($records as $record): ?>
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= date('M j, Y', strtotime($record['record_date'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <i class="fas fa-user text-blue-600"></i>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900"><?= esc($record['patient_name']) ?></div>
                                                <div class="text-sm text-gray-500">
                                                    <?= esc($record['patient_email']) ?>
                                                    <?php if (!empty($record['patient_phone'])): ?>
                                                        • <?= esc($record['patient_phone']) ?>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (!empty($record['allergies'])): ?>
                                                    <div class="text-xs text-red-600 mt-1">
                                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                                        Allergies: <?= esc($record['allergies']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= ucfirst($record['record_type'] ?? 'General') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?= ($record['status'] ?? 'active') === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                            <?= ucfirst($record['status'] ?? 'Active') ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap records-table-actions">
                                        <div class="flex flex-wrap gap-2">
                                            <button onclick="window.recordsManager?.openPatientRecordsModal(<?= $record['user_id'] ?>)"
                                                    class="action-btn action-btn-view">
                                                <i class="fas fa-eye mr-1"></i>
                                                <span>View</span>
                                            </button>
                                            <button onclick="deleteRecord(<?= $record['id'] ?>)"
                                                    class="action-btn action-btn-delete">
                                                <i class="fas fa-trash mr-1"></i>
                                                <span>Delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    <div class="flex flex-col items-center py-8">
                                        <i class="fas fa-file-medical fa-3x text-gray-300 mb-4"></i>
                                        <p class="text-lg font-medium">No records found</p>
                                        <p class="text-sm">Get started by creating a new dental record.</p>
                                        <a href="<?= base_url('admin/dental-records/create') ?>" 
                                           class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                            <i class="fas fa-plus mr-2"></i>Create New Record
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Patient Records Modal -->
<div id="patientRecordsModal" class="modal-overlay hidden fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm z-50 transition-all duration-300 ease-in-out">
    <div id="modalDialog" class="modal-container flex items-center justify-center min-h-screen p-4">
        <div class="resizable-modal relative bg-white rounded-xl shadow-2xl border border-gray-200 transition-all duration-300 ease-in-out transform scale-95 opacity-0" 
             style="width: 90%; max-width: 1200px; min-width: 800px; height: 85vh; max-height: 95vh; min-height: 600px;">
            
            <!-- Modal Header -->
            <div class="modal-header-resizable flex justify-between items-center p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-t-xl">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-md text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Patient Records</h3>
                        <p class="text-sm text-gray-600">Comprehensive patient information</p>
                    </div>
                </div>
                
                <!-- Modal Controls -->
                <div class="modal-controls flex items-center space-x-2">
                    <button id="fullscreenToggle" type="button" class="fullscreen-btn" title="Toggle Fullscreen">
                        <i class="fas fa-expand"></i>
                    </button>
                    <button type="button" onclick="window.recordsManager?.closePatientRecordsModal()" 
                            class="text-gray-400 hover:text-gray-600 p-2 rounded-lg hover:bg-white/50 transition-colors">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>
            
            <!-- Modal Navigation -->
            <div class="flex space-x-1 px-6 pt-4 border-b border-gray-100">
                <button id="basic-info-tab" onclick="window.recordsManager?.showRecordTab('basic-info')" 
                        class="record-tab px-4 py-3 text-sm font-medium rounded-t-lg bg-blue-600 text-white shadow-sm transition-all duration-200">
                    <i class="fas fa-user mr-2"></i>Basic Info
                </button>
                <button id="dental-records-tab" onclick="window.recordsManager?.showRecordTab('dental-records')" 
                        class="record-tab px-4 py-3 text-sm font-medium rounded-t-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-all duration-200">
                    <i class="fas fa-tooth mr-2"></i>Dental Records
                </button>
                <button id="dental-chart-tab" onclick="window.recordsManager?.showRecordTab('dental-chart')" 
                        class="record-tab px-4 py-3 text-sm font-medium rounded-t-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-all duration-200">
                    <i class="fas fa-chart-line mr-2"></i>Dental Chart
                </button>
                <button id="appointments-tab" onclick="window.recordsManager?.showRecordTab('appointments')" 
                        class="record-tab px-4 py-3 text-sm font-medium rounded-t-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-all duration-200">
                    <i class="fas fa-calendar mr-2"></i>Appointments
                </button>
                <button id="treatments-tab" onclick="window.recordsManager?.showRecordTab('treatments')" 
                        class="record-tab px-4 py-3 text-sm font-medium rounded-t-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-all duration-200">
                    <i class="fas fa-procedures mr-2"></i>Treatments
                </button>
                <button id="medical-records-tab" onclick="window.recordsManager?.showRecordTab('medical-records')" 
                        class="record-tab px-4 py-3 text-sm font-medium rounded-t-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-all duration-200">
                    <i class="fas fa-file-medical mr-2"></i>Medical Records
                </button>
            </div>
            
            <!-- Modal Content -->
            <div class="modal-content-resizable p-6 overflow-y-auto" style="height: calc(100% - 140px);">
                <div id="modalContent" class="w-full h-full">
                    <div class="flex items-center justify-center h-32">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin text-2xl text-blue-500 mb-2"></i>
                            <p class="text-gray-600">Loading patient information...</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Resize Handle -->
            <div class="resize-handle-se absolute bottom-0 right-0 w-5 h-5 cursor-se-resize opacity-60 hover:opacity-100 transition-opacity">
                <i class="fas fa-grip-lines-vertical text-gray-400 text-xs transform rotate-45"></i>
            </div>
        </div>
    </div>
</div>

<!-- Advanced Records Search Functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize search functionality
    initializeRecordsSearch();
});

function initializeRecordsSearch() {
    const searchInput = document.getElementById('recordsSearchInput');
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('typeFilter');
    const clearButton = document.getElementById('clearSearch');
    const searchSummary = document.getElementById('searchSummary');
    const searchResultsCount = document.getElementById('searchResultsCount');
    const searchTermDisplay = document.getElementById('searchTermDisplay');
    
    // Get all table rows (excluding header and empty state)
    const tableBody = document.querySelector('tbody');
    const originalRows = Array.from(tableBody.querySelectorAll('tr')).filter(row => 
        !row.querySelector('td[colspan]') // Exclude "no records found" row
    );
    
    let searchTimeout;
    
    // Main search function
    function performSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const statusValue = statusFilter.value.toLowerCase();
            const typeValue = typeFilter.value.toLowerCase();
            
            let visibleCount = 0;
            
            originalRows.forEach(row => {
                const shouldShow = matchesSearchCriteria(row, searchTerm, statusValue, typeValue);
                
                if (shouldShow) {
                    row.style.display = '';
                    row.classList.add('search-match');
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                    row.classList.remove('search-match');
                }
            });
            
            // Update search summary
            updateSearchSummary(visibleCount, searchTerm, statusValue, typeValue);
            
            // Handle empty results
            handleEmptyResults(visibleCount);
            
        }, 300); // Debounce search for 300ms
    }
    
    // Check if a row matches search criteria
    function matchesSearchCriteria(row, searchTerm, statusValue, typeValue) {
        // Get row data
        const dateCell = row.querySelector('td:nth-child(1)')?.textContent.toLowerCase() || '';
        const patientCell = row.querySelector('td:nth-child(2)');
        const typeCell = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
        const statusCell = row.querySelector('td:nth-child(4)')?.textContent.toLowerCase() || '';
        
        // Extract patient information
        const patientName = patientCell?.querySelector('.font-medium')?.textContent.toLowerCase() || '';
        const patientContact = patientCell?.querySelector('.text-gray-500')?.textContent.toLowerCase() || '';
        const allergies = patientCell?.querySelector('.text-red-600')?.textContent.toLowerCase() || '';
        
        // Check search term (searches across multiple fields)
        const matchesSearchTerm = !searchTerm || 
            dateCell.includes(searchTerm) ||
            patientName.includes(searchTerm) ||
            patientContact.includes(searchTerm) ||
            typeCell.includes(searchTerm) ||
            statusCell.includes(searchTerm) ||
            allergies.includes(searchTerm);
        
        // Check status filter
        const matchesStatus = !statusValue || statusCell.includes(statusValue);
        
        // Check type filter
        const matchesType = !typeValue || typeCell.includes(typeValue);
        
        return matchesSearchTerm && matchesStatus && matchesType;
    }
    
    // Update search summary
    function updateSearchSummary(visibleCount, searchTerm, statusValue, typeValue) {
        const totalRecords = originalRows.length;
        
        if (searchTerm || statusValue || typeValue) {
            searchSummary.classList.remove('hidden');
            searchResultsCount.textContent = visibleCount;
            
            let summaryText = '';
            if (searchTerm) summaryText += ` for "${searchTerm}"`;
            if (statusValue) summaryText += ` • Status: ${statusValue}`;
            if (typeValue) summaryText += ` • Type: ${typeValue}`;
            
            searchTermDisplay.textContent = summaryText;
        } else {
            searchSummary.classList.add('hidden');
        }
    }
    
    // Handle empty search results
    function handleEmptyResults(visibleCount) {
        const existingEmptyRow = tableBody.querySelector('.search-empty-row');
        
        if (visibleCount === 0 && originalRows.length > 0) {
            // Show "No matching records" message
            if (!existingEmptyRow) {
                const emptyRow = document.createElement('tr');
                emptyRow.className = 'search-empty-row';
                emptyRow.innerHTML = `
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-search text-gray-300 text-3xl mb-3"></i>
                            <p class="text-lg font-medium">No matching records found</p>
                            <p class="text-sm">Try adjusting your search criteria or clearing the filters.</p>
                        </div>
                    </td>
                `;
                tableBody.appendChild(emptyRow);
            }
        } else if (existingEmptyRow) {
            // Remove "No matching records" message
            existingEmptyRow.remove();
        }
    }
    
    // Clear all filters and search
    function clearAllFilters() {
        searchInput.value = '';
        statusFilter.value = '';
        typeFilter.value = '';
        
        // Show all rows
        originalRows.forEach(row => {
            row.style.display = '';
            row.classList.remove('search-match');
        });
        
        // Hide search summary
        searchSummary.classList.add('hidden');
        
        // Remove empty results message
        const existingEmptyRow = tableBody.querySelector('.search-empty-row');
        if (existingEmptyRow) {
            existingEmptyRow.remove();
        }
        
        // Focus back to search input
        searchInput.focus();
    }
    
    // Highlight search terms in results
    function highlightSearchTerms(searchTerm) {
        if (!searchTerm) {
            // Remove existing highlights
            document.querySelectorAll('.search-highlight').forEach(highlight => {
                const parent = highlight.parentNode;
                parent.replaceChild(document.createTextNode(highlight.textContent), highlight);
                parent.normalize();
            });
            return;
        }
        
        const visibleRows = originalRows.filter(row => row.style.display !== 'none');
        
        visibleRows.forEach(row => {
            const textNodes = getTextNodes(row);
            textNodes.forEach(node => {
                if (node.textContent.toLowerCase().includes(searchTerm)) {
                    highlightText(node, searchTerm);
                }
            });
        });
    }
    
    // Helper function to get all text nodes
    function getTextNodes(element) {
        const textNodes = [];
        const walker = document.createTreeWalker(
            element,
            NodeFilter.SHOW_TEXT,
            null,
            false
        );
        
        let node;
        while (node = walker.nextNode()) {
            if (node.textContent.trim()) {
                textNodes.push(node);
            }
        }
        return textNodes;
    }
    
    // Helper function to highlight text
    function highlightText(textNode, searchTerm) {
        const parent = textNode.parentNode;
        const text = textNode.textContent;
        const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
        
        if (regex.test(text)) {
            const highlightedHTML = text.replace(regex, '<span class="search-highlight bg-yellow-200 px-1 rounded">$1</span>');
            const wrapper = document.createElement('span');
            wrapper.innerHTML = highlightedHTML;
            parent.replaceChild(wrapper, textNode);
        }
    }
    
    // Helper function to escape regex special characters
    function escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
    
    // Event listeners
    searchInput.addEventListener('input', performSearch);
    statusFilter.addEventListener('change', performSearch);
    typeFilter.addEventListener('change', performSearch);
    clearButton.addEventListener('click', clearAllFilters);
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + F to focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
        
        // Escape to clear search
        if (e.key === 'Escape' && document.activeElement === searchInput) {
            clearAllFilters();
        }
    });
    
    // Initialize with focus on search input
    searchInput.focus();
}

// Enhanced search with advanced features
function enhanceSearchExperience() {
    const searchInput = document.getElementById('recordsSearchInput');
    
    // Add search suggestions/autocomplete
    const suggestions = [
        'Active patients', 'Inactive patients', 'General records', 
        'Checkup records', 'Treatment records', 'Emergency records',
        'Patients with allergies', 'Recent records', 'This month', 'Last week'
    ];
    
    // Create suggestions dropdown
    const suggestionsContainer = document.createElement('div');
    suggestionsContainer.className = 'absolute top-full left-0 right-0 bg-white border border-gray-300 rounded-b-lg shadow-lg z-10 hidden max-h-48 overflow-y-auto';
    suggestionsContainer.id = 'searchSuggestions';
    
    searchInput.parentNode.classList.add('relative');
    searchInput.parentNode.appendChild(suggestionsContainer);
    
    // Show/hide suggestions
    searchInput.addEventListener('focus', () => {
        if (searchInput.value.length === 0) {
            showAllSuggestions();
        }
    });
    
    searchInput.addEventListener('blur', () => {
        setTimeout(() => suggestionsContainer.classList.add('hidden'), 150);
    });
    
    function showAllSuggestions() {
        suggestionsContainer.innerHTML = suggestions.map(suggestion => 
            `<div class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm border-b border-gray-100 last:border-b-0" onclick="selectSuggestion('${suggestion}')">${suggestion}</div>`
        ).join('');
        suggestionsContainer.classList.remove('hidden');
    }
    
    window.selectSuggestion = function(suggestion) {
        searchInput.value = suggestion;
        suggestionsContainer.classList.add('hidden');
        searchInput.dispatchEvent(new Event('input'));
        searchInput.focus();
    };
}

// Initialize enhanced search features
document.addEventListener('DOMContentLoaded', function() {
    enhanceSearchExperience();
});
</script>

<?= view('templates/footer') ?>
