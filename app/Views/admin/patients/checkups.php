<?php
// Backwards-compatible shim: delegate to admin/patient_checkups.php
// Keeps existing controller calls to 'admin/patients/checkups' working.
require __DIR__ . '/../patient_checkups.php';
