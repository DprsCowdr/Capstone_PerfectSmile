<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patients - Dentist Dashboard</title>
    <link href="<?= base_url('css/sb-admin-2.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('vendor/datatables/dataTables.bootstrap4.min.css') ?>" rel="stylesheet">
    <style>
        .patient-card {
            transition: all 0.3s ease;
            border-left: 4px solid #007bff;
        }
        .patient-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
        .stat-item {
            text-align: center;
            padding: 10px;
            border-right: 1px solid #dee2e6;
        }
        .stat-item:last-child {
            border-right: none;
        }
        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #007bff;
        }
        .stat-label {
            font-size: 0.8rem;
            color: #6c757d;
        }
    </style>
</head>
<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
        
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-users mr-2"></i>Patients Management
                        </h1>
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
