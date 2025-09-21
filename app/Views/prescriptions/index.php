<?php $user = $user ?? session('user') ?? []; ?>
<div data-sidebar-offset>
	<nav class="sticky top-0 bg-white shadow-sm z-20 p-4 border-b border-gray-200">
		<div class="flex justify-between items-center">
			<div class="flex items-center space-x-4">
				<h1 class="text-2xl font-bold text-gray-900">Prescriptions</h1>
				<div class="h-5 w-px bg-gray-300"></div>
				<p class="text-sm text-gray-600">Manage prescriptions issued to patients</p>
			</div>
			<div>
				<a href="<?= base_url('admin/prescriptions/create') ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm">
					New Prescription
				</a>
			</div>
		</div>
	</nav>

	<main class="p-6 bg-gray-50 min-h-screen">
		<div class="max-w-6xl mx-auto">
			<div class="bg-white shadow-sm rounded-xl border border-gray-200 p-4">
				<?php if (!empty(session()->getFlashdata('success'))): ?>
					<div class="mb-4 p-3 rounded bg-green-50 border border-green-200 text-green-700"><?= esc(session()->getFlashdata('success')) ?></div>
				<?php endif; ?>
				<?php if (!empty(session()->getFlashdata('error'))): ?>
					<div class="mb-4 p-3 rounded bg-red-50 border border-red-200 text-red-700"><?= esc(session()->getFlashdata('error')) ?></div>
				<?php endif; ?>

				<?php if (!empty($prescriptions) && is_array($prescriptions)): ?>
					<div class="overflow-x-auto">
						<table class="min-w-full divide-y divide-gray-200">
							<thead class="bg-gray-50">
								<tr>
									<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
									<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
									<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dentist</th>
									<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Issue Date</th>
									<th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
								</tr>
							</thead>
							<tbody class="bg-white divide-y divide-gray-100">
								<?php foreach ($prescriptions as $i => $p): ?>
									<tr class="hover:bg-gray-50" <?= !empty($p['dentist_id']) ? 'data-dentist-id="' . esc($p['dentist_id']) . '"' : '' ?> >
										<td class="px-4 py-3 align-middle text-sm text-gray-700"><?= esc($p['id']) ?></td>
										<td class="px-4 py-3 align-middle text-sm text-gray-800"><?= esc($p['patient_name'] ?? 'Unknown') ?></td>
										<td class="px-4 py-3 align-middle text-sm text-gray-800"><?= esc($p['dentist_name'] ?? 'Unknown') ?></td>
										<td class="px-4 py-3 align-middle text-sm text-gray-700"><?php if (!empty($p['issue_date'])): ?><?= date('M d, Y', strtotime($p['issue_date'])) ?><?php else: ?>-<?php endif; ?></td>

										<td class="px-4 py-3 align-middle text-sm text-right space-x-2">
											<a href="<?= base_url('admin/prescriptions/'.$p['id']) ?>" class="inline-flex items-center px-3 py-1 text-sm bg-white border border-gray-200 rounded hover:bg-gray-50">View</a>
											<a href="<?= base_url('admin/prescriptions/'.$p['id'].'/edit') ?>" class="inline-flex items-center px-3 py-1 text-sm bg-white border border-gray-200 rounded hover:bg-gray-50">Edit</a>
											<a href="<?= base_url('admin/prescriptions/'.$p['id'].'/preview') ?>" class="inline-flex items-center px-3 py-1 text-sm bg-white border border-gray-200 rounded hover:bg-gray-50">Preview</a>
											<a href="<?= base_url('admin/prescriptions/'.$p['id'].'/download-file') ?>" class="inline-flex items-center px-3 py-1 text-sm bg-white border border-gray-200 rounded hover:bg-gray-50" target="_blank">Download</a>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php else: ?>
					<div class="text-center py-12">
						<svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
						</svg>
						<p class="text-gray-500">No prescriptions found.</p>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</main>
</div>
