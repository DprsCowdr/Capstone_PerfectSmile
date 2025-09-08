<?php

namespace App\Controllers;

use App\Models\AppointmentModel;
use App\Models\DentalRecordModel;
use App\Models\DentalChartModel;
use App\Models\PatientModel;
use App\Models\UserModel;
use App\Controllers\Auth;

class Checkup extends BaseController
{
    protected $appointmentModel;
    protected $dentalRecordModel;
    protected $dentalChartModel;
    protected $patientModel;
    protected $userModel;

    public function __construct()
    {
        $this->appointmentModel = new AppointmentModel();
        $this->dentalRecordModel = new DentalRecordModel();
        $this->dentalChartModel = new DentalChartModel();
        $this->patientModel = new PatientModel();
        $this->userModel = new UserModel();
    }

    /**
     * Checkup dashboard - show today's appointments
     */
    public function index()
    {
    $user = \App\Controllers\Auth::getCurrentUser();
    // allow dentists (previously named 'doctor') and admins to access checkup
    if (!$user || !in_array($user['user_type'], ['dentist', 'admin'])) {
            return redirect()->to('/login');
        }

        // Auto-update appointment statuses
        $this->appointmentModel->autoUpdateStatuses();

        // Get today's appointments
    $dentistId = ($user['user_type'] === 'dentist') ? $user['id'] : null;
        $todayAppointments = $this->appointmentModel->getTodayAppointments($dentistId);

        // Get ongoing checkup for today for this doctor
        $ongoingCheckup = $this->appointmentModel
            ->where('DATE(appointment_datetime)', date('Y-m-d'))
            ->where('status', 'ongoing')
            ->where('dentist_id', $dentistId)
            ->first();

        return view('checkup/dashboard', [
            'user' => $user,
            'appointments' => $todayAppointments,
            'ongoingCheckup' => $ongoingCheckup
        ]);
    }

    /**
     * Start checkup for a specific appointment
     */
    public function startCheckup($appointmentId)
    {
        $user = \App\Controllers\Auth::getCurrentUser();
    if (!$user || !in_array($user['user_type'], ['dentist', 'admin'])) {
            return redirect()->to('/login');
        }

        // Get appointment details
        $appointment = $this->appointmentModel->getAppointmentForCheckup($appointmentId);
        if (!$appointment) {
            return redirect()->to('/checkup')->with('error', 'Appointment not found.');
        }
        
        // Debug: Log appointment data structure
        log_message('debug', 'Appointment data keys: ' . implode(', ', array_keys($appointment)));
        log_message('debug', 'Patient name field exists: ' . (isset($appointment['patient_name']) ? 'YES' : 'NO'));
        if (isset($appointment['patient_name'])) {
            log_message('debug', 'Patient name value: ' . $appointment['patient_name']);
        }

        // Check if appointment is valid for checkup and for today
        if (!in_array($appointment['status'], ['confirmed', 'checked_in']) || date('Y-m-d') !== $appointment['appointment_date']) {
            return redirect()->to('/checkup')->with('error', 'Appointment cannot be started. Status: ' . $appointment['status']);
        }

        // Start checkup
        $this->appointmentModel->startCheckup($appointmentId, $user['id']);

        return redirect()->to("/checkup/patient/{$appointmentId}")->with('success', 'Checkup started successfully.');
    }

    /**
     * Patient checkup form
     */
    public function patientCheckup($appointmentId)
    {
        $user = \App\Controllers\Auth::getCurrentUser();
    if (!$user || !in_array($user['user_type'], ['dentist', 'admin'])) {
            return redirect()->to('/login');
        }

        // Get appointment details
        $appointment = $this->appointmentModel->getAppointmentForCheckup($appointmentId);
        if (!$appointment) {
            return redirect()->to('/checkup')->with('error', 'Appointment not found.');
        }
        
        // Debug: Log appointment data structure for patient checkup
        log_message('debug', 'Patient checkup - Appointment data keys: ' . implode(', ', array_keys($appointment)));
        log_message('debug', 'Patient checkup - Patient name field exists: ' . (isset($appointment['patient_name']) ? 'YES' : 'NO'));
        if (isset($appointment['patient_name'])) {
            log_message('debug', 'Patient checkup - Patient name value: ' . $appointment['patient_name']);
        } else {
            log_message('debug', 'Patient checkup - Full appointment data: ' . json_encode($appointment));
        }

        // Check if appointment is ongoing
        if ($appointment['status'] !== 'ongoing') {
            return redirect()->to('/checkup')->with('error', 'Appointment is not in progress.');
        }

        // Get or create patient record (user record with medical history)
        $patient = $this->patientModel->getPatientWithMedicalHistory($appointment['user_id']);
        
        // Get patient's medical history
        $patientWithHistory = $this->patientModel->getPatientWithMedicalHistory($patient['id']);

        // Get patient's previous dental records
        $previousRecords = $this->dentalRecordModel->getPatientRecords($appointment['user_id']);
        log_message('info', "Patient {$appointment['user_id']} has " . count($previousRecords) . " previous records");

        // Build a merged chart map using the newest data per tooth across
        // the current record (if any) and previous records for this user.
        $currentRecord = $this->dentalRecordModel->where('appointment_id', $appointmentId)->first();
        $previousChart = [];
        $mergedByTooth = [];

        // Helper to merge a chart list into the map preserving the first-seen (newest) value per tooth
        $mergeChart = static function(array $chartList) use (&$mergedByTooth) {
            foreach ($chartList as $toothRow) {
                $toothNum = (int) ($toothRow['tooth_number'] ?? 0);
                if ($toothNum <= 0) {
                    continue;
                }
                if (!array_key_exists($toothNum, $mergedByTooth)) {
                    $mergedByTooth[$toothNum] = $toothRow;
                }
            }
        };

        // 1) Merge current record first (newest for this appointment)
        if ($currentRecord) {
            log_message('info', "Found current record ID: {$currentRecord['id']} for appointment {$appointmentId}");
            $chart = $this->dentalChartModel->getRecordChart($currentRecord['id']);
            if (!empty($chart)) {
                $mergeChart($chart);
                log_message('info', "Merged " . count($chart) . " chart entries from current record");
            }
        }

        // 2) Merge previous records in descending date order, skipping current
        if (!empty($previousRecords)) {
            foreach ($previousRecords as $rec) {
                if ($currentRecord && $currentRecord['id'] == $rec['id']) {
                    continue;
                }
                $chart = $this->dentalChartModel->getRecordChart($rec['id']);
                if (!empty($chart)) {
                    $mergeChart($chart);
                }
            }
        }

        // Convert merged map back to a flat list for the view if needed
        if (!empty($mergedByTooth)) {
            // Maintain ascending tooth order for predictability
            ksort($mergedByTooth);
            $previousChart = array_values($mergedByTooth);
            log_message('info', 'Built merged previousChart with ' . count($previousChart) . ' unique teeth');
        }
        // Debug: log previous records and chart
        log_message('debug', 'Previous records: ' . json_encode($previousRecords));
        log_message('debug', 'Previous chart: ' . json_encode($previousChart));

        // Get tooth conditions and treatment options
        $toothConditions = $this->dentalChartModel->getToothConditions();
        $treatmentOptions = $this->dentalChartModel->getTreatmentOptions();

        // Get existing visual chart data
        $existingVisualChartData = '';
        if ($currentRecord && !empty($currentRecord['visual_chart_data'])) {
            $existingVisualChartData = $currentRecord['visual_chart_data'];
            log_message('info', "Loaded visual chart data for current record ID: {$currentRecord['id']}");
        } elseif (!empty($previousRecords)) {
            // For return patients, load visual chart data from most recent previous record
            foreach ($previousRecords as $rec) {
                if (!empty($rec['visual_chart_data'])) {
                    $existingVisualChartData = $rec['visual_chart_data'];
                    log_message('info', "Loaded visual chart data from previous record ID: {$rec['id']}");
                    break;
                }
            }
        }

        return view('checkup/patient_checkup', [
            'user' => $user,
            'appointment' => $appointment,
            'patient' => $patientWithHistory,
            'previousRecords' => $previousRecords,
            'previousChart' => $previousChart,
            'toothConditions' => $toothConditions,
            'treatmentOptions' => $treatmentOptions,
            'existingVisualChartData' => $existingVisualChartData
        ]);
    }

    /**
     * Save checkup results
     */
    public function saveCheckup($appointmentId)
    {
        $user = \App\Controllers\Auth::getCurrentUser();
    if (!$user || !in_array($user['user_type'], ['dentist', 'admin'])) {
            return redirect()->to('/login');
        }

        // Get appointment details
        $appointment = $this->appointmentModel->getAppointmentForCheckup($appointmentId);
        if (!$appointment) {
            return redirect()->to('/checkup')->with('error', 'Appointment not found.');
        }

        // Validate form data
        $validation = \Config\Services::validation();
        $validation->setRules([
            'treatment' => 'required|min_length[10]',
            'notes' => 'permit_empty|max_length[1000]',
            'next_appointment_date' => 'permit_empty|valid_date',
            'next_appointment_time' => 'permit_empty|regex_match[/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/]'
        ]);

        if (!$validation->run($this->request->getPost())) {
            return redirect()->back()->withInput()->with('error', $validation->getErrors());
        }

        try {
            $db = \Config\Database::connect();
            
            // Use manual transaction for better error handling
            $db->query('START TRANSACTION');
            log_message('info', 'Transaction started manually');

            // Get patient record (medical history is now handled separately)
            $patientId = $appointment['user_id']; // Use existing user_id as patient_id

            // Check if a dental record already exists for this appointment
            $existingRecord = $this->dentalRecordModel->where('appointment_id', $appointmentId)->first();

            $recordData = [
                'user_id' => $appointment['user_id'],
                'dentist_id' => $user['id'],
                'record_date' => date('Y-m-d'),
                'treatment' => $this->request->getPost('treatment'),
                'notes' => $this->request->getPost('notes'),
                'next_appointment_date' => $this->request->getPost('next_appointment_date') ?: null,
                'appointment_id' => $appointmentId,
                'visual_chart_data' => $this->request->getPost('visual_chart_data')
            ];

            if ($existingRecord) {
                $recordId = $existingRecord['id'];
                $this->dentalRecordModel->update($recordId, $recordData);
                log_message('info', "Updated existing dental record ID: {$recordId}");
            } else {
                $recordId = $this->dentalRecordModel->insert($recordData);
                log_message('info', "Created new dental record ID: {$recordId}");
            }

            if (!$recordId) {
                throw new \Exception('Failed to save dental record.');
            }

            // Save dental chart data
            $chartData = $this->request->getPost('dental_chart');
            if ($chartData && is_array($chartData)) {
                log_message('info', "Checkup save - Chart data received: " . json_encode($chartData));
                $chartSaveResult = $this->dentalChartModel->saveChart($recordId, $chartData);
                log_message('info', "Checkup save - Chart save result: " . ($chartSaveResult ? 'success' : 'failed'));
                
                if (!$chartSaveResult) {
                    $dbError = $db->error();
                    log_message('error', "Failed to save dental chart for record ID: {$recordId}");
                    log_message('error', "DB Error (chart insertBatch): " . json_encode($dbError));
                }
            } else {
                log_message('info', "Checkup save - No chart data received or invalid format");
            }

            // Log visual chart data save
            $visualChartData = $this->request->getPost('visual_chart_data');
            if ($visualChartData && !empty($visualChartData)) {
                log_message('info', "Checkup save - Visual chart data received (length: " . strlen($visualChartData) . " characters)");
                log_message('info', "Checkup save - Visual chart data saved with record ID: {$recordId}");
            } else {
                log_message('info', "Checkup save - No visual chart data received");
            }

            // Create next appointment if date and time are provided
            $nextAppointmentDate = $this->request->getPost('next_appointment_date');
            $nextAppointmentTime = $this->request->getPost('next_appointment_time');
            
            if ($nextAppointmentDate && $nextAppointmentTime) {
                $nextAppointmentData = [
                    'user_id' => $appointment['user_id'],
                    'branch_id' => $appointment['branch_id'],
                    'dentist_id' => $user['id'],
                    'appointment_datetime' => $nextAppointmentDate . ' ' . $nextAppointmentTime . ':00',
                    'status' => 'pending',
                    'appointment_type' => 'scheduled',
                    'approval_status' => 'pending',
                    'remarks' => 'Follow-up appointment from checkup on ' . date('M j, Y') . ' - ' . $this->request->getPost('treatment')
                ];
                
                $newAppointmentId = $this->appointmentModel->insert($nextAppointmentData);
                
                if ($newAppointmentId) {
                    // Update the dental record with the new appointment ID
                    log_message('info', "Attempting to update record {$recordId} with next_appointment_id: {$newAppointmentId}");
                    
                    $updateResult = $this->dentalRecordModel->update($recordId, [
                        'next_appointment_id' => $newAppointmentId
                    ]);
                    
                    if ($updateResult === false) {
                        $dbError = $db->error();
                        log_message('error', "Failed to update dental_record with next_appointment_id for record ID: {$recordId}");
                        log_message('error', "DB Error (update dental_record): " . json_encode($dbError));
                        log_message('error', "Last executed query: " . $db->getLastQuery());
                    } else {
                        log_message('info', "Successfully updated record {$recordId} with next_appointment_id: {$newAppointmentId}");
                    }
                    log_message('info', "Created follow-up appointment ID: {$newAppointmentId}");
                }
            }

            // Complete the appointment
            log_message('info', "Attempting to complete checkup for appointment ID: {$appointmentId}");
            $completeResult = $this->appointmentModel->completeCheckup($appointmentId);
            if ($completeResult === false) {
                $dbError = $db->error();
                log_message('error', "Failed to complete checkup for appointment ID: {$appointmentId}");
                log_message('error', "DB Error (completeCheckup): " . json_encode($dbError));
            } else {
                log_message('info', "Successfully completed checkup for appointment ID: {$appointmentId}");
            }

            // Commit transaction manually
            $db->query('COMMIT');
            log_message('info', 'Transaction committed successfully');

            $successMessage = 'Checkup completed and all data saved successfully.';
            if ($nextAppointmentDate && $nextAppointmentTime) {
                $successMessage .= ' Follow-up appointment scheduled for ' . date('M j, Y', strtotime($nextAppointmentDate)) . ' at ' . date('g:i A', strtotime($nextAppointmentTime)) . '.';
            }

            log_message('info', "Checkup completed successfully for appointment ID: {$appointmentId}");
            // Do not redirect to invoice automatically; return to checkup dashboard
            return redirect()->to('/checkup')->with('success', 'Checkup completed successfully. You can create an invoice later from the Invoices page.');

        } catch (\Exception $e) {
            // Rollback transaction on error
            $db->query('ROLLBACK');
            log_message('error', "Transaction rolled back due to error: " . $e->getMessage());
            log_message('error', "Error saving checkup data: " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to save checkup data: ' . $e->getMessage());
        }
    }

    /**
     * Mark patient as no-show
     */
    public function markNoShow($appointmentId)
    {
        $user = \App\Controllers\Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['doctor', 'admin'])) {
            return redirect()->to('/login');
        }

        $this->appointmentModel->markNoShow($appointmentId);

        return redirect()->to('/checkup')->with('success', 'Patient marked as no-show.');
    }

    /**
     * Cancel appointment
     */
    public function cancelAppointment($appointmentId)
    {
        $user = \App\Controllers\Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['doctor', 'admin'])) {
            return redirect()->to('/login');
        }

        $reason = $this->request->getPost('reason');
        $this->appointmentModel->cancelAppointment($appointmentId, $reason);

        return redirect()->to('/checkup')->with('success', 'Appointment cancelled successfully.');
    }

    /**
     * View dental record
     */
    public function viewRecord($recordId)
    {
        $user = \App\Controllers\Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['dentist', 'admin'])) {
            return redirect()->to('/login');
        }

        $record = $this->dentalRecordModel->getRecordWithChart($recordId);
        if (!$record) {
            return redirect()->to('/checkup')->with('error', 'Dental record not found.');
        }

        $chartSummary = $this->dentalChartModel->getChartSummary($recordId);

        return view('checkup/view_record', [
            'user' => $user,
            'record' => $record,
            'chartSummary' => $chartSummary
        ]);
    }

    /**
     * Get patient history for AJAX
     */
    public function getPatientHistory($patientId)
    {
        $user = \App\Controllers\Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['dentist', 'admin'])) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $records = $this->dentalRecordModel->getPatientRecords($patientId);
        
        return $this->response->setJSON([
            'success' => true,
            'records' => $records
        ]);
    }

    /**
     * Debug method to check appointment and chart data
     */
    public function debug($appointmentId = null)
    {
        $user = \App\Controllers\Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['doctor', 'admin'])) {
            return redirect()->to('/login');
        }

        $debugInfo = [];
        
        if ($appointmentId) {
            // Check specific appointment
            $appointment = $this->appointmentModel->find($appointmentId);
            $debugInfo['appointment'] = $appointment;
            
            if ($appointment) {
                // Check dental records
                $records = $this->dentalRecordModel->getPatientRecords($appointment['user_id']);
                $debugInfo['patient_records'] = $records;
                
                // Check current record
                $currentRecord = $this->dentalRecordModel->where('appointment_id', $appointmentId)->first();
                $debugInfo['current_record'] = $currentRecord;
                
                if ($currentRecord) {
                    $chart = $this->dentalChartModel->getRecordChart($currentRecord['id']);
                    $debugInfo['current_chart'] = $chart;
                }
            }
        } else {
            // Check today's appointments
            $dentistId = ($user['user_type'] === 'doctor') ? $user['id'] : null;
            $todayAppointments = $this->appointmentModel->getTodayAppointments($dentistId);
            $debugInfo['today_appointments'] = $todayAppointments;
            
            // Check all appointments for today (regardless of status)
            $allTodayAppointments = $this->appointmentModel
                ->where('DATE(appointment_datetime)', date('Y-m-d'))
                ->findAll();
            $debugInfo['all_today_appointments'] = $allTodayAppointments;
        }
        
        // Return as JSON for easy reading
        return $this->response->setJSON($debugInfo);
    }
} 