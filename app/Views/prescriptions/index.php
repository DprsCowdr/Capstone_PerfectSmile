<?php $user = $user ?? session('user') ?? []; ?>
<div data-sidebar-offset>
    <nav class="sticky top-0 bg-white shadow-sm z-20 p-4 border-b border-gray-200 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Prescriptions</h1>
        <a href="<?= base_url('admin/prescriptions/create') ?>" 
           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Prescription
        </a>
    </nav>

    <main class="p-6 bg-gray-50 min-h-screen">
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <?php if (!empty($prescriptions)): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="p-4 font-semibold text-gray-900">#</th>
                            <th class="p-4 font-semibold text-gray-900">Patient</th>
                            <th class="p-4 font-semibold text-gray-900">Dentist</th>
                            <th class="p-4 font-semibold text-gray-900">Issue Date</th>
                            <th class="p-4 font-semibold text-gray-900">Status</th>
                            <th class="p-4 font-semibold text-gray-900 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($prescriptions as $p): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="p-4 font-medium text-gray-900"><?= esc($p['id']) ?></td>
                            <td class="p-4 text-gray-700"><?= esc($p['patient_name'] ?? 'Unknown') ?></td>
                            <td class="p-4 text-gray-700"><?= esc($p['dentist_name'] ?? 'Unknown') ?></td>
                            <td class="p-4 text-gray-600"><?= date('M d, Y', strtotime($p['issue_date'])) ?></td>
                            <td class="p-4">
                                <?php if (!empty($p['status'])): ?>
                                    <?php if ($p['status'] === 'final'): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                            <div class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></div>
                                            Final
                                        </span>
                                    <?php elseif ($p['status'] === 'draft'): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                                            <div class="w-1.5 h-1.5 bg-yellow-500 rounded-full mr-1.5"></div>
                                            Draft
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                            <div class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1.5"></div>
                                            Cancelled
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                        <div class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-1.5"></div>
                                        N/A
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex justify-end items-center space-x-2">
                                    <a href="<?= base_url('admin/prescriptions/'.$p['id']) ?>" 
                                       class="inline-flex items-center px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-medium rounded-md transition-colors duration-200 border border-blue-200 hover:border-blue-300">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View
                                    </a>
                                    
                                    <a href="<?= base_url('admin/prescriptions/'.$p['id'].'/download') ?>" 
                                       class="inline-flex items-center px-3 py-1.5 bg-gray-50 hover:bg-gray-100 text-gray-700 text-xs font-medium rounded-md transition-colors duration-200 border border-gray-200 hover:border-gray-300">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        PDF
                                    </a>
                                    
                                    <form method="post" action="<?= base_url('admin/prescriptions/'.$p['id']) ?>" 
                                          onsubmit="return confirm('Are you sure you want to delete this prescription? This action cannot be undone.')" 
                                          class="inline">
                                        <input type="hidden" name="_method" value="DELETE" />
                                        <button type="submit" 
                                                class="inline-flex items-center px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-medium rounded-md transition-colors duration-200 border border-red-200 hover:border-red-300">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No prescriptions found</h3>
                <p class="text-gray-500 mb-6">Get started by creating your first prescription.</p>
                <a href="<?= base_url('admin/prescriptions/create') ?>" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create Prescription
                </a>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>