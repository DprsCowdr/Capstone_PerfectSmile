<!-- Tailwind Procedures Table -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
    <h1 class="font-bold text-2xl md:text-3xl text-black tracking-tight">Lists of Procedures</h1>
    <?php if (in_array($user['user_type'], ['admin', 'staff'])): ?>
        <a href="<?= base_url('admin/procedures/create') ?>" class="bg-[#c7aefc] hover:bg-[#a47be5] text-white font-bold text-base rounded-xl shadow px-7 py-2.5 transition">+ Add New Procedure</a>
    <?php endif; ?>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="flex items-center gap-2 bg-green-100 text-green-800 rounded-lg px-4 py-3 mb-4 text-sm font-semibold">
        <i class="fas fa-check-circle"></i>
        <span><?= session()->getFlashdata('success') ?></span>
        <button type="button" class="ml-auto text-green-700 hover:text-green-900 focus:outline-none" onclick="this.parentElement.remove()">&times;</button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="flex items-center gap-2 bg-red-100 text-red-800 rounded-lg px-4 py-3 mb-4 text-sm font-semibold">
        <i class="fas fa-exclamation-circle"></i>
        <span><?= esc(session()->getFlashdata('error')) ?></span>
        <button type="button" class="ml-auto text-red-700 hover:text-red-900 focus:outline-none" onclick="this.parentElement.remove()">&times;</button>
    </div>
<?php endif; ?>

<!-- Desktop Table View -->
<div class="hidden lg:block overflow-x-auto mb-8">
    <table class="min-w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        <thead class="bg-white">
            <tr class="text-black font-extrabold text-base">
                <th class="px-8 py-4 text-left">Title</th>
                <th class="px-4 py-4 text-left">Date</th>
                <th class="px-4 py-4 text-left">Category</th>
                <th class="px-4 py-4 text-left">Fee</th>
                <th class="px-4 py-4 text-left">Treatment Area</th>
                <th class="px-4 py-4 text-left">Status</th>
                <th class="px-4 py-4 text-left">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($procedures)): ?>
                <?php foreach ($procedures as $procedure): ?>
                <tr class="border-b last:border-b-0 hover:bg-indigo-50 transition">
                    <td class="min-w-[180px] px-8 py-5">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center font-bold text-lg text-indigo-400">
                                <i class="fas fa-procedures"></i>
                            </div>
                            <div>
                                <div class="font-extrabold text-black text-base"><?= esc($procedure['title'] ?? $procedure['procedure_name']) ?></div>
                                <div class="text-sm text-gray-600"><?= esc($procedure['patient_name'] ?? 'Unknown Patient') ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="font-bold text-black px-4 py-5">
                        <div class="text-sm">
                            <?= $procedure['procedure_date'] ? date('d M Y', strtotime($procedure['procedure_date'])) : 'Not set' ?>
                        </div>
                        <div class="text-xs text-gray-600">
                            <?= $procedure['procedure_date'] ? date('g:i A', strtotime($procedure['procedure_date'])) : '' ?>
                        </div>
                    </td>
                    <td class="text-black px-4 py-5"><?= esc($procedure['category'] ?? 'none') ?></td>
                    <td class="text-black px-4 py-5 font-bold">$<?= number_format($procedure['fee'] ?? 0, 2) ?></td>
                    <td class="text-black px-4 py-5"><?= esc($procedure['treatment_area'] ?? 'Surface') ?></td>
                    <td class="px-4 py-5">
                        <?php 
                        $status = $procedure['status'] ?? 'scheduled';
                        $statusClasses = [
                            'scheduled' => 'bg-blue-100 text-blue-700',
                            'in_progress' => 'bg-yellow-100 text-yellow-700',
                            'completed' => 'bg-green-100 text-green-700',
                            'cancelled' => 'bg-red-100 text-red-700'
                        ];
                        $statusClass = $statusClasses[$status] ?? 'bg-gray-100 text-gray-700';
                        ?>
                        <span class="inline-block font-semibold rounded-md px-3 py-1 text-xs <?= $statusClass ?>">
                            <?= ucfirst(str_replace('_', ' ', $status)) ?>
                        </span>
                    </td>
                    <td class="px-6 py-5">
                        <div class="flex justify-end gap-2">
                            <?php if (in_array($user['user_type'], ['admin', 'staff'])): ?>
                            <button onclick="openProcedureModal(<?= $procedure['id'] ?>, 'view')" title="View" class="p-2 text-blue-400 hover:bg-blue-50 rounded-lg transition">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="openProcedureModal(<?= $procedure['id'] ?>, 'edit')" title="Edit" class="p-2 text-green-400 hover:bg-green-50 rounded-lg transition">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteProcedure(<?= $procedure['id'] ?>)" title="Delete" class="p-2 text-red-400 hover:bg-red-50 rounded-lg transition">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center py-12 text-black font-semibold">No procedures found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Mobile Card View -->
<div class="lg:hidden space-y-4 mb-8">
    <?php if (!empty($procedures)): ?>
        <?php foreach ($procedures as $procedure): ?>
    <div class="bg-white rounded-2xl shadow-xl p-4 border border-gray-100 cursor-pointer" onclick="openProcedureModal(<?= $procedure['id'] ?>)">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center font-bold text-lg text-indigo-400">
                        <i class="fas fa-procedures"></i>
                    </div>
                    <div>
                        <div class="font-bold text-black text-base"><?= esc($procedure['title'] ?? $procedure['procedure_name']) ?></div>
                        <div class="text-sm text-gray-600"><?= esc($procedure['patient_name'] ?? 'Unknown Patient') ?></div>
                    </div>
                </div>
                <?php 
                $status = $procedure['status'] ?? 'scheduled';
                $statusClasses = [
                    'scheduled' => 'bg-blue-100 text-blue-700',
                    'in_progress' => 'bg-yellow-100 text-yellow-700',
                    'completed' => 'bg-green-100 text-green-700',
                    'cancelled' => 'bg-red-100 text-red-700'
                ];
                $statusClass = $statusClasses[$status] ?? 'bg-gray-100 text-gray-700';
                ?>
                <span class="inline-block font-semibold rounded-md px-2 py-1 text-xs <?= $statusClass ?>">
                    <?= ucfirst(str_replace('_', ' ', $status)) ?>
                </span>
            </div>
            
            <div class="space-y-2 mb-4">
                <div class="flex items-center gap-2 text-sm">
                    <i class="fas fa-calendar text-gray-400 w-4"></i>
                    <span class="text-black">
                        <?= $procedure['procedure_date'] ? date('d M Y', strtotime($procedure['procedure_date'])) : 'Not set' ?>
                    </span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <i class="fas fa-tag text-gray-400 w-4"></i>
                    <span class="text-black"><?= esc($procedure['category'] ?? 'none') ?></span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <i class="fas fa-dollar-sign text-gray-400 w-4"></i>
                    <span class="text-black font-bold">$<?= number_format($procedure['fee'] ?? 0, 2) ?></span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <i class="fas fa-map-marker-alt text-gray-400 w-4"></i>
                    <span class="text-black"><?= esc($procedure['treatment_area'] ?? 'Surface') ?></span>
                </div>
            </div>
            
            <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                <?php if (in_array($user['user_type'], ['admin', 'staff'])): ?>
                <button onclick="openProcedureModal(<?= $procedure['id'] ?>, 'view')" title="View" class="p-2 text-blue-400 hover:bg-blue-50 rounded-lg transition">
                    <i class="fas fa-eye"></i>
                </button>
                <button onclick="openProcedureModal(<?= $procedure['id'] ?>, 'edit')" title="Edit" class="p-2 text-green-400 hover:bg-green-50 rounded-lg transition">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="deleteProcedure(<?= $procedure['id'] ?>)" title="Delete" class="p-2 text-red-400 hover:bg-red-50 rounded-lg transition">
                    <i class="fas fa-trash"></i>
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="text-center py-12 text-black font-semibold bg-white rounded-2xl shadow-xl">
            <i class="fas fa-procedures text-4xl mb-4 block"></i>
            No procedures found.
        </div>
    <?php endif; ?>
</div>

<!-- Scripts -->
<script>
function deleteProcedure(id) {
    const confirmDelete = () => {
        fetch('<?= base_url('admin/procedures/delete/') ?>' + id, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                if (typeof showInvoiceAlert === 'function') showInvoiceAlert('Error: ' + (data.message || ''), 'error', 4000); else alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof showInvoiceAlert === 'function') showInvoiceAlert('An error occurred while deleting the procedure.', 'error', 4000); else alert('An error occurred while deleting the procedure.');
        });
    };

    if (typeof showPrompt === 'function') {
        showPrompt('Are you sure you want to delete this procedure?', '', '').then(result => {
            if (result !== null) confirmDelete();
        });
    } else {
        if (confirm('Are you sure you want to delete this procedure?')) {
            confirmDelete();
        }
    }
}

// Modal setup (only once)
if (!document.getElementById('procedureModal')) {
    const modalHtml = `
    <div id="procedureModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black bg-opacity-40">
        <div class="modal-panel bg-white rounded-xl shadow-xl max-w-sm w-full p-0 relative animate-fade-in" style="min-width:320px;max-width:95vw;">
            <button id="closeProcedureModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl font-bold">&times;</button>
            <div id="procedureModalContent"></div>
        </div>
    </div>`;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function openProcedureModal(id, mode = 'view') {
    const modal = document.getElementById('procedureModal');
    const content = document.getElementById('procedureModalContent');
    content.innerHTML = '<div class="flex items-center justify-center h-32"><i class="fas fa-spinner fa-spin text-2xl text-blue-500"></i></div>';
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    const panel = modal.querySelector('.modal-panel');
    if (panel) {
        panel.style.maxWidth = '420px';
        panel.style.width = '95vw';
        panel.style.padding = '0';
    }

    <?php
        // determine show url based on user type so the same template works for admin and dentist
        $procedureShowBase = (isset($user['user_type']) && $user['user_type'] === 'admin') ? base_url('admin/procedures/show/') : base_url('dentist/procedures/');
    ?>
    fetch('<?= $procedureShowBase ?>' + id + '?modal=1&mode=' + mode)
        .then(res => res.text())
        .then(html => {
            content.innerHTML = html;
            // Re-initialize edit button after modal content is loaded if in view mode
            if (mode === 'view') {
                initProcedureEditBtn();
            }
        })
        .catch(() => {
            content.innerHTML = '<div class="text-center py-8 text-red-600">Failed to load procedure details.</div>';
        });
}

// Modal close
document.addEventListener('click', function(e) {
    if (e.target && e.target.id === 'closeProcedureModal') {
        const modal = document.getElementById('procedureModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    
    // Close modal when clicking outside
    if (e.target && e.target.id === 'procedureModal') {
        const modal = document.getElementById('procedureModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
});

// Edit form toggler for view mode
function initProcedureEditBtn() {
    const editBtn = document.getElementById("editBtn");
    const saveBtn = document.getElementById("saveBtn");
    const cancelBtn = document.getElementById("cancelBtn");
    const form = document.getElementById("procedureForm");

    if (!editBtn) {
        console.log("[EditBtn] Edit button not found in DOM");
        return;
    }

    console.log("[EditBtn] Edit button found");
    editBtn.addEventListener("click", function () {
        if (!form) {
            console.log("[EditBtn] Form not found when clicking edit");
            return;
        }
        // Enable all inputs
        form.querySelectorAll("input, select, textarea").forEach(el => {
            el.removeAttribute("readonly");
            el.removeAttribute("disabled");
        });
        console.log("[EditBtn] All form fields enabled for editing");

        // Toggle buttons
        editBtn.classList.add("hidden");
        if (saveBtn) saveBtn.classList.remove("hidden");
        if (cancelBtn) cancelBtn.classList.remove("hidden");
        console.log("[EditBtn] Edit button hidden, Save/Cancel buttons shown");
    });

    // Cancel button functionality
    if (cancelBtn) {
        cancelBtn.addEventListener("click", function () {
            // Disable all inputs
            form.querySelectorAll("input, select, textarea").forEach(el => {
                el.setAttribute("readonly", true);
            });

            // Toggle buttons back
            editBtn.classList.remove("hidden");
            saveBtn.classList.add("hidden");
            cancelBtn.classList.add("hidden");
            
            // Reload modal content to reset form
            const procedureId = form.action.split('/').pop();
            openProcedureModal(procedureId, 'view');
        });
    }

    // Handle form submission for edit mode
    if (form && saveBtn) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            const procedureId = form.action.split('/').pop();
            
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal and reload page to show updated data
                    const modal = document.getElementById('procedureModal');
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    location.reload();
                } else {
                    if (typeof showInvoiceAlert === 'function') {
                        showInvoiceAlert('Error: ' + (data.message || 'Failed to update procedure'), 'error', 4000);
                    } else {
                        alert('Error: ' + (data.message || 'Failed to update procedure'));
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof showInvoiceAlert === 'function') {
                    showInvoiceAlert('An error occurred while updating the procedure.', 'error', 4000);
                } else {
                    alert('An error occurred while updating the procedure.');
                }
            });
        });
    }
}

document.addEventListener("DOMContentLoaded", function () {
    initProcedureEditBtn();
});

// ESC key to close modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('procedureModal');
        if (modal && !modal.classList.contains('hidden')) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }
});
</script>
