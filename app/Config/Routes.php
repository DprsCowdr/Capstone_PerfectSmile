<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', to: 'Home::index');

// Debug routes for availability testing (development-only)
if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
    $routes->get('debug/availability', 'DebugAvailability::index');
    $routes->get('debug/availability/test', 'DebugAvailability::testCreate');
    $routes->get('debug/availability/manual', 'DebugAvailability::manualTest');
    $routes->get('debug/availability/direct', 'DebugAvailability::directDbTest');
    $routes->get('debug/availability/monday', 'DebugAvailability::mondayDebug');
    $routes->get('debug/availability/calendar-events', 'DebugAvailability::calendarEvents');

    // NOTE: debug/session route removed - debug controllers should not be present in production
}

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

// Debug-only admin helpers (register only in development)
if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
    $routes->get('debug/appointments', 'Debug::checkAppointments');
    $routes->get('debug/add-test', 'Debug::addTestAppointment');
    // Debug approve endpoint (dev-only)
    $routes->get('debug/approve-test/(:num)', 'Debug::approveTestAppointment/$1');
    // Debug list branch notifications
    $routes->get('debug/branch-notifications', 'Debug::listBranchNotifications');
    $routes->get('debug/smoke-run', 'Debug::smokeRun');
}


$routes->group('admin', ['filter' => 'auth'], function($routes) {
    // Main dashboard
    $routes->get('dashboard', 'AdminController::dashboard');
    // Admin-only preview endpoint for branch stats (used by staff/admin UI)
    $routes->get('preview-branch-stats', 'AdminController::previewBranchStats');
    
    // Branch management
    $routes->post('switch-branch', 'AdminController::switchBranch');
    
    // Patient management routes
    $routes->get('patients', 'AdminController::patients'); // → patients/index.php
    $routes->get('patients/add', 'AdminController::addPatient');
    $routes->post('patients/store', 'AdminController::storePatient');
    $routes->get('patients/toggle-status/(:num)', 'AdminController::toggleStatus/$1');
    $routes->get('patients/get/(:num)', 'AdminController::getPatient/$1');
    $routes->post('patients/update/(:num)', 'AdminController::updatePatient/$1');
    $routes->get('patients/appointments/(:num)', 'AdminController::getPatientAppointments/$1');
    $routes->get('patients/create-account/(:num)', 'AdminController::createAccount/$1'); // → patients/create.php
    $routes->post('patients/save-account/(:num)', 'AdminController::saveAccount/$1');
    
    // Patient Account Activation Routes
    $routes->get('patients/activation', 'AdminController::patientActivation');
    $routes->post('patients/activate/(:num)', 'AdminController::activatePatientAccount/$1');
    $routes->post('patients/deactivate/(:num)', 'AdminController::deactivatePatientAccount/$1');
    $routes->get('patient-checkups', 'DentalController::patientCheckups'); // → patients/checkups.php
    
    // Appointment management routes
    $routes->get('appointments', 'AdminController::appointments'); // → appointments/index.php
    $routes->post('appointments/create', 'AdminController::createAppointment');
    $routes->post('appointments/update/(:num)', 'AdminController::updateAppointment/$1');
    $routes->post('appointments/delete/(:num)', 'AdminController::deleteAppointment/$1');
    $routes->post('appointments/approve/(:num)', 'AdminController::approveAppointment/$1');
    $routes->post('appointments/decline/(:num)', 'AdminController::declineAppointment/$1');
    $routes->post('appointments/available-dentists', 'AdminController::getAvailableDentists');
    $routes->post('appointments/check-conflicts', 'AdminController::checkAppointmentConflicts');
    $routes->get('appointments/details/(:num)', 'AdminController::getAppointmentDetails/$1');
    $routes->get('waitlist', 'AdminController::waitlist'); // → appointments/waitlist.php

    // Role-scoped calendar AJAX endpoints (delegates to Appointments controller via thin wrapper)
    $routes->post('calendar/day-appointments', 'AdminCalendarController::dayAppointments');
    $routes->post('calendar/available-slots', 'AdminCalendarController::availableSlots');
    $routes->post('calendar/check-conflicts', 'AdminCalendarController::checkConflicts');
    
    // Dental management routes (moved to DentalController)
    $routes->get('dental-records', 'DentalController::records'); // → dental/records.php
    $routes->get('dental-records/create/(:num)', 'DentalController::createRecord/$1'); // → dental/create_record.php
    $routes->post('dental-records/store-basic', 'DentalController::storeBasicDentalRecord');
    $routes->get('dental-records/(:num)', 'DentalController::viewRecord/$1'); // → dental/view_record.php
    $routes->get('dental/record/(:num)', 'DentalController::viewRecord/$1'); // Alternative route for admin/dental/record/ID
    $routes->get('dental-charts', 'DentalController::charts'); // → dental/charts.php
    $routes->get('dental-charts/(:num)', 'DentalController::viewChart/$1'); // → dental/view_chart.php
    $routes->get('dental-charts/create/(:num)', 'DentalController::createChart/$1'); // → dental/create_chart.php
    $routes->get('dental-charts/edit/(:num)', 'DentalController::editChart/$1'); // → dental/edit_chart.php
    $routes->get('dental-charts/test-3d', 'DentalController::test3DViewer');
    $routes->post('dental-records/store', 'DentalController::storeDentalRecord');
    $routes->post('dental-records/update/(:num)', 'DentalController::updateDentalRecord/$1');
    $routes->get('records', 'AdminController::records'); // → dental/all_records.php
    $routes->delete('dental-records/delete/(:num)', 'AdminController::deleteRecord/$1'); // Delete dental record

    // Prescriptions management
    $routes->get('prescriptions', 'Prescriptions::index');
    $routes->get('prescriptions/create', 'Prescriptions::create');
    $routes->post('prescriptions/store', 'Prescriptions::store');
    $routes->get('prescriptions/edit/(:num)', 'Prescriptions::edit/$1');
    $routes->get('prescriptions/(:num)/edit', 'Prescriptions::edit/$1');
    $routes->post('prescriptions/update/(:num)', 'Prescriptions::update/$1');
    $routes->get('prescriptions/(:num)', 'Prescriptions::show/$1');
    $routes->get('prescriptions/(:num)/download', 'Prescriptions::downloadPdf/$1');
    $routes->get('prescriptions/(:num)/preview', 'Prescriptions::previewPdf/$1');
    $routes->get('prescriptions/(:num)/download-file', 'Prescriptions::downloadPdfFile/$1');
    $routes->delete('prescriptions/(:num)', 'Prescriptions::delete/$1');
    
    // Patient records popup routes
    $routes->get('patient-info/(:num)', 'AdminController::getPatientInfo/$1');
    $routes->post('patient-notes/(:num)', 'AdminController::updatePatientNotes/$1');
    $routes->get('patient-dental-records/(:num)', 'AdminController::getPatientDentalRecords/$1');
    $routes->get('patient-dental-chart/(:num)', 'AdminController::getPatientDentalChart/$1');
    $routes->get('patient-appointments/(:num)', 'AdminController::getPatientAppointmentsModal/$1');
    $routes->get('patient-treatments/(:num)', 'AdminController::getPatientTreatments/$1');
    $routes->get('patient-medical-records/(:num)', 'AdminController::getPatientMedicalRecords/$1');
    $routes->get('patient-invoice-history/(:num)', 'AdminController::getPatientInvoiceHistory/$1');
    $routes->get('patient-prescriptions/(:num)', 'AdminController::getPatientPrescriptions/$1');
    
    // Management routes
    $routes->get('services', 'AdminController::services'); // → management/services.php
    $routes->get('services/ajax-list', 'AdminController::servicesAjaxList'); // AJAX endpoint for services
    $routes->post('services/store', 'AdminController::storeService');
    $routes->get('services/(:num)', 'AdminController::getService/$1');
    $routes->post('services/update/(:num)', 'AdminController::updateService/$1');
    $routes->delete('services/delete/(:num)', 'AdminController::deleteService/$1');
        // Link 'role-permission' directly to the main RoleController index (canonical UI)
        $routes->get('role-permission', 'RoleController::index');
    // Role & Permission management (RoleController)
    $routes->get('roles', 'RoleController::index');
    $routes->get('roles/create', 'RoleController::create');
    $routes->post('roles/create', 'RoleController::store');
    $routes->get('roles/edit/(:num)', 'RoleController::edit/$1');
    $routes->post('roles/update/(:num)', 'RoleController::update/$1');
    $routes->get('roles/show/(:num)', 'RoleController::show/$1');
    $routes->post('roles/delete/(:num)', 'RoleController::delete/$1');
    $routes->match(['GET','POST'], 'roles/assign/(:num)', 'RoleController::assign/$1');
    $routes->post('roles/remove_user/(:num)/(:num)', 'RoleController::remove_user/$1/$2');
    $routes->get('roles/search-users', 'RoleController::searchUsers');
    $routes->get('roles/searchUsers', 'RoleController::searchUsers');
    // Backwards-compatible admin-prefixed routes (views use admin/roles/* URLs)
    $routes->get('admin/roles', 'RoleController::index');
    $routes->get('admin/roles/create', 'RoleController::create');
    $routes->post('admin/roles/create', 'RoleController::store');
    $routes->get('admin/roles/edit/(:num)', 'RoleController::edit/$1');
    $routes->post('admin/roles/update/(:num)', 'RoleController::update/$1');
    $routes->get('admin/roles/show/(:num)', 'RoleController::show/$1');
    $routes->post('admin/roles/delete/(:num)', 'RoleController::delete/$1');
    $routes->match(['GET','POST'], 'admin/roles/assign/(:num)', 'RoleController::assign/$1');
    $routes->post('admin/roles/remove_user/(:num)/(:num)', 'RoleController::remove_user/$1/$2');
    $routes->get('admin/roles/search-users', 'RoleController::searchUsers');
    $routes->get('admin/roles/searchUsers', 'RoleController::searchUsers');
    // Branch management handled by BranchController
    $routes->get('branches', 'BranchController::index');
    $routes->get('branches/create', 'BranchController::create');
    $routes->post('branches', 'BranchController::store');
    $routes->get('branches/(:num)', 'BranchController::show/$1');
    $routes->get('branches/(:num)/edit', 'BranchController::edit/$1');
    $routes->post('branches/update/(:num)', 'BranchController::update/$1');
    $routes->post('branches/delete/(:num)', 'BranchController::delete/$1');
    // Keep legacy save-hours endpoint (if used elsewhere)
    $routes->post('branches/(:num)/save-hours', 'AdminController::saveBranchHours/$1');
    $routes->get('settings', 'AdminController::settings'); // → management/settings.php
    // Message templates editor (writable JSON)
    $routes->get('message-templates', 'Admin\MessageTemplates::index');
    // AJAX endpoint to fetch templates as JSON (for admin UI)
    $routes->get('message-templates/ajax', 'Admin\MessageTemplates::fetch');
    $routes->post('message-templates/save', 'Admin\MessageTemplates::save');
    // Grace periods save endpoint
    $routes->post('grace-periods/save', 'Admin\GracePeriods::save');
    
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
    $routes->get('users', 'AdminController::users'); // → users/index.php
    $routes->get('users/add', 'AdminController::addUser'); // → users/add.php
    $routes->post('users/store', 'AdminController::storeUser');
    $routes->get('users/edit/(:num)', 'AdminController::editUser/$1'); // → users/edit.php
    $routes->post('users/update/(:num)', 'AdminController::updateUser/$1');
    $routes->get('users/toggle-status/(:num)', 'AdminController::toggleUserStatus/$1');
    $routes->get('users/delete/(:num)', 'AdminController::deleteUser/$1');
    
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
$routes->post('staff/notifications/handle/(:num)', 'Staff::markNotificationHandled/$1');

// Availability events (calendar-wide) - authenticated check is performed in controller
$routes->match(['GET','POST'], 'calendar/availability-events', 'Availability::events');

// Checkup routes (accessible by admin and doctor)
$routes->group('checkup', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Checkup::index');
    $routes->get('start/(:num)', 'Checkup::startCheckup/$1');
    $routes->get('patient/(:num)', 'Checkup::patientCheckup/$1');
    $routes->post('save/(:num)', 'Checkup::saveCheckup/$1');
    $routes->get('no-show/(:num)', 'Checkup::markNoShow/$1');
    $routes->post('cancel/(:num)', 'Checkup::cancelAppointment/$1');
    $routes->get('record/(:num)', 'Checkup::viewRecord/$1');
    $routes->get('patient-history/(:num)', 'Checkup::getPatientHistory/$1');
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        $routes->get('debug/(:num)', 'Checkup::debug/$1'); // Debug specific appointment
        $routes->get('debug', 'Checkup::debug'); // Debug today's appointments
    }
    
    // Services management for checkups
    $routes->get('(:num)/services', 'CheckupServices::getAppointmentServices/$1');
    $routes->post('(:num)/services', 'CheckupServices::addService/$1');
    $routes->delete('(:num)/services/(:num)', 'CheckupServices::removeService/$1/$2');
    $routes->get('services/search', 'CheckupServices::searchServices');
    $routes->get('services/all', 'CheckupServices::getAllServices');
});

// Invoice routes removed

// Dentist routes (protected)
// Dentist routes (protected)
$routes->group('dentist', ['filter' => 'auth'], function($routes) {
    $routes->get('dashboard', 'Dentist::dashboard');
    $routes->get('stats', 'Dentist::stats');
    $routes->get('appointments', 'Dentist::appointments');
    // Dentist availability management page (full-page UI)
    $routes->get('availability', 'DentistAvailability::index');
    $routes->post('availability/set', 'Dentist::setAvailability');
    // Availability endpoints (events can be consumed by any authenticated calendar view)
    $routes->match(['GET','POST'], 'calendar/availability-events', 'Availability::events');
    $routes->post('availability/create', 'Availability::create');
    $routes->post('availability/createRecurring', 'Availability::createRecurring');
    $routes->get('availability/list', 'Availability::list');
    $routes->post('availability/update', 'Availability::update');
    // Allow listing availability for a specific user (dentist) within dentist-scoped routes
    $routes->match(['GET','POST'], 'availability/listForUser', 'Availability::listForUser');
    $routes->post('availability/delete', 'Availability::delete');
    $routes->post('appointments/approve/(:num)', 'Dentist::approveAppointment/$1');
    $routes->post('appointments/decline/(:num)', 'Dentist::declineAppointment/$1');
    
    // Additional appointment management routes (same as admin)
    $routes->post('appointments/create', 'Dentist::createAppointment');
    $routes->post('appointments/update/(:num)', 'Dentist::updateAppointment/$1');
    $routes->post('appointments/delete/(:num)', 'Dentist::deleteAppointment/$1');
    $routes->post('appointments/available-dentists', 'Dentist::getAvailableDentists');
    $routes->post('appointments/check-conflicts', 'Dentist::checkAppointmentConflicts');
    $routes->get('appointments/details/(:num)', 'Dentist::getAppointmentDetails/$1');

    // Dentist role-scoped calendar endpoints
    $routes->post('calendar/day-appointments', 'DentistCalendarController::dayAppointments');
    $routes->post('calendar/available-slots', 'DentistCalendarController::availableSlots');
    $routes->post('calendar/check-conflicts', 'DentistCalendarController::checkConflicts');
    
    // Patients Module (accessible by dentist)
    $routes->get('patients', 'Dentist::patients');
    $routes->get('patients/search', 'Dentist::searchPatients');
    $routes->get('patients/(:num)', 'Dentist::patientDetails/$1');
    
    // Additional patient management routes (same as admin)
    $routes->get('patients/add', 'Dentist::addPatient');
    $routes->post('patients/store', 'Dentist::storePatient');
    $routes->get('patients/toggle-status/(:num)', 'Dentist::toggleStatus/$1');
    $routes->get('patients/get/(:num)', 'Dentist::getPatient/$1');
    $routes->post('patients/update/(:num)', 'Dentist::updatePatient/$1');
    $routes->get('patients/appointments/(:num)', 'Dentist::getPatientAppointments/$1');
    
    // Dental Records (Step 3: Checkup/Consultation)
    $routes->get('patient-records/(:num)', 'Dentist::patientRecords/$1');
    $routes->get('dental-chart/(:num)', 'Dentist::dentalChart/$1');
    $routes->post('records/create', 'Dentist::createRecord');
    
    // Procedures (Step 5 & 6: Procedure Scheduling & Execution)
    $routes->get('procedures', 'Dentist::procedures');
    $routes->post('procedures/schedule', 'Dentist::scheduleProcedure');
    $routes->get('procedures/(:num)', 'Dentist::procedureDetails/$1');
});

// Patient routes (protected)
$routes->group('patient', ['filter' => 'auth'], function($routes) {
    $routes->get('dashboard', 'Patient::dashboard');
    $routes->get('calendar', 'Patient::calendar');
    $routes->get('book-appointment', 'Patient::bookAppointment');
    $routes->post('book-appointment', 'Patient::submitAppointment');
    $routes->get('appointments', 'Patient::appointments');
    $routes->get('records', 'Patient::records');
    $routes->get('treatment/(:num)', 'Patient::treatment/$1');
    $routes->get('profile', 'Patient::profile');
    // Patient appointment management (cancel only - edit/delete removed to avoid accidental changes)
    // $routes->get('appointments/edit/(:num)', 'Patient::editAppointment/$1');
    $routes->post('appointments/cancel/(:num)', 'Patient::cancelAppointment/$1');
    $routes->post('appointments/delete/(:num)', 'Patient::deleteAppointment/$1');
    // $routes->post('appointments/update/(:num)', 'Patient::updateAppointment/$1');
    // Read-only view for appointment details
    $routes->get('appointments/view/(:num)', 'Patient::viewAppointment/$1');
    // Patient appointment details endpoint for AJAX (read-only, own appointments only)
    $routes->get('appointments/details/(:num)', 'Patient::getAppointmentDetails/$1');
    // Patient services endpoint (for service details lookup)
    $routes->get('services/(:num)', 'Patient::getService/$1');
    $routes->post('save-profile', 'Patient::saveProfile');
    // New patient modules
    $routes->get('billing', 'Patient::billing');
    $routes->get('invoice/(:num)', 'Patient::invoice/$1');
    $routes->get('invoice/(:num)/download', 'Patient::invoiceDownload/$1');
    $routes->get('messages', 'Patient::messages');
    $routes->get('forms', 'Patient::forms');
    $routes->get('prescriptions', 'Patient::prescriptions');
    $routes->get('prescriptions/(:num)', 'Patient::prescription/$1');
    $routes->get('prescriptions/(:num)/preview', 'Patient::previewPrescription/$1');
    $routes->get('prescriptions/(:num)/download-file', 'Patient::downloadPrescriptionFile/$1');
    $routes->get('treatment-plan', 'Patient::treatmentPlan');
    // Settings pages
    $routes->get('security', 'Patient::security');
    $routes->get('preferences', 'Patient::preferences');
    $routes->get('privacy', 'Patient::privacy');
    $routes->get('support', 'Patient::support');
    // Page routes for patient UI (prevent 404s from dashboard links)
    $routes->get('calendar', 'Patient::calendar');
    $routes->get('book-appointment', 'Patient::bookAppointment');
    $routes->post('book-appointment', 'Patient::submitAppointment');
    $routes->get('appointments', 'Patient::appointments');
    $routes->get('records', 'Patient::records');
    $routes->get('profile', 'Patient::profile');

    $routes->get('progress', 'TreatmentProgress::index/$1'); // View own treatment progress
    $routes->post('save-medical-history', 'Patient::saveMedicalHistory'); // Save medical history via AJAX
    $routes->get('get-medical-history/(:num)', 'Patient::getMedicalHistory/$1'); // Get medical history via AJAX
    $routes->get('get-treatments/(:num)', 'Patient::getPatientTreatments/$1'); // Get patient treatments via AJAX
    $routes->get('get-appointments/(:num)', 'Patient::getPatientAppointments/$1'); // Get patient appointments via AJAX
    $routes->get('get-bills/(:num)', 'Patient::getPatientBills/$1'); // Get patient bills via AJAX
    $routes->get('test-treatments', 'Patient::testTreatmentsEndpoint'); // Test treatments endpoint
    $routes->get('test-database', 'Patient::testDatabase'); // Test database connection
    $routes->get('debug-records', 'Patient::debugRecords'); // Debug records loading
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
    $routes->get('/', 'PatientCheckin::index');
    $routes->post('process/(:num)', 'PatientCheckin::checkinPatient/$1');
});

// Treatment Queue routes (for dentists)
$routes->group('queue', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'TreatmentQueue::index');
    $routes->post('call/(:num)', 'TreatmentQueue::callNext/$1');
        $routes->post('call-auto', 'TreatmentQueue::callNextAuto');
        $routes->post('reschedule', 'TreatmentQueue::rescheduleAppointment');
    $routes->post('complete/(:num)', 'TreatmentQueue::completeTreatment/$1');
    $routes->get('status', 'TreatmentQueue::getQueueStatus'); // AJAX
});

// Staff routes (protected)
$routes->group('staff', ['filter' => 'auth'], function($routes) {
    $routes->get('dashboard', 'StaffController::dashboard');
    // Branch-scoped totals for staff dashboard (AJAX)
    $routes->get('totals', 'StaffController::totals');
    // Optional richer timeseries endpoint for staff dashboards
    $routes->get('stats', 'StaffController::stats');
    $routes->get('patients', 'StaffController::patients');
    $routes->get('patients/add', 'StaffController::addPatient');
    $routes->post('patients/store', 'StaffController::storePatient');
    $routes->post('patients/toggle/(:num)', 'StaffController::toggleStatus/$1');
    $routes->get('patients/get/(:num)', 'StaffController::getPatient/$1');
    $routes->post('patients/update/(:num)', 'StaffController::updatePatient/$1');
    $routes->get('appointments', 'StaffController::appointments');
    $routes->post('appointments/create', 'StaffController::createAppointment');
    $routes->post('appointments/checkConflicts', 'StaffController::checkConflicts');
    // Accept hyphenated route variant for consistency with other role groups
    $routes->post('appointments/check-conflicts', 'StaffController::checkConflicts');
    $routes->get('records', 'Staff::records');
    $routes->get('waitlist', 'StaffController::waitlist');
    // Allow staff to approve or decline appointments (matches admin/dentist endpoints)
    $routes->post('appointments/approve/(:num)', 'StaffController::approveAppointment/$1');
    $routes->post('appointments/decline/(:num)', 'StaffController::declineAppointment/$1');
    // Get appointment details (for modal display)
    $routes->get('appointments/details/(:num)', 'StaffController::getAppointmentDetails/$1');
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
$routes->get('appointments/available-slots', 'Appointments::availableSlots'); // Allow GET for debug mode
$routes->post('appointments/check-conflicts', 'Appointments::checkConflicts');