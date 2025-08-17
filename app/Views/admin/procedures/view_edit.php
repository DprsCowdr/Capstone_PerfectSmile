<?= view('templates/header') ?>
<div class="max-w-xl mx-auto mt-10 bg-white rounded-xl shadow p-8">
    <h2 class="text-2xl font-bold mb-6 text-indigo-700">Procedure Details</h2>
    <?php if (isset($validation)): ?>
        <div class="mb-4 text-red-600">
            <?= $validation->listErrors() ?>
        </div>
    <?php endif; ?>
    <form id="procedureForm" action="<?= base_url('admin/procedures/update/' . $procedure['id']) ?>" method="post" class="space-y-6">
        <?= csrf_field() ?>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
            <input type="text" name="title" value="<?= esc($procedure['title'] ?? '') ?>" class="w-full border rounded px-3 py-2" 
                <?= ($user['user_type'] !== 'admin') ? 'disabled' : '' ?> readonly>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
            <input type="date" name="procedure_date" value="<?= esc($procedure['procedure_date'] ?? '') ?>" class="w-full border rounded px-3 py-2" required readonly>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <input type="text" name="category" value="<?= esc($procedure['category'] ?? '') ?>" class="w-full border rounded px-3 py-2" 
                <?= ($user['user_type'] !== 'admin') ? 'disabled' : '' ?> readonly>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fee</label>
            <input type="number" step="0.01" name="fee" value="<?= esc($procedure['fee'] ?? '') ?>" class="w-full border rounded px-3 py-2" 
                <?= ($user['user_type'] !== 'admin') ? 'disabled' : '' ?> readonly>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Treatment Area</label>
            <input type="text" name="treatment_area" value="<?= esc($procedure['treatment_area'] ?? '') ?>" class="w-full border rounded px-3 py-2" required readonly>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status" class="w-full border rounded px-3 py-2" required <?= ($user['user_type'] !== 'admin') ? 'disabled' : '' ?> readonly>
                <option value="scheduled" <?= (isset($procedure['status']) && $procedure['status'] === 'scheduled') ? 'selected' : '' ?>>Scheduled</option>
                <option value="in_progress" <?= (isset($procedure['status']) && $procedure['status'] === 'in_progress') ? 'selected' : '' ?>>In Progress</option>
                <option value="completed" <?= (isset($procedure['status']) && $procedure['status'] === 'completed') ? 'selected' : '' ?>>Completed</option>
                <option value="cancelled" <?= (isset($procedure['status']) && $procedure['status'] === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>
        <div class="flex gap-3 mt-4">
            <button type="button" id="editBtn" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">Edit</button>
            <button type="submit" id="saveBtn" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition hidden">Save</button>
            <a href="<?= base_url('admin/procedures') ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">Back</a>
        </div>
    </form>
</div>
<script>
    const editBtn = document.getElementById('editBtn');
    const saveBtn = document.getElementById('saveBtn');
    const form = document.getElementById('procedureForm');
    editBtn.addEventListener('click', function() {
        // Enable all fields for admin, or only allowed for staff
        <?php if ($user['user_type'] === 'admin'): ?>
            form.querySelectorAll('input, select').forEach(el => {
                el.removeAttribute('readonly');
                el.removeAttribute('disabled');
            });
        <?php else: ?>
            form.querySelector('input[name=procedure_date]').removeAttribute('readonly');
            form.querySelector('input[name=treatment_area]').removeAttribute('readonly');
            form.querySelector('select[name=status]').removeAttribute('disabled');
        <?php endif; ?>
        editBtn.classList.add('hidden');
        saveBtn.classList.remove('hidden');
    });
</script>
<?= view('templates/footer') ?>
