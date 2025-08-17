<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MigrateAppointmentDataToNewTables extends Migration
{
    public function up()
    {
        // Get all appointments with the data we need to migrate
        $appointments = $this->db->query("
            SELECT id, checked_in_at, checked_in_by, self_checkin, started_at, called_by, 
                   treatment_status, treatment_notes, payment_status, payment_method, 
                   payment_amount, payment_date, payment_received_by, payment_notes,
                   user_id
            FROM appointments 
            WHERE checked_in_at IS NOT NULL 
               OR started_at IS NOT NULL 
               OR payment_status != 'pending'
        ")->getResultArray();

        foreach ($appointments as $appointment) {
            $appointmentId = $appointment['id'];
            
            // 1. Migrate check-in data
            if (!empty($appointment['checked_in_at'])) {
                $checkinData = [
                    'appointment_id' => $appointmentId,
                    'checked_in_at' => $appointment['checked_in_at'],
                    'checked_in_by' => $appointment['checked_in_by'],
                    'self_checkin' => $appointment['self_checkin'] ?? 0,
                    'checkin_method' => $appointment['self_checkin'] ? 'self' : 'staff',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                
                $this->db->table('patient_checkins')->insert($checkinData);
            }
            
            // 2. Migrate treatment session data
            if (!empty($appointment['started_at'])) {
                $treatmentData = [
                    'appointment_id' => $appointmentId,
                    'started_at' => $appointment['started_at'],
                    'called_by' => $appointment['called_by'],
                    'treatment_status' => $this->mapTreatmentStatus($appointment['treatment_status']),
                    'treatment_notes' => $appointment['treatment_notes'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                
                $this->db->table('treatment_sessions')->insert($treatmentData);
            }
            
            // 3. Migrate payment data (create payment record for all appointments)
            $paymentData = [
                'appointment_id' => $appointmentId,
                'patient_id' => $appointment['user_id'],
                'payment_status' => $appointment['payment_status'] ?? 'pending',
                'payment_method' => $this->mapPaymentMethod($appointment['payment_method']),
                'total_amount' => $appointment['payment_amount'] ?? 0.00,
                'paid_amount' => ($appointment['payment_status'] === 'paid') ? ($appointment['payment_amount'] ?? 0.00) : 0.00,
                'balance_amount' => ($appointment['payment_status'] === 'paid') ? 0.00 : ($appointment['payment_amount'] ?? 0.00),
                'payment_date' => $appointment['payment_date'],
                'payment_received_by' => $appointment['payment_received_by'],
                'payment_notes' => $appointment['payment_notes'],
                'invoice_number' => 'INV-' . date('Y') . '-' . str_pad($appointmentId, 6, '0', STR_PAD_LEFT),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            
            $this->db->table('payments')->insert($paymentData);
        }
    }
    
    public function down()
    {
        // Clear the new tables
        $this->db->table('patient_checkins')->truncate();
        $this->db->table('treatment_sessions')->truncate();
        $this->db->table('payments')->truncate();
    }
    
    private function mapTreatmentStatus($status)
    {
        switch ($status) {
            case 'ongoing':
                return 'in_progress';
            case 'completed':
                return 'completed';
            case 'cancelled':
                return 'cancelled';
            default:
                return 'in_progress';
        }
    }
    
    private function mapPaymentMethod($method)
    {
        if (empty($method)) {
            return null;
        }
        
        $method = strtolower($method);
        $validMethods = ['cash', 'card', 'bank_transfer', 'gcash', 'paymaya', 'insurance'];
        
        if (in_array($method, $validMethods)) {
            return $method;
        }
        
        // Map common variations
        switch ($method) {
            case 'credit_card':
            case 'debit_card':
                return 'card';
            case 'bank':
            case 'transfer':
                return 'bank_transfer';
            default:
                return 'cash'; // Default fallback
        }
    }
}
