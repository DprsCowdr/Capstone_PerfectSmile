<?php

namespace App\Controllers;

use App\Traits\AdminAuthTrait;
use App\Services\PatientService;
use App\Services\AppointmentService;
use App\Services\DashboardService;
use App\Controllers\Auth;

class Admin extends BaseController
{
    use AdminAuthTrait;
    
    protected $patientService;
    protected $appointmentService;
    protected $dashboardService;
    
    public function __construct()
    {
        $this->patientService = new PatientService();
        $this->appointmentService = new AppointmentService();
        $this->dashboardService = new DashboardService();
    }

    // ==================== DASHBOARD ====================
    public function dashboard()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $appointmentData = $this->appointmentService->getDashboardData();
        $statistics = $this->dashboardService->getStatistics();
        
        return view('admin/dashboard', array_merge([
            'user' => $user
        ], $appointmentData, $statistics));
    }

    // ==================== PATIENT MANAGEMENT ====================
    public function patients()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        
        return view('admin/patients/index', [
            'user' => $user,
            'patients' => $this->patientService->getAllPatients()
        ]);
    }

    public function addPatient()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        
        return view('admin/patients/add', ['user' => $user]);
    }

    public function storePatient()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $formData = $this->request->getPost();
        
        if (!$this->patientService->validatePatientData($formData)) {
            return redirect()->back()->withInput()->with('error', $this->patientService->getValidationErrors());
        }

        if ($this->patientService->createPatient($formData)) {
            return redirect()->to('/admin/patients')->with('success', 'Patient added successfully.');
        }
        
        return redirect()->back()->withInput()->with('error', 'Failed to add patient.');
    }

    public function getPatient($id)
    {
        $user = $this->checkAdminAuthApi();
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }
        
        $patient = $this->patientService->getPatient($id);
        
        if (!$patient) {
            return $this->response->setJSON(['error' => 'Patient not found']);
        }
        
        return $this->response->setJSON($patient);
    }

    public function updatePatient($id)
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        
        $patient = $this->patientService->getPatient($id);
        if (!$patient) {
            return redirect()->to('/admin/patients')->with('error', 'Patient not found.');
        }
        
        $formData = $this->request->getPost();
        
        if (!$this->patientService->validatePatientData($formData)) {
            return redirect()->back()->withInput()->with('error', $this->patientService->getValidationErrors());
        }
        
        if ($this->patientService->updatePatient($id, $formData)) {
            return redirect()->to('/admin/patients')->with('success', 'Patient updated successfully.');
        }
        
        return redirect()->back()->withInput()->with('error', 'Failed to update patient.');
    }

    public function toggleStatus($id)
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        
        if (!$this->patientService->getPatient($id)) {
            return redirect()->to('/admin/patients')->with('error', 'Patient not found.');
        }
        
        if ($this->patientService->toggleStatus($id)) {
            return redirect()->to('/admin/patients')->with('success', 'Patient status updated successfully.');
        }
        
        return redirect()->to('/admin/patients')->with('error', 'Failed to update patient status.');
    }

    public function createAccount($id)
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        
        $patient = $this->patientService->getPatient($id);
        if (!$patient) {
            return redirect()->to('/admin/patients')->with('error', 'Patient not found.');
        }
        
        return view('admin/patients/create', ['user' => $user, 'patient' => $patient]);
    }

    public function saveAccount($id)
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        
        if (!$this->patientService->getPatient($id)) {
            return redirect()->to('/admin/patients')->with('error', 'Patient not found.');
        }
        
        $password = $this->request->getPost('password');
        
        if ($this->patientService->createAccount($id, $password)) {
            return redirect()->to('/admin/patients')->with('success', 'Account created for patient.');
        }
        
        return redirect()->back()->with('error', 'Password must be at least 6 characters.');
    }

    public function getPatientAppointments($patientId)
    {
        $user = $this->checkAdminAuthApi();
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }

        $result = $this->appointmentService->getPatientAppointments($patientId);
        return $this->response->setJSON($result);
    }

    // ==================== APPOINTMENT MANAGEMENT ====================
    public function appointments()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        
        $formData = $this->dashboardService->getFormData();
        
        return view('admin/appointments/index', array_merge([
            'user' => $user,
            'appointments' => $this->appointmentService->getAllAppointments()
        ], $formData));
    }

    public function createAppointment()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $data = [
            'branch_id' => $this->request->getPost('branch'),
            'user_id' => $this->request->getPost('patient'),
            'dentist_id' => $this->request->getPost('dentist') ?: null,
            'appointment_date' => $this->request->getPost('date'),
            'appointment_time' => $this->request->getPost('time'),
            'appointment_type' => $this->request->getPost('appointment_type') ?? 'scheduled',
            'remarks' => $this->request->getPost('remarks')
        ];

        // For admin-created appointments, ALL scheduled appointments go through waitlist approval
        if ($data['appointment_type'] === 'scheduled') {
            // All scheduled appointments go through waitlist approval process
            $data['approval_status'] = 'pending';
            $data['status'] = 'pending_approval';
        } else if ($data['appointment_type'] === 'walkin') {
            // Walk-in appointments are auto-approved
            $data['approval_status'] = 'auto_approved';
            $data['status'] = 'confirmed';
        }

        $result = $this->appointmentService->createAppointment($data);
        
        session()->setFlashdata($result['success'] ? 'success' : 'error', $result['message']);
        
        if ($result['success']) {
            return redirect()->to('/admin/appointments');
        }
        
        return redirect()->back();
    }

    public function approveAppointment($id)
    {
        try {
            // Check authentication first
            $user = Auth::getCurrentUser();
            if (!$user || $user['user_type'] !== 'admin') {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Authentication failed']);
                }
                return redirect()->to('/dashboard');
            }

            $dentistId = $this->request->getPost('dentist_id');
            log_message('info', "Admin approving appointment ID: {$id}, Dentist ID: " . ($dentistId ?: 'null'));
            
            // Get appointment details before approval for logging
            $appointmentModel = new \App\Models\AppointmentModel();
            $appointment = $appointmentModel->find($id);
            if ($appointment) {
                log_message('info', "Appointment before approval: " . json_encode($appointment));
            }
            
            $result = $this->appointmentService->approveAppointment($id, $dentistId);
            
            log_message('info', "Appointment approval result: " . json_encode($result));
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON($result);
            }
            
            session()->setFlashdata($result['success'] ? 'success' : 'error', $result['message']);
            return redirect()->back();
        } catch (\Exception $e) {
            log_message('error', "Exception in approveAppointment: " . $e->getMessage());
            log_message('error', "Stack trace: " . $e->getTraceAsString());
            
            $errorResult = ['success' => false, 'message' => 'An error occurred while approving the appointment: ' . $e->getMessage()];
            
            // Always return JSON for AJAX requests, even on exceptions
            if ($this->request->isAJAX()) {
                return $this->response->setJSON($errorResult);
            }
            
            session()->setFlashdata('error', $errorResult['message']);
            return redirect()->back();
        }
    }

    public function declineAppointment($id)
    {
        try {
            // Check authentication first
            $user = Auth::getCurrentUser();
            if (!$user || $user['user_type'] !== 'admin') {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Authentication failed']);
                }
                return redirect()->to('/dashboard');
            }

            $reason = $this->request->getPost('reason');
            if (empty($reason)) {
                $errorResult = ['success' => false, 'message' => 'Decline reason is required'];
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON($errorResult);
                }
                session()->setFlashdata('error', $errorResult['message']);
                return redirect()->back();
            }

            log_message('info', "Admin declining appointment ID: {$id}, Reason: {$reason}");
            
            $result = $this->appointmentService->declineAppointment($id, $reason);
            
            log_message('info', "Appointment decline result: " . json_encode($result));
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON($result);
            }
            
            session()->setFlashdata($result['success'] ? 'success' : 'error', $result['message']);
            return redirect()->back();
        } catch (\Exception $e) {
            log_message('error', "Exception in declineAppointment: " . $e->getMessage());
            log_message('error', "Stack trace: " . $e->getTraceAsString());
            
            $errorResult = ['success' => false, 'message' => 'An error occurred while declining the appointment: ' . $e->getMessage()];
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON($errorResult);
            }
            
            session()->setFlashdata('error', $errorResult['message']);
            return redirect()->back();
        }
    }

    public function updateAppointment($id)
    {
        $data = $this->request->getPost();
        
        if ($this->request->isAJAX()) {
            $jsonData = json_decode($this->request->getBody(), true);
            if ($jsonData) {
                $data = array_merge($data, $jsonData);
            }
        }
        
        $success = $this->appointmentService->updateAppointment($id, $data);
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => $success,
                'message' => $success ? 'Appointment updated successfully' : 'Failed to update appointment'
            ]);
        }
        
        session()->setFlashdata($success ? 'success' : 'error', 
                               $success ? 'Appointment updated successfully' : 'Failed to update appointment');
        
        return redirect()->to('/admin/appointments');
    }

    public function deleteAppointment($id)
    {
        $success = $this->appointmentService->deleteAppointment($id);
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => $success,
                'message' => $success ? 'Appointment deleted successfully' : 'Failed to delete appointment'
            ]);
        }
        
        session()->setFlashdata($success ? 'success' : 'error', 
                               $success ? 'Appointment deleted successfully' : 'Failed to delete appointment');
        
        return redirect()->to('/admin/appointments');
    }

    public function getAvailableDentists()
    {
        $user = $this->checkAdminAuthApi();
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }

        $date = $this->request->getPost('date');
        $time = $this->request->getPost('time');
        $branchId = $this->request->getPost('branch_id');

        $result = $this->appointmentService->getAvailableDentists($date, $time, $branchId);
        return $this->response->setJSON($result);
    }

    // ==================== SIMPLE VIEW METHODS ====================
    public function services()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        return view('admin/management/services', ['user' => $user]);
    }

    public function waitlist()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        // Get pending appointments that need approval
        $appointmentModel = new \App\Models\AppointmentModel();
        $pendingAppointments = $appointmentModel->getPendingApprovalAppointments();

        // Get form data for creating appointments
        $formData = $this->dashboardService->getFormData();
        
        return view('admin/appointments/waitlist', array_merge([
            'user' => $user,
            'pendingAppointments' => $pendingAppointments
        ], $formData));
    }

    public function procedures()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        return view('admin/management/procedures', ['user' => $user]);
    }

    public function records()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        return view('admin/dental/all_records', ['user' => $user]);
    }

    // ==================== CHECKUP MODULES ====================
    
    /**
     * View all dental records
     */
    public function dentalRecords()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $dentalRecordModel = new \App\Models\DentalRecordModel();
        $appointmentModel = new \App\Models\AppointmentModel();
        
        // Get all dental records with patient and dentist information
        $records = $dentalRecordModel->select('dental_record.*, patient.name as patient_name, patient.email as patient_email, dentist.name as dentist_name, appointments.appointment_datetime')
                                   ->join('user as patient', 'patient.id = dental_record.user_id')
                                   ->join('user as dentist', 'dentist.id = dental_record.dentist_id')
                                   ->join('appointments', 'appointments.id = dental_record.appointment_id', 'left')
                                   ->orderBy('record_date', 'DESC')
                                   ->findAll();

        // Get appointments without dental records (only approved appointments)
        $appointmentsWithoutRecords = $appointmentModel->select('appointments.*, patient.name as patient_name, dentist.name as dentist_name, branches.name as branch_name')
                                                      ->join('user as patient', 'patient.id = appointments.user_id')
                                                      ->join('user as dentist', 'dentist.id = appointments.dentist_id')
                                                      ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                                      ->where('appointments.status', 'confirmed')
                                                      ->where('appointments.approval_status', 'approved') // Only approved appointments
                                                      ->whereNotIn('appointments.id', function($builder) {
                                                          $builder->select('appointment_id')->from('dental_record');
                                                      })
                                                      ->orderBy('appointment_datetime', 'DESC')
                                                      ->findAll();

        return view('admin/dental/records', [
            'user' => $user,
            'records' => $records,
            'appointmentsWithoutRecords' => $appointmentsWithoutRecords
        ]);
    }

    /**
     * View specific dental record with chart
     */
    public function viewDentalRecord($recordId)
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $dentalRecordModel = new \App\Models\DentalRecordModel();
        $dentalChartModel = new \App\Models\DentalChartModel();
        
        $record = $dentalRecordModel->getRecordWithChart($recordId);
        
        if (!$record) {
            session()->setFlashdata('error', 'Dental record not found');
            return redirect()->to('/admin/dental-records');
        }

        return view('admin/dental/view_record', [
            'user' => $user,
            'record' => $record
        ]);
    }

    /**
     * View all dental charts
     */
    public function dentalCharts()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $dentalChartModel = new \App\Models\DentalChartModel();
        $dentalRecordModel = new \App\Models\DentalRecordModel();
        $appointmentModel = new \App\Models\AppointmentModel();
        
        // Get all approved appointments with dental charts
        $appointments = $appointmentModel->select('appointments.*, patient.name as patient_name, dentist.name as dentist_name, branches.name as branch_name')
                                        ->join('user as patient', 'patient.id = appointments.user_id')
                                        ->join('user as dentist', 'dentist.id = appointments.dentist_id')
                                        ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                        ->where('appointments.status', 'confirmed')
                                        ->where('appointments.approval_status', 'approved') // Only approved appointments
                                        ->orderBy('appointment_datetime', 'DESC')
                                        ->findAll();

        // Check which appointments have dental charts
        foreach ($appointments as &$appointment) {
            // First check if there's a dental record for this appointment
            $dentalRecord = $dentalRecordModel->where('appointment_id', $appointment['id'])->first();
            
            if ($dentalRecord) {
                // Check if there's a dental chart for this record
                $chart = $dentalChartModel->select('id')->where('dental_record_id', $dentalRecord['id'])->first();
                $appointment['has_chart'] = !empty($chart);
                $appointment['dental_record_id'] = $dentalRecord['id'];
            } else {
                $appointment['has_chart'] = false;
                $appointment['dental_record_id'] = null;
            }
        }

        return view('admin/dental/charts', [
            'user' => $user,
            'appointments' => $appointments
        ]);
    }

    /**
     * View specific dental chart
     */
    public function viewDentalChart($appointmentId)
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $appointmentModel = new \App\Models\AppointmentModel();
        $dentalChartModel = new \App\Models\DentalChartModel();
        $dentalRecordModel = new \App\Models\DentalRecordModel();
        
        // Get appointment details
        $appointment = $appointmentModel->select('appointments.*, patient.name as patient_name, dentist.name as dentist_name, branches.name as branch_name')
                                      ->join('user as patient', 'patient.id = appointments.user_id')
                                      ->join('user as dentist', 'dentist.id = appointments.dentist_id')
                                      ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                      ->find($appointmentId);

        if (!$appointment) {
            session()->setFlashdata('error', 'Appointment not found');
            return redirect()->to('/admin/dental-charts');
        }

        // Get related dental record
        $dentalRecord = $dentalRecordModel->where('appointment_id', $appointmentId)->first();
        // Get dental chart data by dental_record_id
        $dentalChart = $dentalRecord ? $dentalChartModel->getRecordChart($dentalRecord['id']) : [];

        // Get teeth layout for display
        $teethLayout = $dentalChartModel->getToothLayout();

        return view('admin/dental/view_chart', [
            'user' => $user,
            'appointment' => $appointment,
            'dentalChart' => $dentalChart,
            'dentalRecord' => $dentalRecord,
            'teethLayout' => $teethLayout
        ]);
    }

    /**
     * Patient checkup overview
     */
    public function patientCheckups()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $userModel = new \App\Models\UserModel();
        $dentalRecordModel = new \App\Models\DentalRecordModel();
        $appointmentModel = new \App\Models\AppointmentModel();
        $dentalChartModel = new \App\Models\DentalChartModel();
        
        // Get all patients with their checkup statistics
        $patients = $userModel->where('user_type', 'patient')
                             ->where('status', 'active')
                             ->orderBy('name', 'ASC')
                             ->findAll();
        
        foreach ($patients as &$patient) {
            // Get total appointments
            $patient['total_appointments'] = $appointmentModel->where('user_id', $patient['id'])->countAllResults();
            
            // Get total dental records
            $patient['total_records'] = $dentalRecordModel->where('user_id', $patient['id'])->countAllResults();
            
            // Get last checkup date
            $lastRecord = $dentalRecordModel->where('user_id', $patient['id'])
                                          ->orderBy('record_date', 'DESC')
                                          ->first();
            $patient['last_checkup'] = $lastRecord ? $lastRecord['record_date'] : null;
            
            // Get teeth needing treatment
            $patient['teeth_needing_treatment'] = $dentalChartModel->getTeethNeedingTreatment($patient['id']);
            $patient['treatment_count'] = count($patient['teeth_needing_treatment']);
        }

        return view('admin/patients/checkups', [
            'user' => $user,
            'patients' => $patients
        ]);
    }

    /**
     * Show form to create a new dental record (Admin)
     */
    public function createDentalRecord($appointmentId)
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $appointmentModel = new \App\Models\AppointmentModel();
        
        // Get appointment details
        $appointment = $appointmentModel->select('appointments.*, patient.name as patient_name, dentist.name as dentist_name, branches.name as branch_name')
                                      ->join('user as patient', 'patient.id = appointments.user_id')
                                      ->join('user as dentist', 'dentist.id = appointments.dentist_id')
                                      ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                      ->find($appointmentId);

        if (!$appointment) {
            session()->setFlashdata('error', 'Appointment not found');
            return redirect()->to('/admin/dental-records');
        }

        // Check if record already exists
        $dentalRecordModel = new \App\Models\DentalRecordModel();
        $existingRecord = $dentalRecordModel->where('appointment_id', $appointmentId)->first();
        
        if ($existingRecord) {
            session()->setFlashdata('error', 'Dental record already exists for this appointment');
            return redirect()->to('/admin/dental-records/' . $existingRecord['id']);
        }

        return view('admin/dental/create_record', [
            'user' => $user,
            'appointment' => $appointment
        ]);
    }

    /**
     * Store new dental record only (Admin)
     */
    public function storeBasicDentalRecord()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        if ($this->request->getMethod() === 'POST') {
            $dentalRecordModel = new \App\Models\DentalRecordModel();
            $appointmentModel = new \App\Models\AppointmentModel();
            
            // Get the datetime field (either next_appointment_datetime or next_appointment_date for backward compatibility)
            $nextAppointmentDatetime = $this->request->getPost('next_appointment_datetime') ?: $this->request->getPost('next_appointment_date');
            
            $recordData = [
                'user_id' => $this->request->getPost('patient_id'),
                'appointment_id' => $this->request->getPost('appointment_id'),
                'diagnosis' => $this->request->getPost('diagnosis'),
                'treatment' => $this->request->getPost('treatment'),
                'notes' => $this->request->getPost('notes'),
                'xray_image_url' => $this->request->getPost('xray_image_url'),
                'next_appointment_date' => $nextAppointmentDatetime,
                'dentist_id' => $this->request->getPost('dentist_id')
            ];

            // Start transaction
            $db = \Config\Database::connect();
            $db->transStart();

            try {
                $recordId = $dentalRecordModel->createRecord($recordData);
                
                if ($recordId) {
                    // Check if we should create the next appointment automatically
                    $createAppointment = $this->request->getPost('create_appointment');
                    
                    if ($createAppointment && $nextAppointmentDatetime) {
                        // Get the current appointment to get branch info
                        $currentAppointment = $appointmentModel->where('id', $recordData['appointment_id'])->first();
                        
                        if ($currentAppointment) {
                            $appointmentData = [
                                'branch_id' => $currentAppointment['branch_id'],
                                'user_id' => $recordData['user_id'],
                                'dentist_id' => $recordData['dentist_id'],
                                'appointment_date' => date('Y-m-d', strtotime($nextAppointmentDatetime)),
                                'appointment_time' => date('H:i:s', strtotime($nextAppointmentDatetime)),
                                'appointment_datetime' => $nextAppointmentDatetime,
                                'appointment_type' => 'scheduled',
                                'status' => 'scheduled',
                                'approval_status' => 'approved', // Auto-approve follow-up appointments
                                'remarks' => 'Follow-up appointment from dental record #' . $recordId
                            ];
                            
                            $appointmentResult = $this->appointmentService->createAppointment($appointmentData);
                            
                            if (!$appointmentResult['success']) {
                                // If appointment creation fails, log it but don't fail the record creation
                                log_message('error', 'Failed to create follow-up appointment: ' . $appointmentResult['message']);
                            }
                        }
                    }
                    
                    $db->transComplete();
                    
                    if ($db->transStatus()) {
                        $successMessage = 'Dental record created successfully';
                        if ($createAppointment && $nextAppointmentDatetime) {
                            $successMessage .= ' and follow-up appointment scheduled';
                        }
                        session()->setFlashdata('success', $successMessage);
                        return redirect()->to('/admin/dental-records/' . $recordId);
                    } else {
                        session()->setFlashdata('error', 'Failed to create dental record');
                    }
                } else {
                    $db->transRollback();
                    session()->setFlashdata('error', 'Failed to create dental record');
                }
            } catch (\Exception $e) {
                $db->transRollback();
                session()->setFlashdata('error', 'Failed to create dental record: ' . $e->getMessage());
            }
        }

        return redirect()->back();
    }

    /**
     * Show dental charting form for a specific appointment (Admin)
     */
    public function createDentalChart($appointmentId)
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $appointmentModel = new \App\Models\AppointmentModel();
        $serviceModel = new \App\Models\ServiceModel();
        
        // Get appointment details
        $appointment = $appointmentModel->select('appointments.*, patient.name as patient_name, dentist.name as dentist_name, branches.name as branch_name')
                                      ->join('user as patient', 'patient.id = appointments.user_id')
                                      ->join('user as dentist', 'dentist.id = appointments.dentist_id')
                                      ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                      ->find($appointmentId);

        if (!$appointment) {
            session()->setFlashdata('error', 'Appointment not found');
            return redirect()->to('/admin/dental-charts');
        }

        // Get available services
        $services = $serviceModel->findAll();
        
        // Get tooth layout and conditions
        $toothLayout = \App\Models\DentalChartModel::getToothLayout();
        $toothConditions = \App\Models\DentalChartModel::getToothConditions();

        return view('admin/dental/create_chart', [
            'user' => $user,
            'appointment' => $appointment,
            'services' => $services,
            'toothLayout' => $toothLayout,
            'toothConditions' => $toothConditions
        ]);
    }

    /**
     * Create new dental record with chart after checkup (Admin)
     */
    public function storeDentalRecord()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        if ($this->request->getMethod() === 'POST') {
            $dentalRecordModel = new \App\Models\DentalRecordModel();
            $dentalChartModel = new \App\Models\DentalChartModel();
            $appointmentModel = new \App\Models\AppointmentModel();
            
            // Get the datetime field (either next_appointment_datetime or next_appointment_date for backward compatibility)
            $nextAppointmentDatetime = $this->request->getPost('next_appointment_datetime') ?: $this->request->getPost('next_appointment_date');
            
            $recordData = [
                'user_id' => $this->request->getPost('patient_id'),
                'appointment_id' => $this->request->getPost('appointment_id'),
                'diagnosis' => $this->request->getPost('diagnosis'),
                'treatment' => $this->request->getPost('treatment'),
                'notes' => $this->request->getPost('notes'),
                'xray_image_url' => $this->request->getPost('xray_image_url'),
                'next_appointment_date' => $nextAppointmentDatetime,
                'dentist_id' => $this->request->getPost('dentist_id') // Admin selects which dentist
            ];

            // Start transaction
            $db = \Config\Database::connect();
            $db->transStart();

            try {
                // Create dental record
                $recordId = $dentalRecordModel->createRecord($recordData);
                
                if ($recordId) {
                    // Process dental chart data
                    $chartData = [];
                    $toothData = $this->request->getPost('tooth');
                    
                    if ($toothData) {
                        foreach ($toothData as $toothNumber => $data) {
                            if (!empty($data['condition']) || !empty($data['notes']) || $data['status'] !== 'healthy') {
                                $chartData[] = [
                                    'tooth_number' => $toothNumber,
                                    'tooth_type' => $data['tooth_type'] ?? 'permanent',
                                    'condition' => $data['condition'] ?? '',
                                    'status' => $data['status'] ?? 'healthy',
                                    'notes' => $data['notes'] ?? '',
                                    'recommended_service_id' => !empty($data['recommended_service_id']) ? $data['recommended_service_id'] : null,
                                    'priority' => $data['priority'] ?? 'medium',
                                    'estimated_cost' => !empty($data['estimated_cost']) ? $data['estimated_cost'] : null
                                ];
                            }
                        }
                    }
                    
                    // Save dental chart
                    if (!empty($chartData)) {
                        $dentalChartModel->saveChart($recordId, $chartData);
                    }
                    
                    // Check if we should create the next appointment automatically
                    $createAppointment = $this->request->getPost('create_appointment');
                    
                    if ($createAppointment && $nextAppointmentDatetime) {
                        // Get the current appointment to get branch info
                        $currentAppointment = $appointmentModel->where('id', $recordData['appointment_id'])->first();
                        
                        if ($currentAppointment) {
                            $appointmentData = [
                                'branch_id' => $currentAppointment['branch_id'],
                                'user_id' => $recordData['user_id'],
                                'dentist_id' => $recordData['dentist_id'],
                                'appointment_date' => date('Y-m-d', strtotime($nextAppointmentDatetime)),
                                'appointment_time' => date('H:i:s', strtotime($nextAppointmentDatetime)),
                                'appointment_datetime' => $nextAppointmentDatetime,
                                'appointment_type' => 'scheduled',
                                'status' => 'scheduled',
                                'approval_status' => 'approved', // Auto-approve follow-up appointments
                                'remarks' => 'Follow-up appointment from dental record #' . $recordId . ' with chart'
                            ];
                            
                            $appointmentResult = $this->appointmentService->createAppointment($appointmentData);
                            
                            if (!$appointmentResult['success']) {
                                // If appointment creation fails, log it but don't fail the record creation
                                log_message('error', 'Failed to create follow-up appointment: ' . $appointmentResult['message']);
                            }
                        }
                    }
                }

                $db->transComplete();

                if ($db->transStatus()) {
                    $successMessage = 'Dental record and chart created successfully';
                    if ($createAppointment && $nextAppointmentDatetime) {
                        $successMessage .= ' and follow-up appointment scheduled';
                    }
                    session()->setFlashdata('success', $successMessage);
                    return redirect()->to('/admin/dental-charts/' . $recordData['appointment_id']);
                } else {
                    session()->setFlashdata('error', 'Failed to create dental record');
                }
            } catch (\Exception $e) {
                $db->transRollback();
                session()->setFlashdata('error', 'Failed to create dental record: ' . $e->getMessage());
            }
        }

        return redirect()->back();
    }

    /**
     * Edit existing dental chart (Admin)
     */
    public function editDentalChart($appointmentId)
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $appointmentModel = new \App\Models\AppointmentModel();
        $dentalChartModel = new \App\Models\DentalChartModel();
        $dentalRecordModel = new \App\Models\DentalRecordModel();
        $serviceModel = new \App\Models\ServiceModel();
        
        // Get appointment details
        $appointment = $appointmentModel->select('appointments.*, patient.name as patient_name, dentist.name as dentist_name, branches.name as branch_name')
                                      ->join('user as patient', 'patient.id = appointments.user_id')
                                      ->join('user as dentist', 'dentist.id = appointments.dentist_id')
                                      ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                      ->find($appointmentId);

        if (!$appointment) {
            session()->setFlashdata('error', 'Appointment not found');
            return redirect()->to('/admin/dental-charts');
        }

        // Get existing dental record and chart
        $dentalRecord = $dentalRecordModel->where('appointment_id', $appointmentId)->first();
        $dentalChart = $dentalChartModel->getAppointmentChart($appointmentId);
        
        // Get available services
        $services = $serviceModel->findAll();
        
        // Get tooth layout and conditions
        $toothLayout = \App\Models\DentalChartModel::getToothLayout();
        $toothConditions = \App\Models\DentalChartModel::getToothConditions();

        return view('admin/dental/edit_chart', [
            'user' => $user,
            'appointment' => $appointment,
            'dentalRecord' => $dentalRecord,
            'dentalChart' => $dentalChart,
            'services' => $services,
            'toothLayout' => $toothLayout,
            'toothConditions' => $toothConditions
        ]);
    }

    /**
     * Update existing dental record and chart (Admin)
     */
    public function updateDentalRecord($recordId)
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        if ($this->request->getMethod() === 'POST') {
            $dentalRecordModel = new \App\Models\DentalRecordModel();
            $dentalChartModel = new \App\Models\DentalChartModel();
            
            // Get the datetime field (either next_appointment_datetime or next_appointment_date for backward compatibility)
            $nextAppointmentDatetime = $this->request->getPost('next_appointment_datetime') ?: $this->request->getPost('next_appointment_date');
            
            $recordData = [
                'diagnosis' => $this->request->getPost('diagnosis'),
                'treatment' => $this->request->getPost('treatment'),
                'notes' => $this->request->getPost('notes'),
                'xray_image_url' => $this->request->getPost('xray_image_url'),
                'next_appointment_date' => $nextAppointmentDatetime,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Start transaction
            $db = \Config\Database::connect();
            $db->transStart();

            try {
                // Update dental record
                $dentalRecordModel->update($recordId, $recordData);
                
                // Delete existing chart data for this record
                $dentalChartModel->where('dental_record_id', $recordId)->delete();
                
                // Process new dental chart data
                $chartData = [];
                $toothData = $this->request->getPost('tooth');
                
                if ($toothData) {
                    foreach ($toothData as $toothNumber => $data) {
                        if (!empty($data['condition']) || !empty($data['notes']) || $data['status'] !== 'healthy') {
                            $chartData[] = [
                                'tooth_number' => $toothNumber,
                                'tooth_type' => $data['tooth_type'] ?? 'permanent',
                                'condition' => $data['condition'] ?? '',
                                'status' => $data['status'] ?? 'healthy',
                                'notes' => $data['notes'] ?? '',
                                'recommended_service_id' => !empty($data['recommended_service_id']) ? $data['recommended_service_id'] : null,
                                'priority' => $data['priority'] ?? 'medium',
                                'estimated_cost' => !empty($data['estimated_cost']) ? $data['estimated_cost'] : null
                            ];
                        }
                    }
                }
                
                // Save updated dental chart
                if (!empty($chartData)) {
                    $dentalChartModel->saveChart($recordId, $chartData);
                }

                $db->transComplete();

                if ($db->transStatus()) {
                    session()->setFlashdata('success', 'Dental record and chart updated successfully');
                    $appointmentId = $this->request->getPost('appointment_id');
                    return redirect()->to('/admin/dental-charts/' . $appointmentId);
                } else {
                    session()->setFlashdata('error', 'Failed to update dental record');
                }
            } catch (\Exception $e) {
                $db->transRollback();
                session()->setFlashdata('error', 'Failed to update dental record: ' . $e->getMessage());
            }
        }

        return redirect()->back();
    }

    public function invoice()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        return view('admin/billing/invoice', ['user' => $user]);
    }

    public function rolePermission()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        return view('admin/management/roles', ['user' => $user]);
    }

    public function branches()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        return view('admin/management/branches', ['user' => $user]);
    }

    public function settings()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }
        return view('admin/management/settings', ['user' => $user]);
    }
} 