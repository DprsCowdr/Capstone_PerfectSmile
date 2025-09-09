<?php /** @var array $branch */ ?>
<?php helper('branch'); // load branch helper functions ?>
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-violet-50 to-emerald-50 p-6">
    <div class="max-w-6xl mx-auto">
        <!-- Header Section -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-violet-500 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold bg-gradient-to-r from-purple-600 via-violet-600 to-emerald-600 bg-clip-text text-transparent">
                                <?= esc($branch['name']) ?>
                            </h1>
                            <?php 
                            $status = $branch['status'] ?? 'active';
                            $statusClasses = $status === 'active' 
                                ? 'bg-emerald-100 text-emerald-800 border-emerald-200' 
                                : 'bg-red-100 text-red-800 border-red-200';
                            ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border <?= $statusClasses ?> mt-1">
                                <div class="w-2 h-2 rounded-full mr-2 <?= $status === 'active' ? 'bg-emerald-500' : 'bg-red-500' ?>"></div>
                                <?= ucfirst($status) ?>
                            </span>
                            <?php if (isset($branch['created_at'])): ?>
                                <div class="text-sm text-slate-500 mt-2">Created: <?= date('M d, Y \a\t g:i A', strtotime($branch['created_at'])) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <p class="text-slate-600">Branch information and management dashboard</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="<?= site_url('admin/branches') ?>" 
                       class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to List
                    </a>
                    <a href="<?= site_url('admin/branches/'.$branch['id'].'/edit') ?>" 
                       class="inline-flex items-center px-4 py-2 bg-purple-100 hover:bg-purple-200 text-purple-700 font-semibold rounded-xl transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Branch
                    </a>
              <form method="post" action="<?= site_url('admin/branches/delete/'.$branch['id']) ?>" 
                  data-confirm="Delete this branch? This cannot be undone." class="inline">
                        <?= csrf_field() ?>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 font-semibold rounded-xl transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Activity Snapshot Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Appointments Today -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-purple-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Appointments Today</p>
                        <p class="text-3xl font-bold text-purple-600"><?= $analytics['appointments_today'] ?? '12' ?></p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-r from-purple-100 to-violet-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Active Patients -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-emerald-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Active Patients</p>
                        <p class="text-3xl font-bold text-emerald-600"><?= $analytics['active_patients'] ?? '248' ?></p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-r from-emerald-100 to-green-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Treatment Total -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Treatment Total</p>
                        <p class="text-3xl font-bold text-blue-600"><?= number_format($analytics['treatment_total'] ?? 0, 0) ?></p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-100 to-indigo-100 rounded-xl flex items-center justify-center">
                        <!-- Tooth icon to represent treatments instead of a dollar sign -->
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8c-1.5-.5-3.5-.5-5 0-1 .38-1.88 1.12-2.5 2-.62-.88-1.5-1.62-2.5-2C6.5 7.5 4.5 7.5 3 8c-1 3 1 6 4 8 1 0 1.5 2 2.5 2s1.5-2 2.5-2 1.5 2 2.5 2c3-2 5-5 4-8z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Staff Count -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-violet-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Staff Members</p>
                        <p class="text-3xl font-bold text-violet-600"><?= count($staff ?? []) ?></p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-r from-violet-100 to-purple-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Branch Details & Operating Hours -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Branch Profile Card -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100">
                    <div class="p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-violet-500 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-slate-800">Branch Information</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Address -->
                            <div class="bg-gradient-to-br from-purple-50 to-violet-50 rounded-xl p-6 border border-purple-100">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-violet-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-slate-800">Address</h4>
                                </div>
                                <p class="text-slate-600 leading-relaxed">
                                    <?= esc($branch['address'] ?? 'No address provided') ?>
                                </p>
                            </div>

                            <!-- Contact Information -->
                            <div class="bg-gradient-to-br from-emerald-50 to-green-50 rounded-xl p-6 border border-emerald-100">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-8 h-8 bg-gradient-to-r from-emerald-500 to-green-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-slate-800">Contact</h4>
                                </div>
                                <div class="space-y-2">
                                    <p class="text-slate-600">
                                        <span class="font-medium">Phone:</span> <?= esc($branch['contact_number'] ?? 'Not provided') ?>
                                    </p>
                                    <?php if (!empty($branch['email'])): ?>
                                    <p class="text-slate-600">
                                        <span class="font-medium">Email:</span> <?= esc($branch['email']) ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Branch metadata moved: created_at shown under title for better visibility -->
                    </div>
                </div>

                <!-- Operating Hours -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100">
                    <div class="p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-gradient-to-r from-emerald-500 to-green-500 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-slate-800">Operating Hours</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php 
                            $normalizedHours = normalizeOperatingHours($branch);
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            $currentDay = date('l');
                            foreach ($days as $day): 
                                $dayLower = strtolower($day);
                                $isEnabled = $normalizedHours[$dayLower]['enabled'] ?? true;
                                $openTime = $normalizedHours[$dayLower]['open'] ?? '09:00';
                                $closeTime = $normalizedHours[$dayLower]['close'] ?? '17:00';
                                $isToday = $day === $currentDay;
                            ?>
                            <div class="flex items-center justify-between p-4 rounded-xl border <?= $isToday ? 'bg-gradient-to-r from-purple-50 to-violet-50 border-purple-200' : 'bg-slate-50 border-slate-200' ?>">
                                <div class="flex items-center gap-3">
                                    <?php if ($isToday): ?>
                                        <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                                    <?php endif; ?>
                                    <span class="font-medium text-slate-800 <?= $isToday ? 'text-purple-800' : '' ?>">
                                        <?= $day ?>
                                    </span>
                                </div>
                                <div class="text-right">
                                    <?php if ($isEnabled): ?>
                                        <span class="text-slate-600 <?= $isToday ? 'text-purple-700 font-medium' : '' ?>">
                                            <?= date('g:i A', strtotime($openTime)) ?> - <?= date('g:i A', strtotime($closeTime)) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-red-500 font-medium">Closed</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Staff & Notifications -->
            <div class="space-y-8">
                <!-- Staff Section -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-r from-violet-500 to-purple-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-slate-800">Staff Members</h3>
                            </div>
                            <a href="<?= site_url('admin/branches/'.$branch['id'].'/staff/assign') ?>" 
                               class="inline-flex items-center px-3 py-2 bg-purple-100 hover:bg-purple-200 text-purple-700 font-medium rounded-lg transition-colors duration-200 text-sm">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Assign Staff
                            </a>
                        </div>

                        <?php if (!empty($staff)): ?>
                            <div class="space-y-3">
                                <?php foreach ($staff as $s): ?>
                                    <div class="bg-gradient-to-br from-slate-50 to-purple-50 rounded-xl p-4 border border-slate-200 hover:border-purple-200 transition-colors duration-200">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-gradient-to-r from-slate-400 to-slate-500 rounded-full flex items-center justify-center text-white font-semibold">
                                                <?= strtoupper(substr($s['user_id'], 0, 1)) ?>
                                            </div>
                                            <div class="flex-1">
                                                <div class="font-medium text-slate-800">
                                                    <?= esc($s['user_id']) ?>
                                                </div>
                                                <div class="text-sm text-slate-500 capitalize">
                                                    <?= esc($s['position'] ?? 'staff') ?>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <?php $uid = is_numeric($s['user_id']) ? (int)$s['user_id'] : null; ?>
                                                <?php if ($uid): ?>
                                                    <a href="<?= base_url('admin/users/edit/' . $uid) ?>" class="p-1 text-slate-400 hover:text-purple-600 transition-colors" title="Edit user">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                    </a>
                                                <?php else: ?>
                                                    <!-- If user_id isn't numeric, link to users edit search page -->
                                                    <a href="<?= base_url('admin/users?search=' . urlencode($s['user_id'])) ?>" class="p-1 text-slate-400 hover:text-purple-600 transition-colors" title="Find user">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-purple-100 to-violet-100 rounded-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <h4 class="text-lg font-medium text-slate-700 mb-2">No Staff Assigned</h4>
                                <p class="text-sm text-slate-500 mb-4">Get started by assigning staff members to this branch.</p>
                                <a href="<?= site_url('admin/branches/'.$branch['id'].'/staff/assign') ?>" 
                                   class="inline-flex items-center px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white font-medium rounded-lg transition-colors duration-200">
                                    Assign Staff
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Notifications/Announcements -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-purple-100">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM10 17H5l5 5v-5zM15 7h5L15 2v5zM10 7H5l5-5v5z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-slate-800">Recent Activity</h3>
                        </div>

                        <div class="space-y-4">
                            <?php 
                            $notifications = getNotifications($branch, $notifications ?? null);
                            ?>
                            <?php foreach ($notifications as $notification): ?>
                                <div class="flex items-start gap-3 p-3 rounded-lg bg-gradient-to-r from-slate-50 to-blue-50 border border-slate-200">
                                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <?php if ($notification['type'] === 'appointment'): ?>
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        <?php elseif ($notification['type'] === 'staff'): ?>
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        <?php else: ?>
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-slate-800 font-medium"><?= esc($notification['message']) ?></p>
                                        <p class="text-xs text-slate-500 mt-1"><?= esc($notification['time']) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>