<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', to: 'Home::index');

// Guest routes (no authentication required)
$routes->get('guest/book-appointment', 'Guest::bookAppointment');
$routes->post('guest/book-appointment', 'Guest::submitAppointment');
$routes->get('guest/services', 'Guest::services');
$routes->get('guest/branches', 'Guest::branches');

// Authentication routes
$routes->get('login', 'Auth::index');
$routes->post('auth/login', 'Auth::login');
$routes->get('auth/register', 'Auth::register');
$routes->post('auth/registerUser', 'Auth::registerUser');
$routes->get('auth/logout', 'Auth::logout');

// Dashboard routes - redirect to appropriate dashboard based on user type
$routes->get('dashboard', 'Dashboard::index');

// Admin routes (protected)
// Debug route (remove in production)
$routes->get('debug/appointments', 'Debug::checkAppointments');
$routes->get('debug/add-test', 'Debug::addTestAppointment');
$routes->get('test-3d-viewer', function() {
    return view('test-3d-viewer');
});

$routes->group('admin', ['filter' => 'auth'], function($routes) {
    // Main dashboard
    $routes->get('dashboard', 'Admin\AdminController::dashboard');
    // Admin-only preview endpoint for branch stats (used by staff/admin UI)
    $routes->get('preview-branch-stats', 'Admin\AdminController::previewBranchStats');
    
    // Branch management
    $routes->post('switch-branch', 'Admin\AdminController::switchBranch');
    
    // Patient management routes
    $routes->get('patients', 'Admin\AdminController::patients'); // → patients/index.php
    $routes->get('patients/add', 'Admin\AdminController::addPatient');
    $routes->post('patients/store', 'Admin\AdminController::storePatient');
    $routes->get('patients/toggle-status/(:num)', 'Admin\AdminController::toggleStatus/$1');
    $routes->get('patients/get/(:num)', 'Admin\AdminController::getPatient/$1');
    $routes->post('patients/update/(:num)', 'Admin\AdminController::updatePatient/$1');
    $routes->get('patients/appointments/(:num)', 'Admin\AdminController::getPatientAppointments/$1');
    $routes->get('patients/create-account/(:num)', 'Admin\AdminController::createAccount/$1'); // → patients/create.php
    $routes->post('patients/save-account/(:num)', 'Admin\AdminController::saveAccount/$1');
    
    // Patient Account Activation Routes
    $routes->get('patients/activation', 'Admin\AdminController::patientActivation');
    $routes->post('patients/activate/(:num)', 'Admin\AdminController::activatePatientAccount/$1');
    $routes->post('patients/deactivate/(:num)', 'Admin\AdminController::deactivatePatientAccount/$1');
    $routes->get('patient-checkups', 'Medical\DentalController::patientCheckups'); // → patients/checkups.php
    
    // Appointment management routes
    $routes->get('appointments', 'Admin\AdminController::appointments'); // → appointments/index.php
    $routes->post('appointments/create', 'Admin\AdminController::createAppointment');
    $routes->post('appointments/update/(:num)', 'Admin\AdminController::updateAppointment/$1');
    $routes->post('appointments/delete/(:num)', 'Admin\AdminController::deleteAppointment/$1');
    $routes->post('appointments/approve/(:num)', 'Admin\AdminController::approveAppointment/$1');
    $routes->post('appointments/decline/(:num)', 'Admin\AdminController::declineAppointment/$1');
    $routes->post('appointments/available-dentists', 'Admin\AdminController::getAvailableDentists');
    $routes->post('appointments/check-conflicts', 'Admin\AdminController::checkAppointmentConflicts');
    $routes->get('appointments/details/(:num)', 'Admin\AdminController::getAppointmentDetails/$1');
    $routes->get('waitlist', 'Admin\AdminController::waitlist'); // → appointments/waitlist.php

    // Role-scoped calendar AJAX endpoints (delegates to Appointments controller via thin wrapper)
    $routes->post('calendar/day-appointments', 'AdminCalendarController::dayAppointments');
    $routes->post('calendar/available-slots', 'AdminCalendarController::availableSlots');
    $routes->post('calendar/check-conflicts', 'AdminCalendarController::checkConflicts');
    
    // Dental management routes (moved to DentalController)
    $routes->get('dental-records', 'Medical\DentalController::records'); // → dental/records.php
    $routes->get('dental-records/create/(:num)', 'Medical\DentalController::createRecord/$1'); // → dental/create_record.php
    $routes->post('dental-records/store-basic', 'Medical\DentalController::storeBasicDentalRecord');
    $routes->get('dental-records/(:num)', 'Medical\DentalController::viewRecord/$1'); // → dental/view_record.php
    $routes->get('dental/record/(:num)', 'Medical\DentalController::viewRecord/$1'); // Alternative route for admin/dental/record/ID
    $routes->get('dental-charts', 'Medical\DentalController::charts'); // → dental/charts.php
    $routes->get('dental-charts/(:num)', 'Medical\DentalController::viewChart/$1'); // → dental/view_chart.php
    $routes->get('dental-charts/create/(:num)', 'Medical\DentalController::createChart/$1'); // → dental/create_chart.php
    $routes->get('dental-charts/edit/(:num)', 'Medical\DentalController::editChart/$1'); // → dental/edit_chart.php
    $routes->get('dental-charts/test-3d', 'Medical\DentalController::test3DViewer');
    $routes->post('dental-records/store', 'Medical\DentalController::storeDentalRecord');
    $routes->post('dental-records/update/(:num)', 'Medical\DentalController::updateDentalRecord/$1');
    $routes->get('records', 'Admin\AdminController::records'); // → dental/all_records.php
    $routes->delete('dental-records/delete/(:num)', 'Admin\AdminController::deleteRecord/$1'); // Delete dental record

    // Prescriptions management
    $routes->get('prescriptions', 'Medical\Prescriptions::index');
    $routes->get('prescriptions/create', 'Medical\Prescriptions::create');
    $routes->post('prescriptions/store', 'Medical\Prescriptions::store');
    $routes->get('prescriptions/edit/(:num)', 'Medical\Prescriptions::edit/$1');
    $routes->get('prescriptions/(:num)/edit', 'Medical\Prescriptions::edit/$1');
    $routes->post('prescriptions/update/(:num)', 'Medical\Prescriptions::update/$1');
    $routes->get('prescriptions/(:num)', 'Medical\Prescriptions::show/$1');
    $routes->get('prescriptions/(:num)/download', 'Medical\Prescriptions::downloadPdf/$1');
    $routes->get('prescriptions/(:num)/preview', 'Medical\Prescriptions::previewPdf/$1');
    $routes->get('prescriptions/(:num)/download-file', 'Medical\Prescriptions::downloadPdfFile/$1');
    $routes->delete('prescriptions/(:num)', 'Medical\Prescriptions::delete/$1');
    
    // Patient records popup routes
    $routes->get('patient-info/(:num)', 'Admin\AdminController::getPatientInfo/$1');
    $routes->post('patient-notes/(:num)', 'Admin\AdminController::updatePatientNotes/$1');
    $routes->get('patient-dental-records/(:num)', 'Admin\AdminController::getPatientDentalRecords/$1');
    $routes->get('patient-dental-chart/(:num)', 'Admin\AdminController::getPatientDentalChart/$1');
    $routes->get('patient-appointments/(:num)', 'Admin\AdminController::getPatientAppointmentsModal/$1');
    $routes->get('patient-treatments/(:num)', 'Admin\AdminController::getPatientTreatments/$1');
    $routes->get('patient-medical-records/(:num)', 'Admin\AdminController::getPatientMedicalRecords/$1');
    $routes->get('patient-invoice-history/(:num)', 'Admin\AdminController::getPatientInvoiceHistory/$1');
    $routes->get('patient-prescriptions/(:num)', 'Admin\AdminController::getPatientPrescriptions/$1');
    
    // Management routes
    $routes->get('services', 'Admin\AdminController::services'); // → management/services.php
    $routes->post('services/store', 'Admin\AdminController::storeService');
    $routes->get('services/(:num)', 'Admin\AdminController::getService/$1');
    $routes->post('services/update/(:num)', 'Admin\AdminController::updateService/$1');
    $routes->delete('services/delete/(:num)', 'Admin\AdminController::deleteService/$1');
    $routes->get('role-permission', 'Admin\AdminController::rolePermission'); // → management/roles.php
    // Role & Permission management (RoleController)
    $routes->get('roles', 'Admin\RoleController::index');
    $routes->get('roles/create', 'Admin\RoleController::create');
    $routes->post('roles/create', 'Admin\RoleController::store');
    $routes->get('roles/edit/(:num)', 'Admin\RoleController::edit/$1');
    $routes->post('roles/update/(:num)', 'Admin\RoleController::update/$1');
    $routes->get('roles/show/(:num)', 'Admin\RoleController::show/$1');
    $routes->post('roles/delete/(:num)', 'Admin\RoleController::delete/$1');
    $routes->match(['get','post'], 'roles/assign/(:num)', 'Admin\RoleController::assign/$1');
    $routes->post('roles/remove_user/(:num)/(:num)', 'Admin\RoleController::remove_user/$1/$2');
    $routes->get('roles/search-users', 'Admin\RoleController::searchUsers');
    $routes->get('roles/searchUsers', 'Admin\RoleController::searchUsers');
    // Branch management handled by BranchController
    $routes->get('branches', 'Admin\BranchController::index');
    $routes->get('branches/create', 'Admin\BranchController::create');
    $routes->post('branches', 'Admin\BranchController::store');
    $routes->get('branches/(:num)', 'Admin\BranchController::show/$1');
    $routes->get('branches/(:num)/edit', 'Admin\BranchController::edit/$1');
    $routes->post('branches/update/(:num)', 'Admin\BranchController::update/$1');
    $routes->post('branches/delete/(:num)', 'Admin\BranchController::delete/$1');
    // Keep legacy save-hours endpoint (if used elsewhere)
    $routes->post('branches/(:num)/save-hours', 'Admin\AdminController::saveBranchHours/$1');
    $routes->get('settings', 'Admin\AdminController::settings'); // → management/settings.php
    
    // Procedure management routes
    $routes->get('procedures', 'Admin\ProcedureController::index');
    $routes->get('procedures/ajax-list', 'Admin\ProcedureController::ajaxList');
    $routes->get('procedures/create', 'Admin\ProcedureController::create');
    $routes->post('procedures/store', 'Admin\ProcedureController::store');
    $routes->get('procedures/show/(:num)', 'Admin\ProcedureController::show/$1'); // Uses combined view/edit page
    $routes->get('procedures/edit/(:num)', 'Admin\ProcedureController::edit/$1'); // Uses combined view/edit page
    $routes->post('procedures/update/(:num)', 'Admin\ProcedureController::update/$1');
    $routes->delete('procedures/delete/(:num)', 'Admin\ProcedureController::delete/$1');
    
    // Users management routes
    $routes->get('users', 'Admin\UserController::index'); // → users/index.php
    $routes->get('users/add', 'Admin\UserController::add'); // → users/add.php
    $routes->post('users/store', 'Admin\UserController::store');
    $routes->get('users/edit/(:num)', 'Admin\UserController::edit/$1'); // → users/edit.php
    $routes->post('users/update/(:num)', 'Admin\UserController::update/$1');
    $routes->get('users/toggle-status/(:num)', 'Admin\UserController::toggleStatus/$1');
    $routes->get('users/delete/(:num)', 'Admin\UserController::delete/$1');
    
    // Invoice management routes
    $routes->get('invoices', 'Admin\InvoiceController::index');
    $routes->get('invoices/create', 'Admin\InvoiceController::create');
    $routes->post('invoices/store', 'Admin\InvoiceController::store');
    $routes->get('invoices/show/(:num)', 'Admin\InvoiceController::show/$1');
    $routes->get('invoices/edit/(:num)', 'Admin\InvoiceController::edit/$1');
    $routes->post('invoices/update/(:num)', 'Admin\InvoiceController::update/$1');
    $routes->delete('invoices/delete/(:num)', 'Admin\InvoiceController::delete/$1');
    $routes->get('invoices/print/(:num)', 'Admin\InvoiceController::print/$1');
    $routes->post('invoices/add-item', 'Admin\InvoiceController::addItem');
    $routes->post('invoices/update-item/(:num)', 'Admin\InvoiceController::updateItem/$1');
    $routes->delete('invoices/delete-item/(:num)', 'Admin\InvoiceController::deleteItem/$1');
    $routes->post('invoices/record-payment/(:num)', 'Admin\InvoiceController::recordPayment/$1');
    $routes->post('invoices/send-email/(:num)', 'Admin\InvoiceController::sendEmail/$1');
    
    // Billing routes
});

// Staff notification handling (mark branch notification handled)
$routes->post('staff/notifications/handle/(:num)', 'Staff\Staff::markNotificationHandled/$1');

// Checkup routes (accessible by admin and doctor)
$routes->group('checkup', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Medical\Checkup::index');
    $routes->get('start/(:num)', 'Medical\Checkup::startCheckup/$1');
    $routes->get('patient/(:num)', 'Medical\Checkup::patientCheckup/$1');
    $routes->post('save/(:num)', 'Medical\Checkup::saveCheckup/$1');
    $routes->get('no-show/(:num)', 'Medical\Checkup::markNoShow/$1');
    $routes->post('cancel/(:num)', 'Medical\Checkup::cancelAppointment/$1');
    $routes->get('record/(:num)', 'Medical\Checkup::viewRecord/$1');
    $routes->get('patient-history/(:num)', 'Medical\Checkup::getPatientHistory/$1');
    $routes->get('debug/(:num)', 'Medical\Checkup::debug/$1'); // Debug specific appointment
    $routes->get('debug', 'Medical\Checkup::debug'); // Debug today's appointments
    
    // Services management for checkups
    $routes->get('(:num)/services', 'Medical\CheckupServices::getAppointmentServices/$1');
    $routes->post('(:num)/services', 'Medical\CheckupServices::addService/$1');
    $routes->delete('(:num)/services/(:num)', 'Medical\CheckupServices::removeService/$1/$2');
    $routes->get('services/search', 'Medical\CheckupServices::searchServices');
    $routes->get('services/all', 'Medical\CheckupServices::getAllServices');
});

// Invoice routes removed

// Dentist routes (protected)
// Dentist routes (protected)
$routes->group('dentist', ['filter' => 'auth'], function($routes) {
    $routes->get('dashboard', 'Dentist\Dentist::dashboard');
    $routes->get('stats', 'Dentist\Dentist::stats');
    $routes->get('appointments', 'Dentist\Dentist::appointments');
    $routes->post('availability/set', 'Dentist\Dentist::setAvailability');
    $routes->post('appointments/approve/(:num)', 'Dentist\Dentist::approveAppointment/$1');
    $routes->post('appointments/decline/(:num)', 'Dentist\Dentist::declineAppointment/$1');
    
    // Additional appointment management routes (same as admin)
    $routes->post('appointments/create', 'Dentist\Dentist::createAppointment');
    $routes->post('appointments/update/(:num)', 'Dentist\Dentist::updateAppointment/$1');
    $routes->post('appointments/delete/(:num)', 'Dentist\Dentist::deleteAppointment/$1');
    $routes->post('appointments/available-dentists', 'Dentist\Dentist::getAvailableDentists');
    $routes->post('appointments/check-conflicts', 'Dentist\Dentist::checkAppointmentConflicts');
    $routes->get('appointments/details/(:num)', 'Dentist\Dentist::getAppointmentDetails/$1');

    // Dentist role-scoped calendar endpoints
    $routes->post('calendar/day-appointments', 'DentistCalendarController::dayAppointments');
    $routes->post('calendar/available-slots', 'DentistCalendarController::availableSlots');
    $routes->post('calendar/check-conflicts', 'DentistCalendarController::checkConflicts');
    
    // Patients Module (accessible by dentist)
    $routes->get('patients', 'Dentist\Dentist::patients');
    $routes->get('patients/search', 'Dentist\Dentist::searchPatients');
    $routes->get('patients/(:num)', 'Dentist\Dentist::patientDetails/$1');
    
    // Additional patient management routes (same as admin)
    $routes->get('patients/add', 'Dentist\Dentist::addPatient');
    $routes->post('patients/store', 'Dentist\Dentist::storePatient');
    $routes->get('patients/toggle-status/(:num)', 'Dentist\Dentist::toggleStatus/$1');
    $routes->get('patients/get/(:num)', 'Dentist\Dentist::getPatient/$1');
    $routes->post('patients/update/(:num)', 'Dentist\Dentist::updatePatient/$1');
    $routes->get('patients/appointments/(:num)', 'Dentist\Dentist::getPatientAppointments/$1');
    
    // Dental Records (Step 3: Checkup/Consultation)
    $routes->get('patient-records/(:num)', 'Dentist\Dentist::patientRecords/$1');
    $routes->get('dental-chart/(:num)', 'Dentist\Dentist::dentalChart/$1');
    $routes->post('records/create', 'Dentist\Dentist::createRecord');
    
    // Procedures (Step 5 & 6: Procedure Scheduling & Execution)
    $routes->get('procedures', 'Dentist\Dentist::procedures');
    $routes->post('procedures/schedule', 'Dentist\Dentist::scheduleProcedure');
    $routes->get('procedures/(:num)', 'Dentist\Dentist::procedureDetails/$1');
});

// Patient routes (protected)
$routes->group('patient', ['filter' => 'auth'], function($routes) {
    $routes->get('dashboard', 'Patient\Patient::dashboard');
    $routes->get('calendar', 'Patient\Patient::calendar');
    $routes->get('book-appointment', 'Patient\Patient::bookAppointment');
    $routes->post('book-appointment', 'Patient\Patient::submitAppointment');
    $routes->get('appointments', 'Patient\Patient::appointments');
    $routes->get('records', 'Patient\Patient::records');
    $routes->get('treatment/(:num)', 'Patient\Patient::treatment/$1');
    $routes->get('profile', 'Patient\Patient::profile');
    // Patient appointment management (cancel only - edit/delete removed to avoid accidental changes)
    // $routes->get('appointments/edit/(:num)', 'Patient\Patient::editAppointment/$1');
    $routes->post('appointments/cancel/(:num)', 'Patient\Patient::cancelAppointment/$1');
    // $routes->post('appointments/delete/(:num)', 'Patient\Patient::deleteAppointment/$1');
    // $routes->post('appointments/update/(:num)', 'Patient\Patient::updateAppointment/$1');
    // Read-only view for appointment details
    $routes->get('appointments/view/(:num)', 'Patient\Patient::viewAppointment/$1');
    $routes->post('save-profile', 'Patient\Patient::saveProfile');
    // New patient modules
    $routes->get('billing', 'Patient\Patient::billing');
    $routes->get('invoice/(:num)', 'Patient\Patient::invoice/$1');
    $routes->get('invoice/(:num)/download', 'Patient\Patient::invoiceDownload/$1');
    $routes->get('messages', 'Patient\Patient::messages');
    $routes->get('forms', 'Patient\Patient::forms');
    $routes->get('prescriptions', 'Patient\Patient::prescriptions');
    $routes->get('prescriptions/(:num)', 'Patient\Patient::prescription/$1');
    $routes->get('prescriptions/(:num)/preview', 'Patient\Patient::previewPrescription/$1');
    $routes->get('prescriptions/(:num)/download-file', 'Patient\Patient::downloadPrescriptionFile/$1');
    $routes->get('treatment-plan', 'Patient\Patient::treatmentPlan');
    // Settings pages
    $routes->get('security', 'Patient\Patient::security');
    $routes->get('preferences', 'Patient\Patient::preferences');
    $routes->get('privacy', 'Patient\Patient::privacy');
    $routes->get('support', 'Patient\Patient::support');
    // Page routes for patient UI (prevent 404s from dashboard links)
    $routes->get('calendar', 'Patient\Patient::calendar');
    $routes->get('book-appointment', 'Patient\Patient::bookAppointment');
    $routes->post('book-appointment', 'Patient\Patient::submitAppointment');
    $routes->get('appointments', 'Patient\Patient::appointments');
    $routes->get('records', 'Patient\Patient::records');
    $routes->get('profile', 'Patient\Patient::profile');

    $routes->get('progress', 'TreatmentProgress::index/$1'); // View own treatment progress
    $routes->post('save-medical-history', 'Patient::saveMedicalHistory'); // Save medical history via AJAX
    $routes->get('get-medical-history/(:num)', 'Patient::getMedicalHistory/$1'); // Get medical history via AJAX
    $routes->get('get-treatments/(:num)', 'Patient::getPatientTreatments/$1'); // Get patient treatments via AJAX
    $routes->get('get-appointments/(:num)', 'Patient::getPatientAppointments/$1'); // Get patient appointments via AJAX
    $routes->get('get-bills/(:num)', 'Patient::getPatientBills/$1'); // Get patient bills via AJAX
    $routes->get('test-treatments', 'Patient::testTreatmentsEndpoint'); // Test treatments endpoint
    $routes->get('test-database', 'Patient::testDatabase'); // Test database connection
});

// API endpoints for patient-scoped appointment data
$routes->group('api', [], function($routes) {
    $routes->group('patient', ['filter' => 'auth'], function($routes) {
        $routes->get('appointments', 'Api\\PatientAppointments::index');
        $routes->post('check-conflicts', 'Api\\PatientAppointments::checkConflicts');
    });
});

// Patient Check-in routes (for staff/reception)
$routes->group('checkin', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Patient\PatientCheckin::index');
    $routes->post('process/(:num)', 'Patient\PatientCheckin::checkinPatient/$1');
});

// Treatment Queue routes (for dentists)
$routes->group('queue', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Medical\TreatmentQueue::index');
    $routes->post('call/(:num)', 'Medical\TreatmentQueue::callNext/$1');
    $routes->post('complete/(:num)', 'Medical\TreatmentQueue::completeTreatment/$1');
    $routes->get('status', 'Medical\TreatmentQueue::getQueueStatus'); // AJAX
});

// Staff routes (protected)
$routes->group('staff', ['filter' => 'auth'], function($routes) {
    $routes->get('dashboard', 'Staff\StaffController::dashboard');
    // Branch-scoped totals for staff dashboard (AJAX)
    $routes->get('totals', 'Staff\StaffController::totals');
    // Optional richer timeseries endpoint for staff dashboards
    $routes->get('stats', 'Staff\StaffController::stats');
    $routes->get('patients', 'Staff\StaffController::patients');
    $routes->get('patients/add', 'Staff\StaffController::addPatient');
    $routes->post('patients/store', 'Staff\StaffController::storePatient');
    $routes->post('patients/toggle/(:num)', 'Staff\StaffController::toggleStatus/$1');
    $routes->get('patients/get/(:num)', 'Staff\StaffController::getPatient/$1');
    $routes->post('patients/update/(:num)', 'Staff\StaffController::updatePatient/$1');
    $routes->get('appointments', 'Staff\StaffController::appointments');
    $routes->post('appointments/create', 'Staff\StaffController::createAppointment');
    $routes->post('appointments/checkConflicts', 'Staff\StaffController::checkConflicts');
    $routes->get('records', 'Staff\Staff::records');
    $routes->get('waitlist', 'Staff\StaffController::waitlist');
    // Allow staff to approve or decline appointments (matches admin/dentist endpoints)
    $routes->post('appointments/approve/(:num)', 'Staff\StaffController::approveAppointment/$1');
    $routes->post('appointments/decline/(:num)', 'Staff\StaffController::declineAppointment/$1');
    // Approve/reject patient-submitted change requests
    $routes->post('appointments/approve-change/(:num)', 'Staff::approveChangeRequest/$1');
    $routes->post('appointments/reject-change/(:num)', 'Staff::rejectChangeRequest/$1');
    // Approve/reject patient-submitted cancellation requests
    $routes->post('appointments/approve-cancel/(:num)', 'Staff::approveCancellation/$1');
    $routes->post('appointments/reject-cancel/(:num)', 'Staff::rejectCancellation/$1');

    // Staff role-scoped calendar endpoints
    $routes->post('calendar/day-appointments', 'StaffCalendarController::dayAppointments');
    $routes->post('calendar/available-slots', 'StaffCalendarController::availableSlots');
    $routes->post('calendar/check-conflicts', 'StaffCalendarController::checkConflicts');
});

// Public appointments AJAX endpoints (used by patient calendar JS)
$routes->post('appointments/day-appointments', 'Appointments::dayAppointments');
$routes->post('appointments/available-slots', 'Appointments::availableSlots');
$routes->post('appointments/check-conflicts', 'Appointments::checkConflicts');