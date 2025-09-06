<?php
require_once 'vendor/autoload.php';

// Test data for PDF generation
$prescription = [
    'id' => 'TEST',
    'patient_name' => 'John Doe',
    'patient_age' => '35',
    'patient_gender' => 'Male',
    'patient_address' => '123 Main Street, City',
    'issue_date' => '2025-09-05',
    'next_appointment' => '2025-09-12',
    'dentist_name' => 'Dr. Smith',
    'license_no' => 'LIC123456',
    'ptr_no' => 'PTR789012',
    'instructions' => 'Take medicine after meals. Follow dosage instructions carefully.'
];

$items = [
    [
        'medicine_name' => 'Amoxicillin',
        'dosage' => '500mg',
        'frequency' => '3x daily',
        'duration' => '7 days',
        'instructions' => 'Take with food'
    ],
    [
        'medicine_name' => 'Ibuprofen',
        'dosage' => '200mg',
        'frequency' => 'As needed',
        'duration' => '3 days',
        'instructions' => 'For pain relief'
    ]
];

// Generate the HTML using the same view logic as the controller
ob_start();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Test Prescription #<?= $prescription['id'] ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: "Times New Roman", serif; font-size: 12px; color: #000; line-height: 1.4; }
        .page { width: 100%; padding: 40px; }
        .header { text-align: center; margin-bottom: 25px; padding: 20px 0; border-bottom: 2px solid #2563eb; }
        .clinic-name { font-size: 24px; font-weight: bold; text-transform: uppercase; color: #1e40af; }
        .dentist-name { font-size: 16px; font-weight: bold; margin: 6px 0 4px; color: #374151; }
        .contact { font-size: 11px; color: #4b5563; }
        .patient-info { margin: 25px 0; background: #f9fafb; padding: 20px; border-radius: 8px; }
        .patient-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .patient-info-item { display: flex; }
        .patient-info-item strong { min-width: 80px; font-weight: 600; }
        .rx { font-size: 36px; font-weight: bold; margin: 25px 0 20px; color: #dc2626; }
        .medicine-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .medicine-table th, .medicine-table td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        .medicine-table th { background: #f5f5f5; font-weight: bold; }
        .instructions { margin: 20px 0; }
        .footer-row { display: flex; justify-content: space-between; align-items: baseline; margin-top: 30px; gap: 40px; }
        .signature { text-align: right; }
        .signature-line { border-bottom: 2px solid #374151; width: 200px; margin: 0 auto 8px; height: 40px; }
        .signature .name { font-weight: bold; font-size: 14px; color: #1f2937; margin-bottom: 4px; }
        .signature .credentials { font-size: 11px; color: #6b7280; line-height: 1.4; }
        .next-appointment { margin-bottom: 30px; max-width: 340px; text-align: left; }
        .next-appointment .signature-line { border-bottom: 2px solid #374151; width: 220px; height: 2px; margin: 8px auto 0; display: block; }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div class="clinic-name">Perfect Smile Dental Clinic</div>
            <div class="dentist-name"><?= $prescription['dentist_name'] ?></div>
            <div class="contact">
                Unit No. 201 Tansylit Bldg., Alfelor St., Brgy San Roque, Iriga City<br>
                Zone 2, Brgy. Sto. Domingo, Nabua, Camarines Sur
            </div>
        </div>

        <div class="patient-info">
            <div class="patient-info-grid">
                <div class="patient-info-item">
                    <strong>Patient:</strong>
                    <span><?= $prescription['patient_name'] ?></span>
                </div>
                <div class="patient-info-item">
                    <strong>Date:</strong>
                    <span><?= date('F d, Y', strtotime($prescription['issue_date'])) ?></span>
                </div>
                <div class="patient-info-item">
                    <strong>Age:</strong>
                    <span><?= $prescription['patient_age'] ?></span>
                </div>
                <div class="patient-info-item">
                    <strong>Gender:</strong>
                    <span><?= $prescription['patient_gender'] ?></span>
                </div>
                <div class="patient-info-item" style="grid-column: 1 / -1;">
                    <strong>Address:</strong>
                    <span><?= $prescription['patient_address'] ?></span>
                </div>
            </div>
        </div>

        <div class="rx">℞</div>

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
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= $item['medicine_name'] ?></td>
                            <td><?= $item['dosage'] ?></td>
                            <td><?= $item['frequency'] ?></td>
                            <td><?= $item['duration'] ?></td>
                            <td><?= $item['instructions'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if (!empty($prescription['instructions'])): ?>
            <div class="instructions">
                <strong>Instructions:</strong>
                <div><?= nl2br($prescription['instructions']) ?></div>
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
                <div class="name"><?= $prescription['dentist_name'] ?></div>
                <div class="credentials">
                    License No.: <?= $prescription['license_no'] ?><br>
                    PTR No.: <?= $prescription['ptr_no'] ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$html = ob_get_clean();

// Generate PDF with dompdf
$dompdf = new \Dompdf\Dompdf();
// Set paper size to Half Bond (5.5" x 8.5") in points
$dompdf->setPaper([0, 0, 5.5 * 72, 8.5 * 72]);
$dompdf->loadHtml($html);
$dompdf->render();

// Save to file
$output = $dompdf->output();
$filename = 'test_prescription_' . date('Y-m-d_H-i-s') . '.pdf';
file_put_contents($filename, $output);

echo "PDF generated successfully: {$filename}\n";
echo "File size: " . number_format(strlen($output)) . " bytes\n";
echo "Paper size: 5.5\" x 8.5\" (Half Bond)\n";
