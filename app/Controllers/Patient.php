<?php

namespace App\Controllers;

use App\Controllers\Auth;
use App\Models\PatientModel;
use App\Models\PatientMedicalHistoryModel;

class Patient extends BaseController
{
    public function dashboard()
    {
        // Check if user is logged in and is patient
        if (!Auth::isAuthenticated()) {
            return redirect()->to('/login');
        }

        $user = Auth::getCurrentUser();
        
        // Check if user is patient
        if ($user['user_type'] !== 'patient') {
            return redirect()->to('/dashboard');
        }
        
        // Get patient's appointments
        $appointmentModel = new \App\Models\AppointmentModel();
        $myAppointments = $appointmentModel->select('appointments.*, branches.name as branch_name')
                                          ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                          ->where('appointments.user_id', $user['id'])
                                          ->orderBy('appointments.appointment_datetime', 'DESC')
                                          ->limit(5)
                                          ->findAll();
        
        // Get upcoming appointments
        $upcomingAppointments = $appointmentModel->select('appointments.*, branches.name as branch_name')
                                                ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                                ->where('appointments.user_id', $user['id'])
                                                ->where('appointments.appointment_datetime >=', date('Y-m-d H:i:s'))
                                                ->whereIn('appointments.status', ['confirmed', 'scheduled'])
                                                ->orderBy('appointments.appointment_datetime', 'ASC')
                                                ->limit(3)
                                                ->findAll();
        
        // Get total appointment count
        $totalAppointments = $appointmentModel->where('user_id', $user['id'])->countAllResults();
        
        // Get completed treatments count
        $completedTreatments = $appointmentModel->where('user_id', $user['id'])
                                               ->where('status', 'completed')
                                               ->countAllResults();
        
        // Get pending appointments count
        $pendingAppointments = $appointmentModel->where('user_id', $user['id'])
                                               ->whereIn('status', ['pending', 'scheduled'])
                                               ->countAllResults();
        
        return view('patient/dashboard', [
            'user' => $user,
            'myAppointments' => $myAppointments,
            'upcomingAppointments' => $upcomingAppointments,
            'totalAppointments' => $totalAppointments,
            'completedTreatments' => $completedTreatments,
            'pendingAppointments' => $pendingAppointments
        ]);
    }

    /**
     * Save patient medical history via AJAX
     */
    public function saveMedicalHistory()
    {
        // Check if user is logged in and has appropriate permissions
        $user = Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['admin', 'staff', 'doctor'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access'
            ])->setStatusCode(403);
        }

        // Get patient ID from request
        $patientId = $this->request->getPost('patient_id');
        if (!$patientId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Patient ID is required'
            ])->setStatusCode(400);
        }

        try {
            $patientModel = new PatientModel();
            
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

            // Update patient medical history
            $result = $patientModel->updateMedicalHistory($patientId, $medicalHistoryData);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Medical history saved successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to save medical history'
                ])->setStatusCode(500);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error saving medical history: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while saving medical history'
            ])->setStatusCode(500);
        }
    }

    /**
     * Get patient medical history via AJAX
     */
    public function getMedicalHistory($patientId)
    {
        // Check if user is logged in and has appropriate permissions
        $user = Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['admin', 'staff', 'doctor'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access'
            ])->setStatusCode(403);
        }

        try {
            $patientModel = new PatientModel();
            
            // Get patient with medical history
            $patient = $patientModel->getPatientWithMedicalHistory($patientId);
            
            if (!$patient) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Patient not found'
                ])->setStatusCode(404);
            }

            // Extract medical history data
            $medicalHistory = [];
            $medicalHistoryFields = [
                'previous_dentist', 'last_dental_visit',
                'physician_name', 'physician_specialty', 'physician_phone', 'physician_address',
                'good_health', 'under_treatment', 'treatment_condition', 'serious_illness',
                'illness_details', 'hospitalized', 'hospitalization_where', 'hospitalization_when', 'hospitalization_why',
                'tobacco_use', 'blood_pressure', 'allergies',
                'pregnant', 'nursing', 'birth_control',
                'medical_conditions', 'other_conditions'
            ];

            foreach ($medicalHistoryFields as $field) {
                if (isset($patient[$field])) {
                    $medicalHistory[$field] = $patient[$field];
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'medical_history' => $medicalHistory
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error getting medical history: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while getting medical history'
            ])->setStatusCode(500);
        }
    }

    /**
     * Test database connection and table structure
     */
    public function testDatabase()
    {
        try {
            $db = \Config\Database::connect();
            
            // Test if we can connect to the database
            $result = $db->query("SELECT COUNT(*) as count FROM dental_record")->getRow();
            
            // Test if we can get records for patient ID 3
            $result2 = $db->query("SELECT COUNT(*) as count FROM dental_record WHERE user_id = 3")->getRow();
            
            // Get a sample record
            $result3 = $db->query("SELECT * FROM dental_record WHERE user_id = 3 LIMIT 1")->getRow();
            
            return $this->response->setJSON([
                'success' => true,
                'total_records' => $result->count,
                'patient_3_records' => $result2->count,
                'sample_record' => $result3
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Test method to check if dental records endpoint is working
     */
    public function testTreatmentsEndpoint()
    {
        try {
            $dentalRecordModel = new \App\Models\DentalRecordModel();
            
            // Test with patient ID 3 (which we know has records)
            $patientId = 3;
            $treatments = $dentalRecordModel->where('user_id', $patientId)
                                          ->orderBy('record_date', 'DESC')
                                          ->findAll();
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Endpoint is working',
                'patient_id' => $patientId,
                'count' => count($treatments),
                'treatments' => $treatments
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Get patient treatments (dental records) via AJAX
     */
    public function getPatientTreatments($patientId)
    {
        // Check if user is logged in and has appropriate permissions
        $user = Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['admin', 'staff', 'doctor'])) {
            log_message('error', 'Unauthorized access attempt to getPatientTreatments');
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access'
            ])->setStatusCode(403);
        }

        try {
            $dentalRecordModel = new \App\Models\DentalRecordModel();
            
            // Ensure patient ID is an integer
            $patientId = (int) $patientId;
            
            // Debug: Log the patient ID being searched
            log_message('info', "Searching for dental records with patient ID: {$patientId}");
            
            // Get dental records for the patient
            $treatments = $dentalRecordModel->where('user_id', $patientId)
                                          ->orderBy('record_date', 'DESC')
                                          ->findAll();

            // Debug: Log the results
            log_message('info', "Found " . count($treatments) . " dental records for patient ID: {$patientId}");
            if (count($treatments) > 0) {
                log_message('info', "First treatment: " . json_encode($treatments[0]));
            } else {
                log_message('info', "No treatments found for patient ID: {$patientId}");
            }

            // Debug: Log the response being sent
            $response = [
                'success' => true,
                'treatments' => $treatments
            ];
            log_message('info', "Sending response: " . json_encode($response));

            return $this->response->setJSON($response);

        } catch (\Exception $e) {
            log_message('error', 'Error getting patient treatments: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while getting patient treatments'
            ])->setStatusCode(500);
        }
    }

    /**
     * Get patient appointments via AJAX
     */
    public function getPatientAppointments($patientId)
    {
        // Check if user is logged in and has appropriate permissions
        $user = Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['admin', 'staff', 'doctor'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access'
            ])->setStatusCode(403);
        }

        try {
            $appointmentModel = new \App\Models\AppointmentModel();
            
            // Get appointments for the patient
            $appointments = $appointmentModel->where('user_id', $patientId)
                                           ->orderBy('appointment_datetime', 'DESC')
                                           ->findAll();

            return $this->response->setJSON([
                'success' => true,
                'appointments' => $appointments
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error getting patient appointments: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while getting patient appointments'
            ])->setStatusCode(500);
        }
    }

    /**
     * Get patient bills via AJAX
     */
    public function getPatientBills($patientId)
    {
        // Check if user is logged in and has appropriate permissions
        $user = Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['admin', 'staff', 'doctor'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access'
            ])->setStatusCode(403);
        }

        try {
            $paymentModel = new \App\Models\PaymentModel();
            
            // Get payments for the patient
            $bills = $paymentModel->where('patient_id', $patientId)
                                 ->orderBy('created_at', 'DESC')
                                 ->findAll();

            return $this->response->setJSON([
                'success' => true,
                'bills' => $bills
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error getting patient bills: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while getting patient bills'
            ])->setStatusCode(500);
        }
    }
} 