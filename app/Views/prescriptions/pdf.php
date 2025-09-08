<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Prescription #<?= esc($prescription['id']) ?></title>
    <link rel="stylesheet" href="<?= base_url('css/prescription-pdf.css') ?>">
</head>
<body>
    <!-- PDF Document -->
    <div class="pdf-document">
        <div class="page">
            <div class="watermark">PRESCRIPTION</div>
            
            <!-- Header -->
            <div class="header">
                <div class="clinic-name">Perfect Smile Dental Clinic</div>
                <div class="dentist-name"><?= esc($prescription['dentist_name'] ?? 'Dentist') ?></div>
                <div class="dentist-role"><?= esc($prescription['dentist_role'] ?? 'Dentist') ?></div>
                <div class="contact">
                    Unit No. 201 Tansylit Bldg., Alfelor St., Brgy San Roque, Iriga City, Camarines Sur — 0946-060-6381<br>
                    Zone 2, Brgy. Sto. Domingo, Nabua, Camarines Sur — 0970-141-5022
                </div>
            </div>

            <!-- Patient Info -->
            <div class="patient-info">
                <!-- <div style="font-weight: bold; color: #065f46; margin-bottom: 12px; font-size: 13px;">
                    PATIENT INFORMATION
                </div> -->
                <div class="patient-info-grid">
                    <div class="patient-info-item">
                        <strong>Patient Name:</strong>
                        <span><?= esc($prescription['patient_name'] ?? 'N/A') ?></span>
                    </div>
                    <div class="patient-info-item">
                        <strong>Date:</strong>
                        <span><?= date('F d, Y', strtotime($prescription['issue_date'])) ?></span>
                    </div>
                    <div class="patient-info-item">
                        <strong>Age:</strong>
                        <span><?= esc($prescription['patient_age'] ?? '___') ?></span>
                    </div>
                    <div class="patient-info-item">
                        <strong>Gender:</strong>
                        <span><?= esc($prescription['patient_gender'] ?? '___') ?></span>
                    </div>
                    <div class="patient-info-item full-width">
                        <strong>Address:</strong>
                        <span><?= esc($prescription['patient_address'] ?? '________________________') ?></span>
                    </div>
                </div>
            </div>

            <!-- RX Symbol -->
            <div class="rx">℞</div>

           <!-- Medicines -->
            <?php if (!empty($items)): ?>
                <table class="medicine-table">
                    <thead>
                        <tr>
                            <th>Medicine</th>
                            <th>Dosage</th>
                            <th>Frequency</th>
                            <th>Duration</th>
                            <th>Instructions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $it): ?>
                            <tr>
                                <td><?= esc($it['medicine_name']) ?></td>
                                <td><?= esc($it['dosage'] ?? '-') ?></td>
                                <td><?= esc($it['frequency'] ?? '-') ?></td>
                                <td><?= esc($it['duration'] ?? '-') ?></td>
                                <td><?= esc($it['instructions'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>


            <!-- General Instructions -->
            <?php if (!empty($prescription['instructions'])): ?>
            <div class="instructions">
                <strong>Instructions:</strong>
                <div>
                    <?= nl2br(esc($prescription['instructions'])) ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="footer-row">
                <?php if (!empty($prescription['next_appointment'])): ?>
                    <div class="next-appointment">
                        <div class="name"><?= date('F d, Y (l)', strtotime($prescription['next_appointment'])) ?></div>
                        <div class="signature-line"></div>
                        <div class="credentials">Next Appointment</div>
                    </div>
                <?php endif; ?>

                <div class="signature">
                    <div class="signature-line"></div>
                    <div class="name"><?= esc($prescription['dentist_name'] ?? 'Dentist') ?></div>
                    <div class="credentials">
                        License No.: <?= esc($prescription['license_no'] ?? '_____________') ?><br>
                        PTR No.: <?= esc($prescription['ptr_no'] ?? '_____________') ?>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</body>
</html>