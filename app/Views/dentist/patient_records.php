<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Records - <?= esc($patient['name']) ?></title>
    <link href="<?= base_url('css/sb-admin-2.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet">
    <style>
        .record-card {
            border-left: 4px solid #007bff;
            transition: all 0.3s;
        }
        .record-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .tooth-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(60px, 1fr));
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
        .priority-urgent { border-left-color: #dc3545; }
        .priority-high { border-left-color: #fd7e14; }
        .priority-medium { border-left-color: #ffc107; }
        .priority-low { border-left-color: #28a745; }
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
                            <i class="fas fa-user-md mr-2"></i>Patient Records - <?= esc($patient['name']) ?>
                        </h1>
                        <a href="<?= base_url('dentist/dashboard') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                        </a>
                    </div>

                    <!-- Patient Info -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Patient Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <p><strong>Name:</strong> <?= esc($patient['name']) ?></p>
                                    <p><strong>Age:</strong> <?= $patient['age'] ? $patient['age'] . ' years' : 'Not specified' ?></p>
                                </div>
                                <div class="col-md-3">
                                    <p><strong>Gender:</strong> <?= esc($patient['gender']) ?></p>
                                    <p><strong>Phone:</strong> <?= esc($patient['phone']) ?></p>
                                </div>
                                <div class="col-md-3">
                                    <p><strong>Email:</strong> <?= esc($patient['email']) ?></p>
                                    <p><strong>DOB:</strong> <?= $patient['date_of_birth'] ? date('F j, Y', strtotime($patient['date_of_birth'])) : 'Not specified' ?></p>
                                </div>
                                <div class="col-md-3">
                                    <p><strong>Address:</strong> <?= esc($patient['address']) ?></p>
                                    <p><strong>Occupation:</strong> <?= esc($patient['occupation']) ?: 'Not specified' ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Treatment Summary -->
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
                                    <div class="card border-left-warning">
                                        <div class="card-body">
                                            <h6 class="font-weight-bold">Tooth #<?= $tooth['tooth_number'] ?></h6>
                                            <p class="mb-1"><strong>Condition:</strong> <?= esc($tooth['condition']) ?></p>
                                            <p class="mb-1"><strong>Status:</strong> 
                                                <span class="badge badge-<?= $tooth['status'] === 'cavity' ? 'danger' : 'warning' ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $tooth['status'])) ?>
                                                </span>
                                            </p>
                                            <?php if ($tooth['service_name']): ?>
                                            <p class="mb-1"><strong>Recommended:</strong> <?= esc($tooth['service_name']) ?> ($<?= $tooth['price'] ?>)</p>
                                            <?php endif; ?>
                                            <p class="mb-0"><strong>Priority:</strong> 
                                                <span class="badge badge-<?= $tooth['priority'] === 'urgent' ? 'danger' : ($tooth['priority'] === 'high' ? 'warning' : 'info') ?>">
                                                    <?= ucfirst($tooth['priority']) ?>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Dental Records -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Dental Records History</h6>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($records)): ?>
                                <?php foreach ($records as $record): ?>
                                <div class="record-card card mb-3">
                                    <div class="card-header">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-calendar mr-2"></i>
                                                    <?= date('F j, Y', strtotime($record['record_date'])) ?>
                                                </h6>
                                            </div>
                                            <div class="col-md-6 text-right">
                                                <small class="text-muted">
                                                    By: <?= esc($record['dentist_name']) ?>
                                                    <?php if ($record['appointment_datetime']): ?>
                                                        | Appointment: <?= date('M j, Y g:i A', strtotime($record['appointment_datetime'])) ?>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6>Diagnosis:</h6>
                                                <p><?= nl2br(esc($record['diagnosis'])) ?></p>
                                                
                                                <?php if ($record['treatment']): ?>
                                                <h6>Treatment:</h6>
                                                <p><?= nl2br(esc($record['treatment'])) ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6">
                                                <?php if ($record['notes']): ?>
                                                <h6>Notes:</h6>
                                                <p><?= nl2br(esc($record['notes'])) ?></p>
                                                <?php endif; ?>
                                                
                                                <?php if ($record['xray_image_url']): ?>
                                                <h6>X-ray:</h6>
                                                <a href="<?= esc($record['xray_image_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-image mr-1"></i>View X-ray
                                                </a>
                                                <?php endif; ?>
                                                
                                                <?php if ($record['next_appointment_date']): ?>
                                                <h6>Next Appointment:</h6>
                                                <p><i class="fas fa-calendar-plus mr-1"></i><?= date('F j, Y', strtotime($record['next_appointment_date'])) ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No dental records found</h5>
                                    <p class="text-muted">This patient has no dental examination records yet.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Complete Dental History Chart -->
                    <?php if (!empty($dentalHistory)): ?>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-tooth mr-2"></i>Complete Dental History Chart
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-3 text-muted">Historical view of all teeth conditions from previous examinations</p>
                            
                            <!-- Group by examination date -->
                            <?php 
                            $groupedHistory = [];
                            foreach ($dentalHistory as $entry) {
                                $groupedHistory[$entry['record_date']][] = $entry;
                            }
                            ?>
                            
                            <?php foreach ($groupedHistory as $date => $teeth): ?>
                            <div class="mb-4">
                                <h6 class="border-bottom pb-2">
                                    <i class="fas fa-calendar mr-2"></i><?= date('F j, Y', strtotime($date)) ?>
                                </h6>
                                <div class="tooth-grid">
                                    <?php foreach ($teeth as $tooth): ?>
                                    <div class="tooth-item tooth-<?= $tooth['status'] ?>" 
                                         title="Tooth <?= $tooth['tooth_number'] ?>: <?= $tooth['condition'] ?>">
                                        <div class="font-weight-bold"><?= $tooth['tooth_number'] ?></div>
                                        <div><?= ucfirst($tooth['status']) ?></div>
                                        <?php if ($tooth['service_name']): ?>
                                        <div class="text-primary" style="font-size: 8px;"><?= $tooth['service_name'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
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

</body>
</html>
