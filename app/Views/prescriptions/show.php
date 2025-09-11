<?php $user = $user ?? session('user') ?? []; ?>
<div data-sidebar-offset>
    <nav class="sticky top-0 bg-white shadow-sm z-20 p-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="<?= base_url('admin/prescriptions') ?>" 
                   class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Prescriptions
                </a>
                <div class="h-5 w-px bg-gray-300"></div>
                <h1 class="text-2xl font-bold text-gray-900">Prescription #<?= esc($prescription['id']) ?></h1>
            </div>
            <div class="flex items-center space-x-3">
                <button type="button" onclick="openPdfPreview(<?= esc($prescription['id']) ?>)" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Preview & Download
                </button>
                <div class="relative">
                    <button type="button" class="inline-flex items-center px-3 py-2 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors duration-200"
                            onclick="document.getElementById('actionsMenu').classList.toggle('hidden')">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                        </svg>
                        Actions
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div id="actionsMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-10">
                        <a href="<?= base_url('admin/prescriptions/'.$prescription['id'].'/edit') ?>" 
                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit Prescription
                        </a>
                        <a href="<?= base_url('admin/prescriptions/'.$prescription['id'].'/duplicate') ?>" 
                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            Duplicate
                        </a>
                        <hr class="my-1">
                        <button onclick="if(confirm('Are you sure you want to delete this prescription?')) window.location.href='<?= base_url('admin/prescriptions/'.$prescription['id'].'/delete') ?>'" 
                                class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete Prescription
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="p-6 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto space-y-6">
            
            <!-- Issue Date Banner -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div></div>
                    <div class="text-sm text-gray-600">
                        Issue Date: <span class="font-medium text-gray-900"><?= date('M d, Y', strtotime($prescription['issue_date'])) ?></span>
                    </div>
                </div>
            </div>

            <!-- Patient & Dentist Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Patient Information -->
                <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Patient Information
                    </h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-700">Name:</span>
                            <span class="text-sm text-gray-900"><?= esc($prescription['patient_name'] ?? 'Unknown') ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-700">Age:</span>
                            <span class="text-sm text-gray-900"><?= esc($prescription['patient_age'] ?? '-') ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-700">Gender:</span>
                            <span class="text-sm text-gray-900"><?= esc($prescription['patient_gender'] ?? '-') ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-700">Address:</span>
                            <span class="text-sm text-gray-900 text-right"><?= esc($prescription['patient_address'] ?? '-') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Dentist Information -->
                <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                        Dentist Information
                    </h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-700">Name:</span>
                            <span class="text-sm text-gray-900"><?= esc($prescription['dentist_name'] ?? 'Unknown') ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-700">License No.:</span>
                            <span class="text-sm text-gray-900"><?= esc($prescription['license_no'] ?? '-') ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-700">PTR No.:</span>
                            <span class="text-sm text-gray-900"><?= esc($prescription['ptr_no'] ?? '-') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <?php if (!empty($prescription['instructions'])): ?>
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Instructions
                </h2>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-700 leading-relaxed"><?= nl2br(esc($prescription['instructions'])) ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Medicines -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                    Prescribed Medicines
                </h2>
                <?php if (!empty($items)): ?>
                    <div class="space-y-4">
                        <?php foreach ($items as $index => $it): ?>
                        <div class="bg-gradient-to-r from-gray-50 to-white rounded-lg p-4 border border-gray-200 hover:shadow-sm transition-shadow duration-200">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <span class="inline-flex items-center justify-center w-6 h-6 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                                            <?= $index + 1 ?>
                                        </span>
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            <?= esc($it['medicine_name']) ?>
                                            <span class="text-base font-medium text-gray-600 ml-1"><?= esc($it['dosage']) ?></span>
                                        </h3>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="text-gray-700"><strong>Frequency:</strong> <?= esc($it['frequency']) ?></span>
                                        </div>
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="text-gray-700"><strong>Duration:</strong> <?= esc($it['duration']) ?></span>
                                        </div>
                                    </div>
                                    <?php if (!empty($it['instructions'])): ?>
                                        <div class="mt-3 bg-blue-50 rounded-md p-3 border-l-4 border-blue-400">
                                            <div class="flex items-start">
                                                <svg class="w-4 h-4 mr-2 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span class="text-sm text-blue-800 font-medium">Instructions: </span>
                                                <span class="text-sm text-blue-700"><?= esc($it['instructions']) ?></span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                        <p class="text-gray-500">No medicines listed in this prescription.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Next Appointment -->
            <?php if (!empty($prescription['next_appointment'])): ?>
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Next Appointment
                </h2>
                <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-200">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-lg font-semibold text-indigo-900"><?= date('F d, Y', strtotime($prescription['next_appointment'])) ?></span>
                        <span class="ml-2 text-sm text-indigo-600">(<?= date('l', strtotime($prescription['next_appointment'])) ?>)</span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('actionsMenu');
    const button = event.target.closest('button');
    
    if (!button || !button.onclick) {
        dropdown.classList.add('hidden');
    }
});
</script>

<!-- PDF Preview Modal -->
<div id="pdfPreviewModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full mx-4 overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold">Prescription Preview</h3>
            <div class="flex items-center space-x-2">
                <a id="downloadPdfBtn" href="#" class="inline-flex items-center px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded" target="_blank" rel="noopener">
                    Download PDF
                </a>
                <button onclick="closePdfPreview()" class="inline-flex items-center px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded">
                    Close
                </button>
            </div>
        </div>
        <div id="pdfPreviewBody" class="p-4 max-h-[70vh] overflow-auto bg-gray-50">
            <!-- preview HTML will be injected here -->
        </div>
    </div>
</div>

<script>
function openPdfPreview(id) {
    const modal = document.getElementById('pdfPreviewModal');
    const body = document.getElementById('pdfPreviewBody');
    const downloadBtn = document.getElementById('downloadPdfBtn');

    // Clear previous
    body.innerHTML = '<div class="text-center py-12">Loading preview…</div>';
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    // Fetch HTML-only preview (no layout)
    fetch(`<?= base_url('admin/prescriptions') ?>/${id}/preview`) 
        .then(res => {
            if (!res.ok) throw new Error('Preview fetch failed');
            return res.text();
        })
        .then(html => {
            body.innerHTML = html;
            // set download link to the PDF generation endpoint
            downloadBtn.href = `<?= base_url('admin/prescriptions') ?>/${id}/download-file`;
        })
        .catch(err => {
            body.innerHTML = '<div class="text-center py-12 text-red-600">Failed to load preview.</div>';
            console.error(err);
        });
}

function closePdfPreview() {
    const modal = document.getElementById('pdfPreviewModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.getElementById('pdfPreviewBody').innerHTML = '';
}

// Close modal when clicking backdrop
document.getElementById('pdfPreviewModal').addEventListener('click', function(e) {
    if (e.target === this) closePdfPreview();
});
</script>