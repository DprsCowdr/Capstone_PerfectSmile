<?php

namespace App\Controllers;

use App\Controllers\Auth;

class Dentist extends BaseController
{
    public function dashboard()
    {
        // Check if user is logged in and is dentist
        if (!Auth::isAuthenticated()) {
            return redirect()->to('/login');
        }

        $user = Auth::getCurrentUser();
        
        // Check if user is dentist
        if ($user['user_type'] !== 'doctor') {
            return redirect()->to('/dashboard');
        }
        
        // Get pending appointments for this dentist
        $appointmentModel = new \App\Models\AppointmentModel();
        $pendingAppointments = $appointmentModel->getPendingApprovalAppointments($user['id']);
        
        // Get today's appointments
        $todayAppointments = $appointmentModel->select('appointments.*, user.name as patient_name, user.email as patient_email, branches.name as branch_name')
                                             ->join('user', 'user.id = appointments.user_id')
                                             ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                             ->where('appointments.dentist_id', $user['id'])
                                             ->where('DATE(appointments.appointment_datetime)', date('Y-m-d'))
                                             ->whereIn('appointments.status', ['confirmed', 'scheduled'])
                                             ->orderBy('appointments.appointment_datetime', 'ASC')
                                             ->findAll();
        
        // Get upcoming appointments (next 7 days)
        $upcomingAppointments = $appointmentModel->select('appointments.*, user.name as patient_name, user.email as patient_email, branches.name as branch_name')
                                                ->join('user', 'user.id = appointments.user_id')
                                                ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                                ->where('appointments.dentist_id', $user['id'])
                                                ->where('DATE(appointments.appointment_datetime) >=', date('Y-m-d'))
                                                ->where('DATE(appointments.appointment_datetime) <=', date('Y-m-d', strtotime('+7 days')))
                                                ->whereIn('appointments.status', ['confirmed', 'scheduled'])
                                                ->orderBy('appointments.appointment_datetime', 'ASC')
                                                ->findAll();
        
        return view('dentist/dashboard', [
            'user' => $user,
            'pendingAppointments' => $pendingAppointments,
            'todayAppointments' => $todayAppointments,
            'upcomingAppointments' => $upcomingAppointments
        ]);
    }
    
    public function appointments()
    {
        // Check if user is logged in and is dentist
        if (!Auth::isAuthenticated()) {
            return redirect()->to('/login');
        }

        $user = Auth::getCurrentUser();
        
        // Check if user is dentist
        if ($user['user_type'] !== 'doctor') {
            return redirect()->to('/dashboard');
        }
        
        // Get all appointments for this dentist
        $appointmentModel = new \App\Models\AppointmentModel();
        $appointments = $appointmentModel->select('appointments.*, user.name as patient_name, user.email as patient_email, branches.name as branch_name')
                                        ->join('user', 'user.id = appointments.user_id')
                                        ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                        ->where('appointments.dentist_id', $user['id'])
                                        ->orderBy('appointments.appointment_datetime', 'DESC')
                                        ->findAll();
        
        return view('dentist/appointments', [
            'user' => $user,
            'appointments' => $appointments
        ]);
    }
    
    public function setAvailability()
    {
        // Check if user is logged in and is dentist
        if (!Auth::isAuthenticated()) {
            return redirect()->to('/login');
        }

        $user = Auth::getCurrentUser();
        
        // Check if user is dentist
        if ($user['user_type'] !== 'doctor') {
            return redirect()->to('/dashboard');
        }
        
        try {
            $data = [
                'doctor_id' => $user['id'],
                'availability_date' => $this->request->getPost('date'),
                'status' => $this->request->getPost('status'),
                'start_time' => $this->request->getPost('start_time'),
                'notes' => $this->request->getPost('notes')
            ];
            
            // Validate required fields
            if (empty($data['availability_date']) || empty($data['status'])) {
                session()->setFlashdata('error', 'Date and status are required');
                return redirect()->back();
            }
            
            // Here you would typically save to a doctor_availability table
            // For now, we'll just show a success message
            session()->setFlashdata('success', 'Availability set successfully');
            return redirect()->back();
            
        } catch (\Exception $e) {
            session()->setFlashdata('error', 'Failed to set availability: ' . $e->getMessage());
            return redirect()->back();
        }
    }
    
    public function approveAppointment($id)
    {
        // Check if user is logged in and is dentist
        if (!Auth::isAuthenticated()) {
            return redirect()->to('/login');
        }

        $user = Auth::getCurrentUser();
        
        // Check if user is dentist
        if ($user['user_type'] !== 'doctor') {
            return redirect()->to('/dashboard');
        }
        
        $appointmentModel = new \App\Models\AppointmentModel();
        
        // Check if this appointment is assigned to this dentist
        $appointment = $appointmentModel->where('id', $id)
                                       ->where('dentist_id', $user['id'])
                                       ->first();
        
        if (!$appointment) {
            session()->setFlashdata('error', 'Appointment not found or not assigned to you');
            return redirect()->back();
        }
        
        try {
            if ($appointmentModel->approveAppointment($id, $user['id'])) {
                session()->setFlashdata('success', 'Appointment approved successfully');
                
                // TODO: Send notification to patient via email/SMS
                $this->sendAppointmentNotification($appointment, 'approved');
            } else {
                session()->setFlashdata('error', 'Failed to approve appointment');
            }
        } catch (\Exception $e) {
            session()->setFlashdata('error', 'Failed to approve appointment: ' . $e->getMessage());
        }
        
        return redirect()->back();
    }
    
    public function declineAppointment($id)
    {
        // Check if user is logged in and is dentist
        if (!Auth::isAuthenticated()) {
            return redirect()->to('/login');
        }

        $user = Auth::getCurrentUser();
        
        // Check if user is dentist
        if ($user['user_type'] !== 'doctor') {
            return redirect()->to('/dashboard');
        }
        
        $appointmentModel = new \App\Models\AppointmentModel();
        $reason = $this->request->getPost('reason');
        
        if (empty($reason)) {
            session()->setFlashdata('error', 'Decline reason is required');
            return redirect()->back();
        }
        
        // Check if this appointment is assigned to this dentist
        $appointment = $appointmentModel->where('id', $id)
                                       ->where('dentist_id', $user['id'])
                                       ->first();
        
        if (!$appointment) {
            session()->setFlashdata('error', 'Appointment not found or not assigned to you');
            return redirect()->back();
        }
        
        try {
            if ($appointmentModel->declineAppointment($id, $reason)) {
                session()->setFlashdata('success', 'Appointment declined successfully');
                
                // TODO: Send notification to patient via email/SMS
                $this->sendAppointmentNotification($appointment, 'declined', $reason);
            } else {
                session()->setFlashdata('error', 'Failed to decline appointment');
            }
        } catch (\Exception $e) {
            session()->setFlashdata('error', 'Failed to decline appointment: ' . $e->getMessage());
        }
        
        return redirect()->back();
    }
    
    private function sendAppointmentNotification($appointment, $action)
    {
        // TODO: Implement email/SMS notification
        // For now, we'll just log the notification
        $datetime = $appointment['appointment_datetime'] ?? 'Unknown';
        $date = isset($appointment['appointment_date']) ? $appointment['appointment_date'] : (isset($appointment['appointment_datetime']) ? substr($appointment['appointment_datetime'], 0, 10) : 'Unknown');
        $time = isset($appointment['appointment_time']) ? $appointment['appointment_time'] : (isset($appointment['appointment_datetime']) ? substr($appointment['appointment_datetime'], 11, 5) : 'Unknown');
        
        log_message('info', "Dentist {$action} appointment: Patient ID {$appointment['user_id']}, DateTime: {$datetime}, Date: {$date}, Time: {$time}");
    }

    // ============== DENTAL RECORDS (STEP 3: CHECKUP/CONSULTATION) ==============
    
    /**
     * View patient's dental records with charts
     */
    public function patientRecords($patientId)
    {
        if (!Auth::isAuthenticated() || Auth::getCurrentUser()['user_type'] !== 'doctor') {
            return redirect()->to('/login');
        }

        $dentalRecordModel = new \App\Models\DentalRecordModel();
        $dentalChartModel = new \App\Models\DentalChartModel();
        $userModel = new \App\Models\UserModel();
        
        $patient = $userModel->find($patientId);
        if (!$patient || $patient['user_type'] !== 'patient') {
            session()->setFlashdata('error', 'Patient not found');
            return redirect()->back();
        }

        $records = $dentalRecordModel->getPatientRecords($patientId);
        $dentalHistory = $dentalChartModel->getPatientDentalHistory($patientId);
        $teethNeedingTreatment = $dentalChartModel->getTeethNeedingTreatment($patientId);
        
        return view('dentist/patient_records', [
            'patient' => $patient,
            'records' => $records,
            'dentalHistory' => $dentalHistory,
            'teethNeedingTreatment' => $teethNeedingTreatment,
            'toothLayout' => \App\Models\DentalChartModel::getToothLayout(),
            'toothConditions' => \App\Models\DentalChartModel::getToothConditions(),
            'user' => Auth::getCurrentUser()
        ]);
    }

    /**
     * Show dental charting form for a specific appointment
     */
    public function dentalChart($appointmentId)
    {
        if (!Auth::isAuthenticated() || Auth::getCurrentUser()['user_type'] !== 'doctor') {
            return redirect()->to('/login');
        }

        $appointmentModel = new \App\Models\AppointmentModel();
        $serviceModel = new \App\Models\ServiceModel();
        $userModel = new \App\Models\UserModel();
        
        $appointment = $appointmentModel->select('appointments.*, user.name as patient_name, user.id as patient_id')
                                       ->join('user', 'user.id = appointments.user_id')
                                       ->find($appointmentId);
                                       
        if (!$appointment) {
            session()->setFlashdata('error', 'Appointment not found');
            return redirect()->to('/dentist/dashboard');
        }

        $services = $serviceModel->findAll();
        $toothLayout = \App\Models\DentalChartModel::getToothLayout();
        $toothConditions = \App\Models\DentalChartModel::getToothConditions();
        
        return view('dentist/dental_chart', [
            'appointment' => $appointment,
            'services' => $services,
            'toothLayout' => $toothLayout,
            'toothConditions' => $toothConditions,
            'user' => Auth::getCurrentUser()
        ]);
    }

    /**
     * Create new dental record with chart after checkup
     */
    public function createRecord()
    {
        if (!Auth::isAuthenticated() || Auth::getCurrentUser()['user_type'] !== 'doctor') {
            return redirect()->to('/login');
        }

        if ($this->request->getMethod() === 'POST') {
            $dentalRecordModel = new \App\Models\DentalRecordModel();
            $dentalChartModel = new \App\Models\DentalChartModel();
            $currentUser = Auth::getCurrentUser();
            
            $recordData = [
                'user_id' => $this->request->getPost('patient_id'),
                'appointment_id' => $this->request->getPost('appointment_id'),
                'diagnosis' => $this->request->getPost('diagnosis'),
                'treatment' => $this->request->getPost('treatment'),
                'notes' => $this->request->getPost('notes'),
                'xray_image_url' => $this->request->getPost('xray_image_url'),
                'next_appointment_date' => $this->request->getPost('next_appointment_date'),
                'dentist_id' => $currentUser['id']
            ];

            // Start transaction
            $db = \Config\Database::connect();
            $db->transStart();

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
            }

            $db->transComplete();

            if ($db->transStatus()) {
                session()->setFlashdata('success', 'Dental record and chart created successfully');
                return redirect()->to('/dentist/patient-records/' . $recordData['user_id']);
            } else {
                session()->setFlashdata('error', 'Failed to create dental record');
            }
        }

        return redirect()->back();
    }

    // ============== PROCEDURES (STEP 5 & 6: PROCEDURE SCHEDULING & EXECUTION) ==============
    
    /**
     * Schedule a procedure for a patient
     */
    public function scheduleProcedure()
    {
        if (!Auth::isAuthenticated() || Auth::getCurrentUser()['user_type'] !== 'doctor') {
            return redirect()->to('/login');
        }

        if ($this->request->getMethod() === 'POST') {
            $procedureModel = new \App\Models\ProcedureModel();
            
            $data = [
                'user_id' => $this->request->getPost('patient_id'),
                'procedure_name' => $this->request->getPost('procedure_name'),
                'description' => $this->request->getPost('description'),
                'procedure_date' => $this->request->getPost('procedure_date')
            ];

            $serviceIds = $this->request->getPost('service_ids') ?? [];
            
            if ($procedureId = $procedureModel->scheduleProcedure($data, $serviceIds)) {
                session()->setFlashdata('success', 'Procedure scheduled successfully');
                return redirect()->to('/dentist/procedures');
            } else {
                session()->setFlashdata('error', 'Failed to schedule procedure');
            }
        }

        return redirect()->back();
    }

    /**
     * View all procedures
     */
    public function procedures()
    {
        if (!Auth::isAuthenticated() || Auth::getCurrentUser()['user_type'] !== 'doctor') {
            return redirect()->to('/login');
        }

        $procedureModel = new \App\Models\ProcedureModel();
        $serviceModel = new \App\Models\ServiceModel();
        $userModel = new \App\Models\UserModel();
        
        // Get all procedures with patient info
        $procedures = $procedureModel->select('procedures.*, user.name as patient_name')
                                   ->join('user', 'user.id = procedures.user_id')
                                   ->orderBy('procedure_date', 'DESC')
                                   ->findAll();
        
        $services = $serviceModel->findAll();
        $patients = $userModel->where('user_type', 'patient')->findAll();
        
        return view('dentist/procedures', [
            'procedures' => $procedures,
            'services' => $services,
            'patients' => $patients,
            'user' => Auth::getCurrentUser()
        ]);
    }

    /**
     * View specific procedure details
     */
    public function procedureDetails($procedureId)
    {
        if (!Auth::isAuthenticated() || Auth::getCurrentUser()['user_type'] !== 'doctor') {
            return redirect()->to('/login');
        }

        $procedureModel = new \App\Models\ProcedureModel();
        
        $procedure = $procedureModel->find($procedureId);
        if (!$procedure) {
            session()->setFlashdata('error', 'Procedure not found');
            return redirect()->to('/dentist/procedures');
        }

        $procedureWithServices = $procedureModel->getProcedureWithServices($procedureId);
        
        return view('dentist/procedure_details', [
            'procedure' => $procedure,
            'procedureWithServices' => $procedureWithServices,
            'user' => Auth::getCurrentUser()
        ]);
    }

    // ============== PATIENTS MODULE ==============
    
    /**
     * View all patients for dentist
     */
    public function patients()
    {
        if (!Auth::isAuthenticated() || Auth::getCurrentUser()['user_type'] !== 'doctor') {
            return redirect()->to('/login');
        }

        $userModel = new \App\Models\UserModel();
        $dentalRecordModel = new \App\Models\DentalRecordModel();
        $appointmentModel = new \App\Models\AppointmentModel();
        
        // Get all patients
        $patients = $userModel->where('user_type', 'patient')
                             ->where('status', 'active')
                             ->orderBy('name', 'ASC')
                             ->findAll();
        
        // Add additional info for each patient
        foreach ($patients as &$patient) {
            // Get total appointments for this patient
            $patient['total_appointments'] = $appointmentModel->where('user_id', $patient['id'])->countAllResults();
            
            // Get last appointment date
            $lastAppointment = $appointmentModel->where('user_id', $patient['id'])
                                               ->orderBy('appointment_datetime', 'DESC')
                                               ->first();
            $patient['last_appointment'] = $lastAppointment ? $lastAppointment['appointment_datetime'] : null;
            $patient['last_appointment_id'] = $lastAppointment ? $lastAppointment['id'] : null;
            
            // Get total dental records
            $patient['total_records'] = $dentalRecordModel->where('user_id', $patient['id'])->countAllResults();
            
            // Get last dental record
            $lastRecord = $dentalRecordModel->where('user_id', $patient['id'])
                                          ->orderBy('record_date', 'DESC')
                                          ->first();
            $patient['last_record_date'] = $lastRecord ? $lastRecord['record_date'] : null;
        }
        
        return view('dentist/patients', [
            'patients' => $patients,
            'user' => Auth::getCurrentUser()
        ]);
    }

    /**
     * View specific patient details
     */
    public function patientDetails($patientId)
    {
        if (!Auth::isAuthenticated() || Auth::getCurrentUser()['user_type'] !== 'doctor') {
            return redirect()->to('/login');
        }

        $userModel = new \App\Models\UserModel();
        $dentalRecordModel = new \App\Models\DentalRecordModel();
        $appointmentModel = new \App\Models\AppointmentModel();
        $dentalChartModel = new \App\Models\DentalChartModel();
        
        $patient = $userModel->find($patientId);
        if (!$patient || $patient['user_type'] !== 'patient') {
            session()->setFlashdata('error', 'Patient not found');
            return redirect()->to('/dentist/patients');
        }

        // Get patient's appointments
        $appointments = $appointmentModel->select('appointments.*, branches.name as branch_name, dentist.name as dentist_name')
                                       ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                       ->join('user as dentist', 'dentist.id = appointments.dentist_id', 'left')
                                       ->where('appointments.user_id', $patientId)
                                       ->orderBy('appointment_datetime', 'DESC')
                                       ->findAll();

        // Get patient's dental records
        $records = $dentalRecordModel->getPatientRecords($patientId);
        
        // Get teeth needing treatment
        $teethNeedingTreatment = $dentalChartModel->getTeethNeedingTreatment($patientId);
        
        // Get complete dental history
        $dentalHistory = $dentalChartModel->getPatientDentalHistory($patientId);

        return view('dentist/patient_details', [
            'patient' => $patient,
            'appointments' => $appointments,
            'records' => $records,
            'teethNeedingTreatment' => $teethNeedingTreatment,
            'dentalHistory' => $dentalHistory,
            'user' => Auth::getCurrentUser()
        ]);
    }

    /**
     * Search patients
     */
    public function searchPatients()
    {
        if (!Auth::isAuthenticated() || Auth::getCurrentUser()['user_type'] !== 'doctor') {
            return redirect()->to('/login');
        }

        $searchTerm = $this->request->getGet('search');
        $userModel = new \App\Models\UserModel();
        $dentalRecordModel = new \App\Models\DentalRecordModel();
        $appointmentModel = new \App\Models\AppointmentModel();
        
        if (empty($searchTerm)) {
            return redirect()->to('/dentist/patients');
        }

        $patients = $userModel->like('name', $searchTerm)
                             ->orLike('email', $searchTerm)
                             ->orLike('phone', $searchTerm)
                             ->where('user_type', 'patient')
                             ->where('status', 'active')
                             ->orderBy('name', 'ASC')
                             ->findAll();

        // Add additional info for each patient (same as in patients() method)
        foreach ($patients as &$patient) {
            // Get total appointments for this patient
            $patient['total_appointments'] = $appointmentModel->where('user_id', $patient['id'])->countAllResults();
            
            // Get last appointment date
            $lastAppointment = $appointmentModel->where('user_id', $patient['id'])
                                               ->orderBy('appointment_datetime', 'DESC')
                                               ->first();
            $patient['last_appointment'] = $lastAppointment ? $lastAppointment['appointment_datetime'] : null;
            $patient['last_appointment_id'] = $lastAppointment ? $lastAppointment['id'] : null;
            
            // Get total dental records
            $patient['total_records'] = $dentalRecordModel->where('user_id', $patient['id'])->countAllResults();
            
            // Get last dental record
            $lastRecord = $dentalRecordModel->where('user_id', $patient['id'])
                                          ->orderBy('record_date', 'DESC')
                                          ->first();
            $patient['last_record_date'] = $lastRecord ? $lastRecord['record_date'] : null;
        }

        return view('dentist/patients', [
            'patients' => $patients,
            'searchTerm' => $searchTerm,
            'user' => Auth::getCurrentUser()
        ]);
    }
} 