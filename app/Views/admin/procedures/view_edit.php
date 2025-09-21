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
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <input type="text" name="category" 
                   value="<?= esc($procedure['category'] ?? '') ?>" 
                   class="w-full border rounded px-3 py-2"
                   <?= $isEditMode ? '' : 'readonly' ?>>
        </div>

        <!-- Fee -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fee</label>
            <input type="number" step="0.01" name="fee" 
                   value="<?= esc($procedure['fee'] ?? '') ?>" 
                   class="w-full border rounded px-3 py-2"
                   <?= $isEditMode ? '' : 'readonly' ?>>
        </div>

        <!-- Treatment Area -->
        <div>
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
 
