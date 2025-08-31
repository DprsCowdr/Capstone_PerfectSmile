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
    
    // Patient records popup routes
    $routes->get('patient-info/(:num)', 'AdminController::getPatientInfo/$1');
    $routes->post('patient-notes/(:num)', 'AdminController::updatePatientNotes/$1');
    $routes->get('patient-dental-records/(:num)', 'AdminController::getPatientDentalRecords/$1');
    $routes->get('patient-dental-chart/(:num)', 'AdminController::getPatientDentalChart/$1');
    $routes->get('patient-appointments/(:num)', 'AdminController::getPatientAppointmentsModal/$1');
    $routes->get('patient-treatments/(:num)', 'AdminController::getPatientTreatments/$1');
    $routes->get('patient-medical-records/(:num)', 'AdminController::getPatientMedicalRecords/$1');
    
    // Management routes
    $routes->get('services', 'AdminController::services'); // → management/services.php
    $routes->get('role-permission', 'AdminController::rolePermission'); // → management/roles.php
    $routes->get('branches', 'AdminController::branches'); // → management/branches.php
    $routes->post('branches/(:num)/save-hours', 'AdminController::saveBranchHours/$1');
    $routes->get('settings', 'AdminController::settings'); // → management/settings.php
    
    // Procedure management routes
    $routes->get('procedures', 'Admin\ProcedureController::index');
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
    
    // Billing routes
});

// Staff notification handling (mark branch notification handled)
$routes->post('staff/notifications/handle/(:num)', 'Staff::markNotificationHandled/$1');

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
    $routes->get('debug/(:num)', 'Checkup::debug/$1'); // Debug specific appointment
    $routes->get('debug', 'Checkup::debug'); // Debug today's appointments
});

// Dentist routes (protected)
// Dentist routes (protected)
$routes->group('dentist', ['filter' => 'auth'], function($routes) {
    $routes->get('dashboard', 'Dentist::dashboard');
    $routes->get('stats', 'Dentist::stats');
    $routes->get('appointments', 'Dentist::appointments');
    $routes->post('availability/set', 'Dentist::setAvailability');
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
    $routes->get('profile', 'Patient::profile');
    // Patient appointment management (cancel only - edit/delete removed to avoid accidental changes)
    // $routes->get('appointments/edit/(:num)', 'Patient::editAppointment/$1');
    $routes->post('appointments/cancel/(:num)', 'Patient::cancelAppointment/$1');
    // $routes->post('appointments/delete/(:num)', 'Patient::deleteAppointment/$1');
    // $routes->post('appointments/update/(:num)', 'Patient::updateAppointment/$1');
    // Read-only view for appointment details
    $routes->get('appointments/view/(:num)', 'Patient::viewAppointment/$1');
    $routes->post('save-profile', 'Patient::saveProfile');
    // New patient modules
    $routes->get('billing', 'Patient::billing');
    $routes->get('messages', 'Patient::messages');
    $routes->get('forms', 'Patient::forms');
    $routes->get('prescriptions', 'Patient::prescriptions');
    $routes->get('treatment-plan', 'Patient::treatmentPlan');
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

// Temporary workaround: Remove auth filter from process route
$routes->post('checkin/process/(:num)', 'PatientCheckin::checkinPatient/$1');

// Treatment Queue routes (for dentists)
$routes->group('queue', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'TreatmentQueue::index');
    $routes->post('call/(:num)', 'TreatmentQueue::callNext/$1');
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
    $routes->get('waitlist', 'StaffController::waitlist');
    // Allow staff to approve or decline appointments (matches admin/dentist endpoints)
    $routes->post('appointments/approve/(:num)', 'StaffController::approveAppointment/$1');
    $routes->post('appointments/decline/(:num)', 'StaffController::declineAppointment/$1');
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