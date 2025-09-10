<?php /** @var array $branches */ ?>
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-violet-50 to-emerald-50 p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100 mb-8">
            <div class="px-8 py-6 border-b border-purple-100/50">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-purple-600 via-violet-600 to-emerald-600 bg-clip-text text-transparent">
                            Branch Management
                        </h1>
                        <p class="text-slate-600 mt-1">Manage your branch locations and information</p>
                    </div>
                    <a href="<?= site_url('admin/branches/create') ?>" 
                       class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-violet-500 hover:from-purple-600 hover:to-violet-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        New Branch
                    </a>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="px-8 py-4 border-b border-purple-100/50 bg-gradient-to-r from-purple-25 to-emerald-25">
                <form method="get" class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    <!-- Search by name -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Search Branch</label>
                        <div class="relative">
                            <input type="text" name="search" value="<?= esc($filters['search'] ?? '') ?>" 
                                   placeholder="Search by name..."
                                   class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Filter by status -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">All Status</option>
                            <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>

                    <!-- Filter by city -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">City</label>
                        <input type="text" name="city" value="<?= esc($filters['city'] ?? '') ?>" 
                               placeholder="Filter by city..."
                               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <!-- Filter buttons -->
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white font-medium rounded-lg transition-colors">
                            Filter
                        </button>
                        <a href="<?= site_url('admin/branches') ?>" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium rounded-lg transition-colors">
                            Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Content Section -->
            <div class="p-8">
                <!-- Flash messages (success / error) -->
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800">
                        <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-800">
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>
                <?php if (empty($branches)): ?>
                    <div class="text-center py-16">
                        <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-purple-100 to-emerald-100 rounded-full flex items-center justify-center">
                            <svg class="w-12 h-12 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-slate-700 mb-2">No branches found</h3>
                        <p class="text-slate-500">Get started by creating your first branch location.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-hidden rounded-xl border border-purple-100">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="bg-gradient-to-r from-purple-50 to-emerald-50 border-b border-purple-100">
                                        <th class="text-left py-4 px-6 font-semibold text-slate-700">Branch Name</th>
                                        <th class="text-left py-4 px-6 font-semibold text-slate-700">Address</th>
                                        <th class="text-left py-4 px-6 font-semibold text-slate-700">Contact</th>
                                        <th class="text-center py-4 px-6 font-semibold text-slate-700">Status</th>
                                        <th class="text-center py-4 px-6 font-semibold text-slate-700">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-purple-50">
                                    <?php foreach ($branches as $i => $b): ?>
                                        <tr class="hover:bg-gradient-to-r hover:from-purple-25 hover:to-emerald-25 transition-all duration-200">
                                            <td class="py-4 px-6">
                                                <div class="font-semibold text-slate-800"><?= esc($b['name']) ?></div>
                                            </td>
                                            <td class="py-4 px-6">
                                                <div class="text-slate-600 max-w-xs truncate" title="<?= esc($b['address'] ?? '') ?>">
                                                    <?= esc($b['address'] ?? 'N/A') ?>
                                                </div>
                                            </td>
                                            <td class="py-4 px-6">
                                                <div class="text-slate-600">
                                                    <div><?= esc($b['contact_number'] ?? 'N/A') ?></div>
                                                    <div class="text-xs text-slate-400"><?= !empty($b['email']) ? esc($b['email']) : '' ?></div>
                                                </div>
                                            </td>
                                            <td class="py-4 px-6 text-center">
                                                <?php 
                                                $status = $b['status'] ?? 'active';
                                                $statusClasses = $status === 'active' 
                                                    ? 'bg-emerald-100 text-emerald-800 border-emerald-200' 
                                                    : 'bg-red-100 text-red-800 border-red-200';
                                                ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border <?= $statusClasses ?>">
                                                    <div class="w-1.5 h-1.5 rounded-full mr-1 <?= $status === 'active' ? 'bg-emerald-500' : 'bg-red-500' ?>"></div>
                                                    <?= ucfirst($status) ?>
                                                </span>
                                            </td>
                                            <!-- created_at moved to show view -->
                                            <td class="py-4 px-6">
                                                <div class="flex items-center justify-center gap-1">
                                                    <a href="<?= site_url('admin/branches/'.$b['id']) ?>" 
                                                       class="inline-flex items-center px-2 py-1.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-medium rounded-lg transition-colors duration-200 text-sm">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                        </svg>
                                                        View
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination (if needed) -->
                    <?php if (isset($pager)): ?>
                        <div class="mt-6 flex justify-center">
                            <?= $pager->links() ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>