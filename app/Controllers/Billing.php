<?php

namespace App\Controllers;

use App\Models\AppointmentModel;
use App\Models\DentalRecordModel;
use App\Models\ServiceModel;
use App\Controllers\Auth;

class Billing extends BaseController
{
    protected $appointmentModel;
    protected $dentalRecordModel;
    protected $serviceModel;

    public function __construct()
    {
        $this->appointmentModel = new AppointmentModel();
        $this->dentalRecordModel = new DentalRecordModel();
        $this->serviceModel = new ServiceModel();
    }

    /**
     * Generate bill for completed appointment
     */
    public function generateBill($appointmentId)
    {
        $user = Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['staff', 'admin', 'doctor'])) {
            return redirect()->to('/login');
        }

        // Get appointment details
        $appointment = $this->appointmentModel
            ->select('appointments.*, patient.name as patient_name, patient.email as patient_email, 
                     patient.phone as patient_phone, dentist.name as dentist_name, branches.name as branch_name')
            ->join('user as patient', 'patient.id = appointments.user_id')
            ->join('user as dentist', 'dentist.id = appointments.dentist_id')
            ->join('branches', 'branches.id = appointments.branch_id', 'left')
            ->find($appointmentId);

        if (!$appointment || $appointment['status'] !== 'completed') {
            session()->setFlashdata('error', 'Appointment not found or not completed');
            return redirect()->back();
        }

        // Get dental record for this appointment
        $dentalRecord = $this->dentalRecordModel->where('appointment_id', $appointmentId)->first();
        
        // Get services used (this would need to be tracked during treatment)
        $services = $this->serviceModel->findAll();
        
        // Calculate bill (basic implementation)
        $billItems = [];
        $subtotal = 0;
        
        // Base consultation fee
        $billItems[] = [
            'service' => 'Dental Consultation',
            'quantity' => 1,
            'unit_price' => 1500, // PHP 1,500
            'total' => 1500
        ];
        $subtotal += 1500;

        // Add other services based on treatment
        if ($dentalRecord && $dentalRecord['treatment']) {
            $treatment = strtolower($dentalRecord['treatment']);
            
            if (strpos($treatment, 'cleaning') !== false) {
                $billItems[] = [
                    'service' => 'Dental Cleaning',
                    'quantity' => 1,
                    'unit_price' => 2000,
                    'total' => 2000
                ];
                $subtotal += 2000;
            }
            
            if (strpos($treatment, 'filling') !== false) {
                $billItems[] = [
                    'service' => 'Dental Filling',
                    'quantity' => 1,
                    'unit_price' => 3000,
                    'total' => 3000
                ];
                $subtotal += 3000;
            }
            
            // Add more service mappings as needed
        }

        $tax = $subtotal * 0.12; // 12% VAT
        $total = $subtotal + $tax;

        $billData = [
            'appointment' => $appointment,
            'dentalRecord' => $dentalRecord,
            'billItems' => $billItems,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'bill_number' => 'BILL-' . date('Y') . '-' . str_pad($appointmentId, 6, '0', STR_PAD_LEFT),
            'bill_date' => date('Y-m-d')
        ];

        return view('billing/bill', array_merge(['user' => $user], $billData));
    }

    /**
     * Process payment
     */
    public function processPayment($appointmentId)
    {
        $user = Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['staff', 'admin'])) {
            return redirect()->to('/login');
        }

        $paymentMethod = $this->request->getPost('payment_method');
        $amount = $this->request->getPost('amount');
        $notes = $this->request->getPost('notes');

        // Update appointment with payment info
        $result = $this->appointmentModel->update($appointmentId, [
            'payment_status' => 'paid',
            'payment_method' => $paymentMethod,
            'payment_amount' => $amount,
            'payment_date' => date('Y-m-d H:i:s'),
            'payment_received_by' => $user['id'],
            'payment_notes' => $notes
        ]);

        if ($result) {
            session()->setFlashdata('success', 'Payment processed successfully');
            return redirect()->to("/billing/receipt/{$appointmentId}");
        } else {
            session()->setFlashdata('error', 'Failed to process payment');
            return redirect()->back();
        }
    }

    /**
     * Generate receipt
     */
    public function receipt($appointmentId)
    {
        $user = Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['staff', 'admin', 'doctor', 'patient'])) {
            return redirect()->to('/login');
        }

        $appointment = $this->appointmentModel
            ->select('appointments.*, patient.name as patient_name, patient.email as patient_email, 
                     dentist.name as dentist_name, branches.name as branch_name')
            ->join('user as patient', 'patient.id = appointments.user_id')
            ->join('user as dentist', 'dentist.id = appointments.dentist_id')
            ->join('branches', 'branches.id = appointments.branch_id', 'left')
            ->find($appointmentId);

        if (!$appointment || $appointment['payment_status'] !== 'paid') {
            session()->setFlashdata('error', 'Receipt not available');
            return redirect()->back();
        }

        return view('billing/receipt', [
            'user' => $user,
            'appointment' => $appointment,
            'receipt_number' => 'REC-' . date('Y') . '-' . str_pad($appointmentId, 6, '0', STR_PAD_LEFT)
        ]);
    }
}
