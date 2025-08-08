<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Details - <?= esc($patient['name']) ?></title>
    <link href="<?= base_url('css/sb-admin-2.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet">
    <style>
        .info-card {
            border-left: 4px solid #007bff;
            transition: all 0.3s;
        }
        .info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .appointment-item {
            border-left: 3px solid #28a745;
            padding-left: 15px;
            margin-bottom: 15px;
        }
        .appointment-item.cancelled {
            border-left-color: #dc3545;
        }
        .appointment-item.pending {
            border-left-color: #ffc107;
        }
        .tooth-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(50px, 1fr));
            gap: 5px;
            margin: 10px 0;
        }
        .tooth-item {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 5px;
            text-align: center;
            font-size: 10px;
        }
        .tooth-healthy { background-color: #d4edda; }
        .tooth-cavity { background-color: #f8d7da; }
        .tooth-filling { background-color: #fff3cd; }
        .tooth-crown { background-color: #d1ecf1; }
        .tooth-extraction { background-color: #f5c6cb; }
        .tooth-missing { background-color: #e2e3e5; }
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
                            <i class="fas fa-user-md mr-2"></i>Patient Details - <?= esc($patient['name']) ?>
                        </h1>
                        <div>
                            <a href="<?= base_url('dentist/patients') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-2"></i>Back to Patients
                            </a>
                        </div>
                    </div>

                    <!-- Patient Information -->
                    <div class="row">
                        <!-- Basic Info -->
                        <div class="col-lg-4">
                            <div class="card info-card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Patient Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                            <i class="fas fa-user fa-2x"></i>
                                        </div>
                                    </div>
                                    
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Name:</strong></td>
                                            <td><?= esc($patient['name']) ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td><?= esc($patient['email']) ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Phone:</strong></td>
                                            <td><?= esc($patient['phone']) ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Age:</strong></td>
                                            <td><?= $patient['age'] ? $patient['age'] . ' years' : 'Not specified' ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Gender:</strong></td>
                                            <td><?= esc($patient['gender']) ?: 'Not specified' ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Address:</strong></td>
                                            <td><?= esc($patient['address']) ?: 'Not specified' ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Occupation:</strong></td>
                                            <td><?= esc($patient['occupation']) ?: 'Not specified' ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <span class="badge badge-<?= $patient['status'] === 'active' ? 'success' : 'secondary' ?>">
                                                    <?= ucfirst($patient['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="<?= base_url('dentist/patient-records/' . $patient['id']) ?>" class="btn btn-primary">
                                            <i class="fas fa-clipboard-list mr-2"></i>View Medical Records
                                        </a>
                                        <?php if (!empty($appointments)): ?>
                                        <a href="<?= base_url('dentist/dental-chart/' . $appointments[0]['id']) ?>" class="btn btn-success">
                                            <i class="fas fa-tooth mr-2"></i>Open Dental Chart
                                        </a>
                                        <?php endif; ?>
                                        <a href="#" class="btn btn-info">
                                            <i class="fas fa-calendar-plus mr-2"></i>Schedule Appointment
                                        </a>
                                        <a href="#" class="btn btn-warning">
                                            <i class="fas fa-procedures mr-2"></i>Schedule Procedure
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Main Content -->
                        <div class="col-lg-8">
                            
                            <!-- Teeth Requiring Treatment -->
                            <?php if (!empty($teethNeedingTreatment)): ?>
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 bg-warning">
                                    <h6 class="m-0 font-weight-bold text-white">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>Teeth Requiring Treatment
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php foreach ($teethNeedingTreatment as $tooth): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="border border-warning rounded p-3">
                                                <h6 class="font-weight-bold text-warning">Tooth #<?= $tooth['tooth_number'] ?></h6>
                                                <p class="mb-1"><strong>Condition:</strong> <?= esc($tooth['condition']) ?></p>
                                                <p class="mb-1">
                                                    <strong>Status:</strong> 
                                                    <span class="badge badge-<?= $tooth['status'] === 'cavity' ? 'danger' : 'warning' ?>">
                                                        <?= ucfirst(str_replace('_', ' ', $tooth['status'])) ?>
                                                    </span>
                                                </p>
                                                <?php if ($tooth['service_name']): ?>
                                                <p class="mb-1"><strong>Recommended:</strong> <?= esc($tooth['service_name']) ?></p>
                                                <?php endif; ?>
                                                <p class="mb-0">
                                                    <strong>Priority:</strong> 
                                                    <span class="badge badge-<?= $tooth['priority'] === 'urgent' ? 'danger' : ($tooth['priority'] === 'high' ? 'warning' : 'info') ?>">
                                                        <?= ucfirst($tooth['priority']) ?>
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Appointments History -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Appointment History</h6>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($appointments)): ?>
                                        <?php foreach ($appointments as $appointment): ?>
                                        <div class="appointment-item <?= $appointment['status'] === 'cancelled' ? 'cancelled' : ($appointment['status'] === 'pending' ? 'pending' : '') ?>">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <?= date('F j, Y g:i A', strtotime($appointment['appointment_datetime'])) ?>
                                                    </h6>
                                                    <p class="mb-1 text-muted">
                                                        <i class="fas fa-user-md mr-1"></i><?= esc($appointment['dentist_name']) ?>
                                                        <i class="fas fa-building ml-3 mr-1"></i><?= esc($appointment['branch_name']) ?>
                                                    </p>
                                                    <?php if ($appointment['remarks']): ?>
                                                    <p class="mb-0 text-sm">
                                                        <i class="fas fa-comment mr-1"></i><?= esc($appointment['remarks']) ?>
                                                    </p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-right">
                                                    <span class="badge badge-<?= $appointment['status'] === 'confirmed' ? 'success' : ($appointment['status'] === 'cancelled' ? 'danger' : 'warning') ?>">
                                                        <?= ucfirst($appointment['status']) ?>
                                                    </span>
                                                    <div class="mt-2">
                                                        <a href="<?= base_url('dentist/dental-chart/' . $appointment['id']) ?>" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-tooth"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                                            <p class="text-muted">No appointments found for this patient.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Recent Medical Records -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Recent Medical Records</h6>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($records)): ?>
                                        <?php foreach (array_slice($records, 0, 3) as $record): ?>
                                        <div class="border-left border-primary pl-3 mb-3">
                                            <h6 class="mb-1"><?= date('F j, Y', strtotime($record['record_date'])) ?></h6>
                                            <p class="mb-1"><strong>Diagnosis:</strong> <?= esc($record['diagnosis']) ?></p>
                                            <p class="mb-1 text-muted">
                                                <i class="fas fa-user-md mr-1"></i>By: <?= esc($record['dentist_name']) ?>
                                            </p>
                                        </div>
                                        <?php endforeach; ?>
                                        <div class="text-center mt-3">
                                            <a href="<?= base_url('dentist/patient-records/' . $patient['id']) ?>" class="btn btn-outline-primary">
                                                <i class="fas fa-eye mr-2"></i>View All Records
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-clipboard-list fa-2x text-muted mb-2"></i>
                                            <p class="text-muted">No medical records found for this patient.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>
                    </div>

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

</body>
</html>
