<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Prescription #<?= esc($prescription['id']) ?></title>
    <style>
        /* Prescription PDF Styles - Embedded for PDF generation */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body { 
            font-family: "Times New Roman", serif; 
            font-size: 10px; 
            color: #000; 
            line-height: 1.3;
            background: white;
            margin: 0;
            padding: 0;
        }

        /* Ensure landscape layout */
        @page {
            size: landscape;
            width: 11in;
            height: 8.5in;
            margin: 0;
        }

        /* PDF Document */
        .pdf-document {
            padding: 0;
            display: block;
            width: 792px; /* Full 11" bond paper width in landscape */
            height: 612px; /* Full 8.5" bond paper height in landscape */
            margin: 0;
            position: relative;
        }

        .page {
            width: 396px; /* Exactly half of 11" bond paper width (792px / 2) */
            height: 612px; /* Full 8.5" bond paper height in landscape */
            margin: 0;
            background: #fff;
            padding: 15px;
            box-sizing: border-box;
            position: relative;
            page-break-after: avoid;
        }

        /* Watermark */
        .watermark {
            position: absolute;
            top: 306px; /* Center of the 612px tall form (612/2 = 306px) */
            left: 198px; /* Center of the 396px wide form (396/2 = 198px) */
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 30px;
            color: rgba(59, 130, 246, 0.03);
            font-weight: bold;
            z-index: -1;
            pointer-events: none;
        }

        /* Header Styles */
        .header { 
            text-align: center; 
            margin-bottom: 10px; 
            padding: 8px 0;
            border-bottom: 1px solid #2563eb;
            position: relative;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 2px;
            background: linear-gradient(90deg, #2563eb, #1d4ed8);
            border-radius: 1px;
        }

        .clinic-name { 
            font-size: 14px; 
            font-weight: bold; 
            text-transform: uppercase; 
            color: #1e40af;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .dentist-name { 
            font-size: 11px; 
            font-weight: bold; 
            margin: 2px 0 1px;
            color: #374151;
        }

        .dentist-role { 
            font-size: 9px; 
            margin-bottom: 3px;
            color: #6b7280;
            font-style: italic;
        }

        .contact { 
            font-size: 7px; 
            line-height: 1.2;
            color: #4b5563;
            max-width: 100%;
            margin: 0 auto;
        }

        /* Patient Info Styles */
        .patient-info { 
            margin: 8px 0; 
            font-size: 8px;
            background: #f9fafb;
            padding: 8px;
            border-radius: 3px;
            border-left: 2px solid #d1d5db;
        }

        .patient-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3px;
            margin-top: 3px;
        }

        .patient-info-item {
            display: flex;
            align-items: baseline;
        }

        .patient-info-item.full-width {
            grid-column: 1 / -1;
        }

        .patient-info-item strong {
            color: #374151;
            min-width: 50px;
            font-weight: 600;
            font-size: 8px;
        }

        .patient-info-item span {
            color: #1f2937;
            border-bottom: 1px dotted #d1d5db;
            flex: 1;
            padding-bottom: 1px;
            margin-left: 4px;
            font-size: 8px;
        }

        /* RX Symbol */
        .rx { 
            font-size: 24px; 
            font-weight: bold; 
            margin: 8px 0 6px; 
            color: #000000;
            text-align: left;
        }

        /* Medicine Table Styles */
        .medicine-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            font-size: 7px;
        }

        .medicine-table th {
            text-align: left;
            padding: 2px;
            border-bottom: 1px solid #000;
            font-weight: 600;
            font-size: 7px;
        }

        .medicine-table td {
            padding: 2px;
            vertical-align: top;
            border-bottom: 1px solid #e5e7eb;
            font-size: 7px;
        }

        /* Instructions */
        .instructions {
            margin: 6px 0;
            font-size: 8px;
        }

        /* Footer Styles */
        .footer { 
            margin-top: 15px; 
            font-size: 8px;
        }

        .next-appointment {
            margin-bottom: 15px;
            max-width: 120px;
            text-align: center;
        }

        .next-appointment .name {
            font-weight: bold;
            font-size: 8px;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .next-appointment .credentials {
            font-size: 7px;
            color: #6b7280;
            line-height: 1.2;
            margin-top: 4px;
        }

        .signature { 
            margin-top: 25px;
            text-align: center;
            padding: 10px;
            border-top: 1px solid #e5e7eb;
        }

        .signature .name { 
            font-weight: bold;
            font-size: 8px;
            color: #1f2937;
            margin-bottom: 2px;
            margin-top: 4px;
        }

        .signature .credentials {
            font-size: 7px;
            color: #6b7280;
            line-height: 1.2;
            margin-top: 4px;
        }

        .footer-row {
            width: 100%;
            margin-top: 20px;
            padding: 0;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .footer-table td {
            width: 50%;
            vertical-align: top;
            text-align: center;
            padding: 0 5px;
        }

        .footer-row .next-appointment {
            width: 100%;
            text-align: center;
            margin-bottom: 0;
        }

        .footer-row .signature {
            width: 100%;
            text-align: center;
            margin-top: 0;
            padding: 0;
            border-top: none;
        }

        .signature-line {
            border-bottom: 1px solid #374151;
            width: 100px;
            margin: 0 auto 4px;
            height: 15px;
        }

        .next-appointment .signature-line {
            border-bottom: 1px solid #374151;
            width: 100px;
            height: 15px;
            margin: 0 auto 4px;
            display: block;
        }
    </style>
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
                    Email: perfectsmile@dental.com
                </div>
            </div>

            <!-- Patient Information -->
            <div class="patient-info">
                <strong style="color: #374151; font-weight: 600; font-size: 8px;">Patient Information</strong>
                <div class="patient-info-grid">
                    <div class="patient-info-item full-width">
                        <strong>Name:</strong>
                        <span><?= esc($prescription['patient_name'] ?? 'N/A') ?></span>
                    </div>
                    <div class="patient-info-item full-width">
                        <strong>Address:</strong>
                        <span><?= esc($prescription['patient_address'] ?? 'N/A') ?></span>
                    </div>
                    <div class="patient-info-item">
                        <strong>Age:</strong>
                        <span><?= esc($prescription['patient_age'] ?? 'N/A') ?></span>
                    </div>
                    <div class="patient-info-item">
                        <strong>Gender:</strong>
                        <span><?= esc($prescription['patient_gender'] ?? 'N/A') ?></span>
                    </div>
                </div>
            </div>

            <!-- RX Symbol -->
            <div class="rx">℞</div>

            <!-- Medicine List -->
            <?php if (!empty($items)): ?>
                <table class="medicine-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Medicine</th>
                            <th style="width: 15%;">Dosage</th>
                            <th style="width: 15%;">Frequency</th>
                            <th style="width: 15%;">Duration</th>
                            <th style="width: 30%;">Instructions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= esc($item['medicine_name']) ?></td>
                                <td><?= esc($item['dosage']) ?></td>
                                <td><?= esc($item['frequency'] ?? '-') ?></td>
                                <td><?= esc($item['duration'] ?? '-') ?></td>
                                <td><?= esc($item['instructions']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="font-style: italic; color: #6b7280; font-size: 8px;">No medicines prescribed.</p>
            <?php endif; ?>

            <!-- General Instructions -->
            <?php if (!empty($prescription['instructions'])): ?>
                <div class="instructions">
                    <strong style="color: #374151; font-size: 8px;">Instructions:</strong><br>
                    <?= nl2br(esc($prescription['instructions'])) ?>
                </div>
            <?php endif; ?>

            <!-- Footer with Next Appointment and Signature -->
            <div class="footer">
                <div class="footer-row">
                    <table class="footer-table">
                        <tr>
                            <td>
                                <!-- Next Appointment -->
                                <div class="next-appointment">
                                    <div style="font-weight: bold; font-size: 8px; color: #1f2937; margin-bottom: 8px;">Next Appointment</div>
                                    <div class="signature-line"></div>
                                    <?php if (!empty($prescription['next_appointment'])): ?>
                                        <div style="font-size: 7px; color: #1f2937; margin-top: 4px;">
                                            <?= date('M j, Y', strtotime($prescription['next_appointment'])) ?>
                                        </div>
                                    <?php else: ?>
                                        <div style="font-size: 7px; color: #6b7280; margin-top: 4px;">Date</div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <!-- Doctor Signature -->
                                <div class="signature">
                                    <div style="font-weight: bold; font-size: 8px; color: #1f2937; margin-bottom: 8px;">Doctor's Signature</div>
                                    <div class="signature-line"></div>
                                    <div class="name" style="margin-top: 4px;"><?= esc($prescription['dentist_name'] ?? 'Dentist') ?></div>
                                    <div class="credentials" style="margin-top: 2px;">
                                        <?php if (!empty($prescription['license_no'])): ?>
                                            License No: <?= esc($prescription['license_no']) ?><br>
                                        <?php endif; ?>
                                        <?php if (!empty($prescription['ptr_no'])): ?>
                                            PTR No: <?= esc($prescription['ptr_no']) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
