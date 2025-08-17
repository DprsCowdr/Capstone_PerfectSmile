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

<div class="flex min-h-screen bg-gray-50">
    <!-- Include existing sidebar -->
    <?= view('templates/sidebar') ?>

    <!-- Main Content Area -->
    <div class="flex-1 lg:ml-0 p-8 space-y-8">
        <!-- Page Header -->
        <header class="mb-2">
            <h1 class="text-xl font-semibold text-gray-800 tracking-tight">Records Management</h1>
            <p class="text-sm text-gray-500">Comprehensive dental records management system</p>
        </header>

        <!-- Main Content Area - Full Width Records Table -->
        <section class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center mb-3">
                    <h2 class="text-sm font-semibold text-gray-700">Recent Records</h2>
                </div>
                
                <!-- Advanced Search Bar -->
                <div class="bg-gray-50 rounded-md p-4">
                    <div class="flex flex-col md:flex-row gap-4 md:items-center">
                        <!-- Main Search Input -->
                        <div class="flex-1 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                <input type="text" 
                                   id="recordsSearchInput" 
                    class="block w-full pl-9 pr-3 py-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-sm placeholder:text-gray-400"
                                   placeholder="Search patient name, contact, date, type, allergies...">
                        </div>
                        
                        <!-- Search Filters -->
            <div class="flex gap-3 md:w-auto">
                <select id="statusFilter" class="px-3 py-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-xs text-gray-700">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            
                <select id="typeFilter" class="px-3 py-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-xs text-gray-700">
                                <option value="">All Types</option>
                                <option value="general">General</option>
                                <option value="checkup">Checkup</option>
                                <option value="treatment">Treatment</option>
                                <option value="emergency">Emergency</option>
                            </select>
                            
                <button id="clearSearch" class="px-3 py-2.5 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-xs font-medium transition-colors flex items-center gap-1 shadow-sm">
                                <i class="fas fa-times text-[11px]"></i><span>Clear</span>
                            </button>
                        </div>
                    </div>
                    
            <!-- Search Results Summary -->
            <div id="searchSummary" class="mt-3 text-xs text-gray-600 hidden">
                        <i class="fas fa-info-circle mr-1"></i>
                        <span id="searchResultsCount">0</span> records found
                        <span id="searchTermDisplay"></span>
                    </div>
                </div>
            </div>
            
        <div class="overflow-x-auto scrollbar-thin">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50/70">
                        <tr>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Patient</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Type</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
            <tbody class="bg-white divide-y divide-gray-100 text-sm">
                        <?php if (!empty($records)): ?>
                            <?php foreach ($records as $record): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-5 py-3 whitespace-nowrap text-[13px] text-gray-800">
                                        <?= date('M j, Y', strtotime($record['record_date'])) ?>
                                    </td>
                                    <td class="px-5 py-3 whitespace-nowrap">
                                        <div class="flex items-start gap-4">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-sm font-medium">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div class="min-w-[180px] space-y-1">
                                                <div class="text-[13px] font-medium text-gray-900 leading-tight tracking-tight">
                                                    <?= esc($record['patient_name']) ?>
                                                </div>
                                                <div class="text-[11px] text-gray-500">
                                                    <?= esc($record['patient_email']) ?>
                                                    <?php if (!empty($record['patient_phone'])): ?>
                                                        • <?= esc($record['patient_phone']) ?>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (!empty($record['allergies'])): ?>
                                                    <div class="text-[10px] text-red-600 mt-1 flex items-center gap-1">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        <span><?= esc($record['allergies']) ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 whitespace-nowrap text-[13px] text-gray-700">
                                        <?= ucfirst($record['record_type'] ?? 'General') ?>
                                    </td>
                                    <td class="px-5 py-3 whitespace-nowrap">
                                        <span class="px-2 py-0.5 inline-flex text-[10px] font-medium rounded-full tracking-wide <?= ($record['status'] ?? 'active') === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' ?>">
                                            <?= ucfirst($record['status'] ?? 'Active') ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <button onclick="window.recordsManager?.openPatientRecordsModal(<?= $record['user_id'] ?>)" class="px-3 py-1.5 rounded-md bg-blue-600 text-white text-[11px] font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1 shadow-sm">
                                                View
                                            </button>
                                            <button onclick="deleteRecord(<?= $record['id'] ?>)" class="px-3 py-1.5 rounded-md bg-red-50 text-red-600 text-[11px] font-medium hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-1">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-14 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-file-medical text-3xl text-gray-300 mb-3"></i>
                                        <p class="text-sm font-medium">No records found</p>
                                        <p class="text-xs">Create a new dental record to get started.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<!-- Patient Records Modal -->
<div id="patientRecordsModal" class="hidden fixed inset-0 z-50 flex items-start justify-center bg-gray-900/50 backdrop-blur-sm p-4 overflow-y-auto">
    <div id="modalDialog" class="w-full max-w-5xl mx-auto">
        <div class="modal-panel relative bg-white rounded-lg border border-gray-200 shadow-xl overflow-hidden transform transition-all scale-95 opacity-0" style="min-height:560px;">
            
            <!-- Modal Header -->
            <header class="flex justify-between items-center px-5 py-3 border-b border-gray-100 bg-white">
                <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-user-md text-blue-600"></i>
                    Patient Records
                </h3>
                <button type="button" onclick="window.recordsManager?.closePatientRecordsModal()" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-md hover:bg-gray-100">
                    <span class="sr-only">Close</span>
                    <i class="fas fa-times text-sm"></i>
                </button>
            </header>
            
            <!-- Modal Navigation -->
            <nav class="flex gap-1 px-5 pt-3 border-b border-gray-100 bg-white overflow-x-auto text-xs">
                <button id="basic-info-tab" onclick="window.recordsManager?.showRecordTab('basic-info')" class="record-tab px-3 py-2 rounded-md bg-blue-600 text-white font-medium">Basic Info</button>
                <button id="dental-records-tab" onclick="window.recordsManager?.showRecordTab('dental-records')" class="record-tab px-3 py-2 rounded-md text-gray-600 hover:bg-gray-100">Dental Records</button>
                <button id="dental-chart-tab" onclick="window.recordsManager?.showRecordTab('dental-chart')" class="record-tab px-3 py-2 rounded-md text-gray-600 hover:bg-gray-100">Dental Chart</button>
                <button id="appointments-tab" onclick="window.recordsManager?.showRecordTab('appointments')" class="record-tab px-3 py-2 rounded-md text-gray-600 hover:bg-gray-100">Appointments</button>
                <button id="treatments-tab" onclick="window.recordsManager?.showRecordTab('treatments')" class="record-tab px-3 py-2 rounded-md text-gray-600 hover:bg-gray-100">Treatments</button>
                <button id="medical-records-tab" onclick="window.recordsManager?.showRecordTab('medical-records')" class="record-tab px-3 py-2 rounded-md text-gray-600 hover:bg-gray-100">Medical Records</button>
            </nav>
            
            <!-- Modal Content -->
            <div class="px-5 py-4 overflow-y-auto" style="height: calc(100% - 92px);">
                <div id="modalContent" class="w-full h-full text-sm">
                    <div class="flex items-center justify-center h-40">
                        <div class="text-center space-y-2">
                            <i class="fas fa-spinner fa-spin text-xl text-blue-500"></i>
                            <p class="text-xs text-gray-500">Loading patient information...</p>
                        </div>
                    </div>
                </div>
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
