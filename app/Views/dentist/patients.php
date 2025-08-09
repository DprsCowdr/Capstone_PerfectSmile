<?= view('templates/header') ?>
<div class="min-h-screen bg-white flex">
    <?= view('templates/sidebar', ['user' => $user]) ?>
    <div class="flex-1 flex flex-col min-h-screen bg-white">
        <main class="flex-1 px-6 py-8 bg-white">
            <?= view('templates/patientsTable', ['patients' => $patients, 'user' => $user]) ?>
        </main>
    </div>
</div>
<?= view('templates/footer') ?>
                        <div>
                            <a href="<?= base_url('dentist/dashboard') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>

                    <!-- Search Bar -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form method="GET" action="<?= base_url('dentist/patients/search') ?>" class="form-inline">
                                <div class="input-group" style="width: 100%; max-width: 400px;">
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Search patients by name, email, or phone..." 
                                           value="<?= isset($searchTerm) ? esc($searchTerm) : '' ?>">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php if (isset($searchTerm)): ?>
                                <a href="<?= base_url('dentist/patients') ?>" class="btn btn-outline-secondary ml-2">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>

                    <!-- Results Summary -->
                    <?php if (isset($searchTerm)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        Found <?= count($patients) ?> patient(s) matching "<?= esc($searchTerm) ?>"
                    </div>
                    <?php endif; ?>

                    <!-- Patients Grid -->
                    <?php if (!empty($patients)): ?>
                    <div class="row">
                        <?php foreach ($patients as $patient): ?>
                        <div class="col-xl-6 col-lg-6 col-md-12 mb-4">
                            <div class="card patient-card h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <!-- Patient Info -->
                                            <div class="row mb-3">
                                                <div class="col-md-8">
                                                    <h5 class="font-weight-bold text-primary mb-1">
                                                        <?= esc($patient['name']) ?>
                                                    </h5>
                                                    <p class="text-sm text-gray-600 mb-1">
                                                        <i class="fas fa-envelope mr-1"></i><?= esc($patient['email']) ?>
                                                    </p>
                                                    <p class="text-sm text-gray-600 mb-1">
                                                        <i class="fas fa-phone mr-1"></i><?= esc($patient['phone']) ?>
                                                    </p>
                                                    <p class="text-sm text-gray-600 mb-0">
                                                        <i class="fas fa-birthday-cake mr-1"></i>
                                                        <?php if ($patient['age']): ?>
                                                            <?= $patient['age'] ?> years old
                                                        <?php else: ?>
                                                            Age not specified
                                                        <?php endif; ?>
                                                        <?php if ($patient['gender']): ?>
                                                            • <?= ucfirst($patient['gender']) ?>
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                                <div class="col-md-4 text-right">
                                                    <span class="badge badge-<?= $patient['status'] === 'active' ? 'success' : 'secondary' ?> status-badge">
                                                        <?= ucfirst($patient['status']) ?>
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Patient Statistics -->
                                            <div class="row border-top pt-3">
                                                <div class="col stat-item">
                                                    <div class="stat-number"><?= $patient['total_appointments'] ?? 0 ?></div>
                                                    <div class="stat-label">Appointments</div>
                                                </div>
                                                <div class="col stat-item">
                                                    <div class="stat-number"><?= $patient['total_records'] ?? 0 ?></div>
                                                    <div class="stat-label">Records</div>
                                                </div>
                                                <div class="col stat-item">
                                                    <div class="stat-number">
                                                        <?php if (!empty($patient['last_appointment'])): ?>
                                                            <?= date('M j', strtotime($patient['last_appointment'])) ?>
                                                        <?php else: ?>
                                                            Never
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="stat-label">Last Visit</div>
                                                </div>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="row mt-3">
                                                <div class="col">
                                                    <div class="btn-group btn-group-sm w-100" role="group">
                                                        <a href="<?= base_url('dentist/patients/' . $patient['id']) ?>" 
                                                           class="btn btn-primary">
                                                            <i class="fas fa-eye mr-1"></i>View Details
                                                        </a>
                                                        <a href="<?= base_url('dentist/patient-records/' . $patient['id']) ?>" 
                                                           class="btn btn-info">
                                                            <i class="fas fa-clipboard-list mr-1"></i>Records
                                                        </a>
                                                        <?php if (!empty($patient['last_appointment_id'])): ?>
                                                        <a href="<?= base_url('dentist/dental-chart/' . $patient['last_appointment_id']) ?>" 
                                                           class="btn btn-success">
                                                            <i class="fas fa-tooth mr-1"></i>Chart
                                                        </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination would go here if needed -->
                    
                    <?php else: ?>
                    <!-- No Patients Found -->
                    <div class="card shadow">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Patients Found</h5>
                            <?php if (isset($searchTerm)): ?>
                                <p class="text-muted">No patients match your search criteria.</p>
                                <a href="<?= base_url('dentist/patients') ?>" class="btn btn-primary">
                                    <i class="fas fa-list mr-2"></i>View All Patients
                                </a>
                            <?php else: ?>
                                <p class="text-muted">No patients are currently registered in the system.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Core JavaScript -->
    <script src="<?= base_url('vendor/jquery/jquery.min.js') ?>"></script>
    <script src="<?= base_url('vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('js/sb-admin-2.min.js') ?>"></script>

    <script>
        // Add some interactive features
        $(document).ready(function() {
            // Highlight search results
            <?php if (isset($searchTerm)): ?>
            var searchTerm = "<?= esc($searchTerm) ?>";
            $('.patient-card').each(function() {
                var cardText = $(this).text();
                if (cardText.toLowerCase().indexOf(searchTerm.toLowerCase()) !== -1) {
                    $(this).addClass('border-warning');
                }
            });
            <?php endif; ?>
        });
    </script>

</body>
</html>
