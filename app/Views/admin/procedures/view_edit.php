<?php if (!service('request')->isAJAX() && !isset($_GET['modal'])): ?>
<?= view('templates/header') ?>
<div class="max-w-xl mx-auto mt-10 bg-white rounded-xl shadow p-8">
<?php else: ?>
<div class="p-6">
<?php endif; ?>

    <h2 id="procedurePanelTitle" class="text-2xl font-bold mb-6 text-indigo-700">
        <?php 
        $isEditMode = isset($_GET['mode']) && $_GET['mode'] === 'edit';
        echo $isEditMode ? 'Edit Procedure' : 'Procedure Details';
        ?>
    </h2>

    <?php if (isset($validation)): ?>
        <div class="mb-4 text-red-600">
            <?= $validation->listErrors() ?>
        </div>
    <?php endif; ?>

    <form id="procedureForm" 
          action="<?= base_url('admin/procedures/update/' . $procedure['id']) ?>" 
          method="post" 
          class="space-y-6">
        <?= csrf_field() ?>

        <?php $isEditMode = isset($_GET['mode']) && $_GET['mode'] === 'edit'; ?>
        
        <!-- Title -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
            <input type="text" name="title" 
                   value="<?= esc($procedure['title'] ?? '') ?>" 
                   class="w-full border rounded px-3 py-2"
                   <?= $isEditMode ? '' : 'readonly' ?>>
        </div>

        <!-- Date -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
            <input type="date" name="procedure_date" 
                   value="<?= esc($procedure['procedure_date'] ?? '') ?>" 
                   class="w-full border rounded px-3 py-2"
                   required <?= $isEditMode ? '' : 'readonly' ?>>
        </div>

        <!-- Category -->
        <!-- Service (replace category) -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Service</label>
            <select name="service_id" id="service_id" class="w-full border rounded px-3 py-2" <?= $isEditMode ? '' : 'disabled' ?> >
                <option value="">Loading services...</option>
            </select>
        </div>

        <!-- Fee -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fee</label>
            <input type="number" step="0.01" name="fee" 
                   value="<?= esc($procedure['fee'] ?? '') ?>" 
                   class="w-full border rounded px-3 py-2"
                   <?= $isEditMode ? '' : 'readonly' ?>>
        </div>

        <!-- Duration -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Duration (minutes)</label>
            <input type="number" name="duration" min="0" step="1" 
                   value="<?= esc($procedure['duration'] ?? '') ?>"
                   class="w-full border rounded px-3 py-2" <?= $isEditMode ? '' : 'readonly' ?>>
        </div>

        <!-- Treatment Area -->
        <div id="treatment_area_wrapper" style="display: none;">
            <label class="block text-sm font-medium text-gray-700 mb-1">Treatment Area</label>
            <input type="text" name="treatment_area" 
                   value="<?= esc($procedure['treatment_area'] ?? '') ?>" 
                   class="w-full border rounded px-3 py-2"
                   required <?= $isEditMode ? '' : 'readonly' ?>>
        </div>

        <!-- Status -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status" class="w-full border rounded px-3 py-2" required <?= $isEditMode ? '' : 'disabled' ?>>
                <option value="scheduled"  <?= (isset($procedure['status']) && $procedure['status'] === 'scheduled') ? 'selected' : '' ?>>Scheduled</option>
                <option value="in_progress" <?= (isset($procedure['status']) && $procedure['status'] === 'in_progress') ? 'selected' : '' ?>>In Progress</option>
                <option value="completed"  <?= (isset($procedure['status']) && $procedure['status'] === 'completed') ? 'selected' : '' ?>>Completed</option>
                <option value="cancelled"  <?= (isset($procedure['status']) && $procedure['status'] === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>

        <!-- Buttons -->
        <div class="flex gap-3 mt-4">
            <?php $isEditMode = isset($_GET['mode']) && $_GET['mode'] === 'edit'; ?>
            <?php if (!$isEditMode): ?>
            <button type="button" id="editBtn" 
                    class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">
                <i class="fas fa-edit mr-1"></i> Edit
            </button>
            <?php endif; ?>
            <button type="submit" id="saveBtn" 
                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition <?= $isEditMode ? '' : 'hidden' ?>">
                <i class="fas fa-save mr-1"></i> Save
            </button>
            <button type="button" id="cancelBtn" 
                    class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition <?= $isEditMode ? '' : 'hidden' ?>">
                <i class="fas fa-times mr-1"></i> Cancel
            </button>
            <?php if (!isset($_GET['modal'])): ?>
            <a href="<?= base_url('admin/procedures') ?>" 
               class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">
               <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
            <?php endif; ?>
        </div>
    </form>

<?php if (!service('request')->isAJAX() && !isset($_GET['modal'])): ?>
<?= view('templates/footer') ?>
<?php endif; ?>
<script>
// Populate services into the edit/view form and toggle treatment area
(function(){
    const serviceSelect = document.getElementById('service_id');
    const feeField = document.querySelector('input[name="fee"]');
    const durationField = document.querySelector('input[name="duration"]');
    const treatmentWrapper = document.getElementById('treatment_area_wrapper');

    function clearOptions(select){ while(select.firstChild) select.removeChild(select.firstChild); }

    fetch('<?= base_url('checkup/services/all') ?>', { credentials: 'same-origin' })
        .then(r=>r.json())
        .then(json=>{
            clearOptions(serviceSelect);
            const defaultOpt = document.createElement('option'); defaultOpt.value=''; defaultOpt.textContent='Select a service'; serviceSelect.appendChild(defaultOpt);
            if (!json || !json.services) return;
            json.services.forEach(s=>{
                const opt = document.createElement('option'); opt.value = s.id; opt.textContent = s.name + (s.price?` ($${parseFloat(s.price).toFixed(2)})`: '');
                if (s.price !== undefined) opt.dataset.price = s.price;
                if (s.duration !== undefined) opt.dataset.duration = s.duration;
                if (s.treatment_area_required !== undefined) opt.dataset.treatmentAreaRequired = s.treatment_area_required;
                serviceSelect.appendChild(opt);
            });

            // select existing value if present
            const existing = '<?= esc($procedure['service_id'] ?? '') ?>';
            if (existing) { serviceSelect.value = existing; serviceSelect.dispatchEvent(new Event('change')); }
        }).catch(e=>{ clearOptions(serviceSelect); const opt=document.createElement('option'); opt.value=''; opt.textContent='Failed to load'; serviceSelect.appendChild(opt); });

    serviceSelect.addEventListener('change', function(){
        const sel = this.options[this.selectedIndex];
        if (!sel || !sel.value) { if(feeField) feeField.value=''; if(durationField) durationField.value=''; treatmentWrapper.style.display='none'; return; }
        if (sel.dataset.price !== undefined && feeField) feeField.value = parseFloat(sel.dataset.price).toFixed(2);
        if (sel.dataset.duration !== undefined && durationField) durationField.value = sel.dataset.duration;
        const tr = sel.dataset.treatmentAreaRequired;
        if (tr !== undefined && (tr==='1' || tr==='true' || tr==='yes')) {
            treatmentWrapper.style.display='';
        } else {
            treatmentWrapper.style.display='none';
        }
    });
})();
</script>
 