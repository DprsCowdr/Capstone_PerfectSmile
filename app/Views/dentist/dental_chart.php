<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dental Chart - <?= esc($appointment['patient_name']) ?></title>
    <link href="<?= base_url('css/sb-admin-2.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet">
    <style>
        .tooth-grid {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 10px;
            margin: 20px 0;
        }
        .tooth-item {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }
        .tooth-item:hover {
            border-color: #007bff;
            box-shadow: 0 2px 5px rgba(0,123,255,0.3);
        }
        .tooth-item.selected {
            border-color: #007bff;
            background-color: #e3f2fd;
        }
        .tooth-item.has-condition {
            border-color: #dc3545;
            background-color: #f8d7da;
        }
        .tooth-number {
            font-weight: bold;
            font-size: 18px;
            color: #495057;
        }
        .tooth-name {
            font-size: 10px;
            color: #6c757d;
            margin-top: 5px;
        }
        .tooth-status {
            font-size: 8px;
            margin-top: 3px;
            padding: 2px 4px;
            border-radius: 3px;
            display: inline-block;
        }
        .status-healthy { background-color: #d4edda; color: #155724; }
        .status-cavity { background-color: #f8d7da; color: #721c24; }
        .status-filling { background-color: #fff3cd; color: #856404; }
        .status-crown { background-color: #d1ecf1; color: #0c5460; }
        .status-extraction_needed { background-color: #f5c6cb; color: #721c24; }
        .status-missing { background-color: #e2e3e5; color: #383d41; }
        .jaw-section {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }
        .jaw-title {
            font-weight: bold;
            margin-bottom: 15px;
            color: #495057;
        }
        .tooth-detail-form {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1000;
            max-width: 500px;
            width: 90%;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
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
                            <i class="fas fa-tooth mr-2"></i>Dental Chart - <?= esc($appointment['patient_name']) ?>
                        </h1>
                        <a href="<?= base_url('dentist/dashboard') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                        </a>
                    </div>

                    <!-- Appointment Info -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Appointment Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Patient:</strong> <?= esc($appointment['patient_name']) ?></p>
                                    <p><strong>Date:</strong> <?= date('F j, Y', strtotime($appointment['appointment_datetime'])) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Time:</strong> <?= date('g:i A', strtotime($appointment['appointment_datetime'])) ?></p>
                                    <p><strong>Status:</strong> <?= ucfirst($appointment['status']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dental Chart Form -->
                    <form method="POST" action="<?= base_url('dentist/records/create') ?>">
                        <input type="hidden" name="patient_id" value="<?= $appointment['patient_id'] ?>">
                        <input type="hidden" name="appointment_id" value="<?= $appointment['id'] ?>">

                        <!-- General Record Information -->
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">General Examination</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="diagnosis">Diagnosis/Findings</label>
                                            <textarea class="form-control" name="diagnosis" id="diagnosis" rows="3" required></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="treatment">Treatment Performed</label>
                                            <textarea class="form-control" name="treatment" id="treatment" rows="3"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="notes">Additional Notes</label>
                                            <textarea class="form-control" name="notes" id="notes" rows="3"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="next_appointment_date">Next Appointment Date</label>
                                            <input type="date" class="form-control" name="next_appointment_date" id="next_appointment_date">
                                        </div>
                                        <div class="form-group">
                                            <label for="xray_image_url">X-ray Image URL</label>
                                            <input type="url" class="form-control" name="xray_image_url" id="xray_image_url">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dental Chart -->
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Dental Chart - Click on teeth to examine</h6>
                            </div>
                            <div class="card-body">
                                
                                <!-- Permanent Teeth -->
                                <div class="jaw-section">
                                    <div class="jaw-title">Upper Jaw (Maxillary) - Permanent Teeth</div>
                                    <div class="tooth-grid">
                                        <?php foreach ($toothLayout['permanent']['upper'] as $number => $name): ?>
                                            <div class="tooth-item" data-tooth="<?= $number ?>" data-type="permanent">
                                                <div class="tooth-number"><?= $number ?></div>
                                                <div class="tooth-name"><?= $name ?></div>
                                                <div class="tooth-status status-healthy" id="status-<?= $number ?>">Healthy</div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="jaw-section">
                                    <div class="jaw-title">Lower Jaw (Mandibular) - Permanent Teeth</div>
                                    <div class="tooth-grid">
                                        <?php foreach ($toothLayout['permanent']['lower'] as $number => $name): ?>
                                            <div class="tooth-item" data-tooth="<?= $number ?>" data-type="permanent">
                                                <div class="tooth-number"><?= $number ?></div>
                                                <div class="tooth-name"><?= $name ?></div>
                                                <div class="tooth-status status-healthy" id="status-<?= $number ?>">Healthy</div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Primary Teeth Section -->
                                <div class="jaw-section">
                                    <div class="jaw-title">Upper Jaw (Maxillary) - Primary Teeth</div>
                                    <div class="tooth-grid">
                                        <?php foreach ($toothLayout['primary']['upper'] as $letter => $name): ?>
                                            <div class="tooth-item" data-tooth="<?= $letter ?>" data-type="primary">
                                                <div class="tooth-number"><?= $letter ?></div>
                                                <div class="tooth-name"><?= $name ?></div>
                                                <div class="tooth-status status-healthy" id="status-<?= $letter ?>">Healthy</div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="jaw-section">
                                    <div class="jaw-title">Lower Jaw (Mandibular) - Primary Teeth</div>
                                    <div class="tooth-grid">
                                        <?php foreach ($toothLayout['primary']['lower'] as $letter => $name): ?>
                                            <div class="tooth-item" data-tooth="<?= $letter ?>" data-type="primary">
                                                <div class="tooth-number"><?= $letter ?></div>
                                                <div class="tooth-name"><?= $name ?></div>
                                                <div class="tooth-status status-healthy" id="status-<?= $letter ?>">Healthy</div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center mb-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save mr-2"></i>Save Dental Record & Chart
                            </button>
                        </div>

                    </form>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Overlay for modal -->
    <div class="overlay" id="overlay"></div>

    <!-- Tooth Detail Form -->
    <div class="tooth-detail-form" id="toothDetailForm">
        <h5 id="toothTitle">Tooth Details</h5>
        <hr>
        
        <input type="hidden" id="currentTooth" value="">
        <input type="hidden" id="currentToothType" value="">
        
        <div class="form-group">
            <label for="toothCondition">Condition</label>
            <input type="text" class="form-control" id="toothCondition" placeholder="Describe the condition">
        </div>
        
        <div class="form-group">
            <label for="toothStatus">Status</label>
            <select class="form-control" id="toothStatus">
                <?php foreach ($toothConditions as $key => $value): ?>
                    <option value="<?= $key ?>"><?= $value ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="toothNotes">Notes</label>
            <textarea class="form-control" id="toothNotes" rows="3" placeholder="Additional notes about this tooth"></textarea>
        </div>
        
        <div class="form-group">
            <label for="recommendedService">Recommended Service</label>
            <select class="form-control" id="recommendedService">
                <option value="">-- Select Service --</option>
                <?php foreach ($services as $service): ?>
                    <option value="<?= $service['id'] ?>" data-price="<?= $service['price'] ?>"><?= $service['name'] ?> - $<?= $service['price'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="treatmentPriority">Priority</label>
            <select class="form-control" id="treatmentPriority">
                <option value="low">Low</option>
                <option value="medium" selected>Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="estimatedCost">Estimated Cost</label>
            <input type="number" class="form-control" id="estimatedCost" step="0.01" placeholder="0.00">
        </div>
        
        <div class="text-center">
            <button type="button" class="btn btn-primary" onclick="saveToothData()">Save</button>
            <button type="button" class="btn btn-secondary" onclick="closeToothForm()">Cancel</button>
        </div>
    </div>

    <!-- Core JavaScript -->
    <script src="<?= base_url('vendor/jquery/jquery.min.js') ?>"></script>
    <script src="<?= base_url('vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('js/sb-admin-2.min.js') ?>"></script>

    <script>
        $(document).ready(function() {
            // Handle tooth clicking
            $('.tooth-item').click(function() {
                const toothNumber = $(this).data('tooth');
                const toothType = $(this).data('type');
                const toothName = $(this).find('.tooth-name').text();
                
                openToothForm(toothNumber, toothType, toothName);
            });

            // Handle service selection to auto-fill price
            $('#recommendedService').change(function() {
                const selectedOption = $(this).find('option:selected');
                const price = selectedOption.data('price');
                if (price) {
                    $('#estimatedCost').val(price);
                }
            });
        });

        function openToothForm(toothNumber, toothType, toothName) {
            $('#currentTooth').val(toothNumber);
            $('#currentToothType').val(toothType);
            $('#toothTitle').text(`Tooth ${toothNumber} - ${toothName}`);
            
            // Load existing data if any
            loadToothData(toothNumber);
            
            $('#overlay').show();
            $('#toothDetailForm').show();
        }

        function closeToothForm() {
            $('#overlay').hide();
            $('#toothDetailForm').hide();
        }

        function loadToothData(toothNumber) {
            // Load existing form data for this tooth
            const condition = $(`input[name="tooth[${toothNumber}][condition]"]`).val() || '';
            const status = $(`select[name="tooth[${toothNumber}][status]"]`).val() || 'healthy';
            const notes = $(`textarea[name="tooth[${toothNumber}][notes]"]`).val() || '';
            const serviceId = $(`select[name="tooth[${toothNumber}][recommended_service_id]"]`).val() || '';
            const priority = $(`select[name="tooth[${toothNumber}][priority]"]`).val() || 'medium';
            const cost = $(`input[name="tooth[${toothNumber}][estimated_cost]"]`).val() || '';
            
            $('#toothCondition').val(condition);
            $('#toothStatus').val(status);
            $('#toothNotes').val(notes);
            $('#recommendedService').val(serviceId);
            $('#treatmentPriority').val(priority);
            $('#estimatedCost').val(cost);
        }

        function saveToothData() {
            const toothNumber = $('#currentTooth').val();
            const toothType = $('#currentToothType').val();
            const condition = $('#toothCondition').val();
            const status = $('#toothStatus').val();
            const notes = $('#toothNotes').val();
            const serviceId = $('#recommendedService').val();
            const priority = $('#treatmentPriority').val();
            const cost = $('#estimatedCost').val();

            // Create hidden form inputs if they don't exist
            createHiddenInput(`tooth[${toothNumber}][tooth_type]`, toothType);
            createHiddenInput(`tooth[${toothNumber}][condition]`, condition);
            createHiddenInput(`tooth[${toothNumber}][status]`, status);
            createHiddenInput(`tooth[${toothNumber}][notes]`, notes);
            createHiddenInput(`tooth[${toothNumber}][recommended_service_id]`, serviceId);
            createHiddenInput(`tooth[${toothNumber}][priority]`, priority);
            createHiddenInput(`tooth[${toothNumber}][estimated_cost]`, cost);

            // Update visual display
            updateToothDisplay(toothNumber, status, condition);
            
            closeToothForm();
        }

        function createHiddenInput(name, value) {
            // Remove existing input if it exists
            $(`input[name="${name}"], select[name="${name}"], textarea[name="${name}"]`).remove();
            
            // Create new hidden input
            if (value) {
                $('<input>').attr({
                    type: 'hidden',
                    name: name,
                    value: value
                }).appendTo('form');
            }
        }

        function updateToothDisplay(toothNumber, status, condition) {
            const toothElement = $(`.tooth-item[data-tooth="${toothNumber}"]`);
            const statusElement = $(`#status-${toothNumber}`);
            
            // Update status display
            statusElement.removeClass().addClass('tooth-status status-' + status);
            statusElement.text(status.replace('_', ' ').toUpperCase());
            
            // Update tooth appearance
            toothElement.removeClass('has-condition');
            if (status !== 'healthy' || condition) {
                toothElement.addClass('has-condition');
            }
        }

        // Close modal when clicking overlay
        $('#overlay').click(function() {
            closeToothForm();
        });
    </script>

</body>
</html>
