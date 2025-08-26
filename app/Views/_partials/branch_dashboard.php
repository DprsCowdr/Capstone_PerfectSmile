<!-- Branch dashboard partial: cards + chart (shared between staff and admin branch view) -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
    <div class="bg-white border-l-4 border-indigo-400 shadow rounded-lg p-5 flex items-center justify-between">
        <div>
            <div class="text-xs font-bold text-indigo-600 uppercase mb-1">Total Patients</div>
            <div class="text-2xl font-bold text-gray-800"><span id="staff-total-patients"><?= $totalPatients ?? 0 ?></span></div>
        </div>
        <i class="fas fa-users fa-2x text-gray-300"></i>
    </div>
    <div class="bg-white border-l-4 border-green-400 shadow rounded-lg p-5 flex items-center justify-between">
        <div>
            <div class="text-xs font-bold text-green-600 uppercase mb-1">Today's Appointments</div>
            <div class="text-2xl font-bold text-gray-800"><span id="staff-total-today-appointments"><?= count($todayAppointments ?? []) ?></span></div>
        </div>
        <i class="fas fa-calendar fa-2x text-gray-300"></i>
    </div>
    <div class="bg-white border-l-4 border-orange-400 shadow rounded-lg p-5 flex items-center justify-between">
        <div>
            <div class="text-xs font-bold text-orange-600 uppercase mb-1">Pending Approvals</div>
            <div class="text-2xl font-bold text-gray-800"><span id="staff-total-pending-approvals"><?= count($pendingAppointments ?? []) ?></span></div>
        </div>
        <i class="fas fa-clock fa-2x text-gray-300"></i>
    </div>
    <div class="bg-white border-l-4 border-purple-400 shadow rounded-lg p-5 flex items-center justify-between">
        <div>
            <div class="text-xs font-bold text-purple-600 uppercase mb-1">Available Dentists</div>
            <div class="text-2xl font-bold text-gray-800"><span id="staff-total-dentists"><?= $totalDentists ?? 0 ?></span></div>
        </div>
        <i class="fas fa-user-md fa-2x text-gray-300"></i>
    </div>
</div>

<!-- Chart area -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2 bg-white shadow rounded-lg p-4">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center space-x-3">
                <label for="chartSelectorTop" class="text-sm text-gray-600">Metric</label>
                <select id="chartSelectorTop" class="border rounded px-2 py-1 text-sm text-gray-700">
                    <option value="patients">Patients</option>
                    <option value="appointments">Appointments</option>
                    <option value="treatments">Treatments</option>
                </select>
                <input type="hidden" id="statsScope" value="<?= isset($statsScopeValue) ? $statsScopeValue : (isset($selectedBranchId) ? 'branch:'.(int)$selectedBranchId : 'all') ?>">
                <button id="showTotalsHistoryBtn" class="ml-2 px-3 py-1 bg-gray-100 text-sm rounded hover:bg-gray-200">View totals history</button>
            </div>
            <div class="text-sm text-gray-500"></div>
        </div>
        <div class="w-full h-64">
            <canvas id="staffTotalsChart" height="160"></canvas>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-4">
        <div class="mb-4">
            <div class="text-xs font-semibold text-gray-500 uppercase">Average / day</div>
            <div id="avgPerDayTop" class="text-2xl font-bold text-gray-800">—</div>
        </div>
        <div class="mb-4">
            <div class="text-xs font-semibold text-gray-500 uppercase">Peak day</div>
            <div id="peakDayTop" class="text-lg font-semibold text-gray-800">—</div>
        </div>
        <div class="mb-4">
            <div class="text-xs font-semibold text-gray-500 uppercase">Total (latest)</div>
            <div id="patientTotal" class="text-2xl font-bold text-gray-800">—</div>
        </div>
        <div class="mb-4">
            <div class="text-xs font-semibold text-gray-500 uppercase">upcoming appointment</div>
            <div class="text-sm text-gray-700 flex items-center gap-2" id="nextAppointment">
                <span id="nextAppointmentText">—</span>
                <button id="nextAppointmentBadge" class="ml-2 px-2 py-0.5 text-xs rounded bg-gray-100 text-gray-700 hidden" title="Click to refresh stats">Refresh</button>
            </div>
        </div>
        <div class="mb-4">
            <div class="text-xs font-semibold text-gray-500 uppercase">Status</div>
            <div id="statusLegend" class="mt-2 text-sm text-gray-700"></div>
        </div>
        <!-- <div>
            <div class="text-xs font-semibold text-gray-500 uppercase">Recent samples</div>
            <div id="recentValues" class="mt-2 text-sm text-gray-700 max-h-40 overflow-y-auto"></div>
        </div> -->
        <!-- <div id="staffDebugWrap" class="mt-4">
            <div class="text-xs font-semibold text-gray-500 uppercase">Debug (dev only)</div>
            <pre id="staffDebugDump" class="mt-2 p-2 bg-gray-100 text-xs text-gray-800 max-h-48 overflow-auto hidden"></pre>
        </div> -->
    </div>
</div>
