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
        if (!$user || !in_array($user['user_type'], ['doctor', 'admin'])) {
            return redirect()->to('/login');
        }

        // Auto-update appointment statuses
        $this->appointmentModel->autoUpdateStatuses();

        // Get today's appointments
        $dentistId = ($user['user_type'] === 'doctor') ? $user['id'] : null;
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
        if (!$user || !in_array($user['user_type'], ['doctor', 'admin'])) {
            return redirect()->to('/login');
        }

        // Get appointment details
        $appointment = $this->appointmentModel->getAppointmentForCheckup($appointmentId);
        if (!$appointment) {
            return redirect()->to('/checkup')->with('error', 'Appointment not found.');
        }

        // Check if appointment is confirmed and for today
        if ($appointment['status'] !== 'confirmed' || date('Y-m-d') !== $appointment['appointment_date']) {
            return redirect()->to('/checkup')->with('error', 'Appointment cannot be started.');
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
        if (!$user || !in_array($user['user_type'], ['doctor', 'admin'])) {
            return redirect()->to('/login');
        }

        // Get appointment details
        $appointment = $this->appointmentModel->getAppointmentForCheckup($appointmentId);
        if (!$appointment) {
            return redirect()->to('/checkup')->with('error', 'Appointment not found.');
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

        // Try to get the dental record for the current appointment
        $currentRecord = $this->dentalRecordModel->where('appointment_id', $appointmentId)->first();
        $previousChart = [];
        if ($currentRecord) {
            log_message('info', "Found current record ID: {$currentRecord['id']} for appointment {$appointmentId}");
            $chart = $this->dentalChartModel->getRecordChart($currentRecord['id']);
            if (!empty($chart)) {
                $previousChart = $chart;
                log_message('info', "Loaded " . count($chart) . " chart entries from current record");
            } elseif (!empty($previousRecords)) {
                // Fallback: use the latest previous record with a non-empty chart
                foreach ($previousRecords as $rec) {
                    // Skip the current record if it's in the list
                    if ($currentRecord['id'] == $rec['id']) continue;
                    $chart = $this->dentalChartModel->getRecordChart($rec['id']);
                    if (!empty($chart)) {
                        $previousChart = $chart;
                        log_message('info', "Loaded " . count($chart) . " chart entries from previous record ID: {$rec['id']}");
                        break;
                    }
                }
            }
        } elseif (!empty($previousRecords)) {
            // No current record, fallback to latest previous record with a non-empty chart
            log_message('info', "No current record, checking previous records");
            foreach ($previousRecords as $rec) {
                $chart = $this->dentalChartModel->getRecordChart($rec['id']);
                if (!empty($chart)) {
                    $previousChart = $chart;
                    break;
                }
            }
        }
        // Debug: log previous records and chart
        log_message('debug', 'Previous records: ' . json_encode($previousRecords));
        log_message('debug', 'Previous chart: ' . json_encode($previousChart));

        // Get tooth conditions and treatment options
        $toothConditions = $this->dentalChartModel->getToothConditions();
        $treatmentOptions = $this->dentalChartModel->getTreatmentOptions();

        return view('checkup/patient_checkup', [
            'user' => $user,
            'appointment' => $appointment,
            'patient' => $patientWithHistory,
            'previousRecords' => $previousRecords,
            'previousChart' => $previousChart,
            'toothConditions' => $toothConditions,
            'treatmentOptions' => $treatmentOptions
        ]);
    }

    /**
     * Save checkup results
     */
    public function saveCheckup($appointmentId)
    {
        $user = \App\Controllers\Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['doctor', 'admin'])) {
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
            'diagnosis' => 'required|min_length[10]',
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
            $db->transStart();

            // Get or create patient record (user record with medical history)
            $patient = $this->patientModel->getPatientWithMedicalHistory($appointment['user_id']);
            $patientId = $appointment['user_id']; // Use existing user_id as patient_id

            // Collect medical history data from the form
            $medicalHistoryData = [
                'previous_dentist' => $this->request->getPost('previous_dentist'),
                'last_dental_visit' => $this->request->getPost('last_dental_visit'),
                'physician_name' => $this->request->getPost('physician_name'),
                'physician_specialty' => $this->request->getPost('physician_specialty'),
                'physician_phone' => $this->request->getPost('physician_phone'),
                'physician_address' => $this->request->getPost('physician_address'),
                'good_health' => $this->request->getPost('good_health'),
                'under_treatment' => $this->request->getPost('under_treatment'),
                'treatment_condition' => $this->request->getPost('treatment_condition'),
                'serious_illness' => $this->request->getPost('serious_illness'),
                'illness_details' => $this->request->getPost('illness_details'),
                'hospitalized' => $this->request->getPost('hospitalized'),
                'hospitalization_where' => $this->request->getPost('hospitalization_where'),
                'hospitalization_when' => $this->request->getPost('hospitalization_when'),
                'hospitalization_why' => $this->request->getPost('hospitalization_why'),
                'tobacco_use' => $this->request->getPost('tobacco_use'),
                'blood_pressure' => $this->request->getPost('blood_pressure'),
                'allergies' => $this->request->getPost('allergies'),
                'pregnant' => $this->request->getPost('pregnant'),
                'nursing' => $this->request->getPost('nursing'),
                'birth_control' => $this->request->getPost('birth_control'),
                'medical_conditions' => $this->request->getPost('medical_conditions') ?: [],
                'other_conditions' => $this->request->getPost('other_conditions'),
            ];

            // Remove empty values to avoid unnecessary database updates
            $medicalHistoryData = array_filter($medicalHistoryData, function($value) {
                return $value !== null && $value !== '' && $value !== [];
            });

            // Update patient medical history if data provided
            if (!empty($medicalHistoryData)) {
                $this->patientModel->updateMedicalHistory($patientId, $medicalHistoryData);
                log_message('info', "Updated medical history for patient ID: {$patientId}");
            }

            // Check if a dental record already exists for this appointment
            $existingRecord = $this->dentalRecordModel->where('appointment_id', $appointmentId)->first();

            $recordData = [
                'user_id' => $appointment['user_id'],
                'dentist_id' => $user['id'],
                'record_date' => date('Y-m-d'),
                'diagnosis' => $this->request->getPost('diagnosis'),
                'treatment' => $this->request->getPost('treatment'),
                'notes' => $this->request->getPost('notes'),
                'next_appointment_date' => $this->request->getPost('next_appointment_date') ?: null,
                'appointment_id' => $appointmentId
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
                    log_message('error', "Failed to save dental chart for record ID: {$recordId}");
                }
            } else {
                log_message('info', "Checkup save - No chart data received or invalid format");
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
                    'remarks' => 'Follow-up appointment from checkup on ' . date('M j, Y') . ' - ' . $this->request->getPost('diagnosis')
                ];
                
                $newAppointmentId = $this->appointmentModel->insert($nextAppointmentData);
                
                if ($newAppointmentId) {
                    // Update the dental record with the new appointment ID
                    $this->dentalRecordModel->update($recordId, [
                        'next_appointment_id' => $newAppointmentId
                    ]);
                    log_message('info', "Created follow-up appointment ID: {$newAppointmentId}");
                }
            }

            // Complete the appointment
            $this->appointmentModel->completeCheckup($appointmentId);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Database transaction failed.');
            }

            $successMessage = 'Checkup completed and all data saved successfully.';
            if ($nextAppointmentDate && $nextAppointmentTime) {
                $successMessage .= ' Follow-up appointment scheduled for ' . date('M j, Y', strtotime($nextAppointmentDate)) . ' at ' . date('g:i A', strtotime($nextAppointmentTime)) . '.';
            }

            log_message('info', "Checkup completed successfully for appointment ID: {$appointmentId}");
            return redirect()->to('/checkup')->with('success', $successMessage);

        } catch (\Exception $e) {
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
        if (!$user || !in_array($user['user_type'], ['doctor', 'admin'])) {
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
        if (!$user || !in_array($user['user_type'], ['doctor', 'admin'])) {
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