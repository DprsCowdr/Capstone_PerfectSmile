<?php

namespace App\Services;

use App\Models\UserModel;
use App\Services\EmailService;

class PatientRegistrationService
{
    protected $userModel;
    protected $emailService;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->emailService = new EmailService();
    }

    /**
     * Get existing patient or create new one from appointment data
     * This is the "on-the-fly" patient creation
     */
    public function getOrCreatePatient($appointmentData, $source = 'appointment')
    {
        $email = $appointmentData['patient_email'] ?? null;
        $name = $appointmentData['patient_name'] ?? null;
        $phone = $appointmentData['patient_phone'] ?? null;

        // If user_id provided, get existing patient
        if (!empty($appointmentData['user_id'])) {
            $patient = $this->userModel->find($appointmentData['user_id']);
            if ($patient && $patient['user_type'] === 'patient') {
                return ['success' => true, 'patient' => $patient, 'created' => false];
            }
        }

        // If email provided, check if patient exists
        if (!empty($email)) {
            $existingPatient = $this->userModel->where('email', $email)
                                               ->where('user_type', 'patient')
                                               ->first();
            if ($existingPatient) {
                return ['success' => true, 'patient' => $existingPatient, 'created' => false];
            }
        }

        // Create new patient if we have minimum required info
        if (!empty($email) && !empty($name)) {
            return $this->createNewPatient($name, $email, $phone, $source);
        }

        return ['success' => false, 'message' => 'Insufficient patient information'];
    }

    /**
     * Create a new patient record
     */
    private function createNewPatient($name, $email, $phone = null, $source = 'appointment')
    {
        try {
            // Generate temporary password for new patients
            $tempPassword = $this->generateTempPassword();
            
            $patientData = [
                'name' => trim($name),
                'email' => trim(strtolower($email)),
                'phone' => $phone ? trim($phone) : null,
                'user_type' => 'patient',
                'status' => 'active',
                'password' => password_hash($tempPassword, PASSWORD_DEFAULT),
                'registration_source' => $source, // Track how they were added
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $patientId = $this->userModel->insert($patientData);
            
            if ($patientId) {
                $patient = $this->userModel->find($patientId);
                // Ensure we have array format
                if (is_object($patient)) {
                    $patient = (array) $patient;
                }
                
                // Send welcome email with temporary password (async, don't block if fails)
                $this->sendWelcomeEmail($patient, $tempPassword, $source);
                
                log_message('info', "New patient created via {$source}: {$email} (ID: {$patientId})");
                
                return [
                    'success' => true, 
                    'patient' => $patient, 
                    'created' => true,
                    'temp_password' => $tempPassword,
                    'message' => 'New patient registered successfully'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to create patient record'];
            
        } catch (\Exception $e) {
            log_message('error', "Failed to create patient: " . $e->getMessage());
            return ['success' => false, 'message' => 'Patient registration failed: ' . $e->getMessage()];
        }
    }

    /**
     * Generate temporary password for new patients
     */
    private function generateTempPassword($length = 8)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $password;
    }

    /**
     * Send welcome email with account details (non-blocking)
     */
    private function sendWelcomeEmail($patient, $tempPassword, $source)
    {
        try {
            $subject = $source === 'walkin' ? 
                'Welcome to Perfect Smile - Your Account Details' : 
                'Perfect Smile Account Created - Appointment Confirmation';
                
            $this->emailService->sendWelcomeEmail(
                $patient['email'],
                $patient['name'],
                $tempPassword,
                $subject,
                $source
            );
        } catch (\Exception $e) {
            // Don't block appointment creation if email fails
            log_message('warning', "Welcome email failed for {$patient['email']}: " . $e->getMessage());
        }
    }

    /**
     * Update patient information from appointment data
     */
    public function updatePatientInfo($patientId, $appointmentData)
    {
        $updateData = [];
        
        if (!empty($appointmentData['patient_phone'])) {
            $updateData['phone'] = trim($appointmentData['patient_phone']);
        }
        
        if (!empty($updateData)) {
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            return $this->userModel->update($patientId, $updateData);
        }
        
        return true;
    }

    /**
     * Validate patient data for appointment creation
     */
    public function validatePatientData($data, $isWalkIn = false)
    {
        $errors = [];

        // For walk-ins, we need at least name
        if ($isWalkIn) {
            if (empty($data['patient_name'])) {
                $errors['patient_name'] = 'Patient name is required for walk-ins';
            }
            // Email optional for walk-ins, but recommended
            if (empty($data['patient_email'])) {
                $errors['patient_email'] = 'Email recommended for patient records';
            }
        } else {
            // For scheduled appointments, email is required
            if (empty($data['patient_email'])) {
                $errors['patient_email'] = 'Email is required for appointment booking';
            }
            if (empty($data['patient_name'])) {
                $errors['patient_name'] = 'Patient name is required';
            }
        }

        if (!empty($data['patient_email']) && !filter_var($data['patient_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['patient_email'] = 'Invalid email format';
        }

        return empty($errors) ? ['valid' => true] : ['valid' => false, 'errors' => $errors];
    }
}
