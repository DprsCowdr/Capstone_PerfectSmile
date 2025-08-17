<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'appointment_id',
        'patient_id',
        'payment_status',
        'payment_method',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'payment_date',
        'payment_received_by',
        'payment_notes',
        'invoice_number',
        'receipt_number',
        'transaction_reference',
        'discount_amount',
        'discount_reason'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'appointment_id' => 'required|integer',
        'patient_id' => 'required|integer',
        'payment_status' => 'in_list[pending,paid,partial,waived,refunded]',
        'payment_method' => 'in_list[cash,card,bank_transfer,gcash,paymaya,insurance]',
        'total_amount' => 'required|decimal',
        'paid_amount' => 'decimal',
        'balance_amount' => 'decimal'
    ];

    protected $validationMessages = [
        'appointment_id' => [
            'required' => 'Appointment ID is required',
            'integer' => 'Appointment ID must be a valid number'
        ],
        'patient_id' => [
            'required' => 'Patient ID is required',
            'integer' => 'Patient ID must be a valid number'
        ],
        'total_amount' => [
            'required' => 'Total amount is required',
            'decimal' => 'Total amount must be a valid decimal number'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['generateInvoiceNumber', 'calculateBalance'];
    protected $afterInsert = [];
    protected $beforeUpdate = ['calculateBalance'];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Generate invoice number
     */
    protected function generateInvoiceNumber(array $data)
    {
        if (empty($data['data']['invoice_number'])) {
            $year = date('Y');
            $count = $this->where('YEAR(created_at)', $year)->countAllResults() + 1;
            $data['data']['invoice_number'] = 'INV-' . $year . '-' . str_pad($count, 6, '0', STR_PAD_LEFT);
        }
        
        return $data;
    }

    /**
     * Calculate balance amount
     */
    protected function calculateBalance(array $data)
    {
        $total = $data['data']['total_amount'] ?? 0;
        $paid = $data['data']['paid_amount'] ?? 0;
        $discount = $data['data']['discount_amount'] ?? 0;
        
        $data['data']['balance_amount'] = max(0, $total - $paid - $discount);
        
        // Update payment status based on balance
        if ($data['data']['balance_amount'] == 0 && $paid > 0) {
            $data['data']['payment_status'] = 'paid';
        } elseif ($paid > 0 && $data['data']['balance_amount'] > 0) {
            $data['data']['payment_status'] = 'partial';
        }
        
        return $data;
    }

    /**
     * Get payment for a specific appointment
     */
    public function getByAppointmentId($appointmentId)
    {
        return $this->where('appointment_id', $appointmentId)->first();
    }

    /**
     * Get payments with patient and staff details
     */
    public function getWithDetails($appointmentId = null)
    {
        $builder = $this->db->table($this->table . ' p')
            ->select('p.*, pt.name as patient_name, s.name as received_by_name, 
                     a.appointment_datetime')
            ->join('user pt', 'pt.id = p.patient_id', 'left')
            ->join('user s', 's.id = p.payment_received_by', 'left')
            ->join('appointments a', 'a.id = p.appointment_id', 'left');
            
        if ($appointmentId) {
            $builder->where('p.appointment_id', $appointmentId);
        }
        
        return $builder->get()->getResultArray();
    }

    /**
     * Create payment record for appointment
     */
    public function createPaymentRecord($appointmentId, $patientId, $totalAmount, $paymentMethod = null)
    {
        $data = [
            'appointment_id' => $appointmentId,
            'patient_id' => $patientId,
            'total_amount' => $totalAmount,
            'paid_amount' => 0.00,
            'payment_status' => 'pending',
            'payment_method' => $paymentMethod
        ];

        return $this->insert($data);
    }

    /**
     * Process payment
     */
    public function processPayment($paymentId, $amount, $method, $receivedBy, $notes = null, $transactionRef = null)
    {
        $payment = $this->find($paymentId);
        if (!$payment) {
            return false;
        }

        $newPaidAmount = $payment['paid_amount'] + $amount;
        
        $data = [
            'paid_amount' => $newPaidAmount,
            'payment_method' => $method,
            'payment_date' => date('Y-m-d H:i:s'),
            'payment_received_by' => $receivedBy,
            'payment_notes' => $notes,
            'transaction_reference' => $transactionRef,
            'receipt_number' => $this->generateReceiptNumber()
        ];

        return $this->update($paymentId, $data);
    }

    /**
     * Apply discount
     */
    public function applyDiscount($paymentId, $discountAmount, $reason)
    {
        $data = [
            'discount_amount' => $discountAmount,
            'discount_reason' => $reason
        ];

        return $this->update($paymentId, $data);
    }

    /**
     * Get pending payments
     */
    public function getPendingPayments()
    {
        return $this->whereIn('payment_status', ['pending', 'partial'])
                   ->orderBy('created_at', 'DESC')
                   ->findAll();
    }

    /**
     * Get payments by patient
     */
    public function getByPatient($patientId)
    {
        return $this->where('patient_id', $patientId)
                   ->orderBy('created_at', 'DESC')
                   ->findAll();
    }

    /**
     * Get today's payments
     */
    public function getTodayPayments()
    {
        return $this->where('DATE(payment_date)', date('Y-m-d'))
                   ->where('payment_status !=', 'pending')
                   ->orderBy('payment_date', 'DESC')
                   ->findAll();
    }

    /**
     * Get payment statistics
     */
    public function getPaymentStats($startDate = null, $endDate = null)
    {
        $builder = $this->db->table($this->table);
        
        if ($startDate && $endDate) {
            $builder->where('DATE(payment_date) >=', $startDate)
                   ->where('DATE(payment_date) <=', $endDate);
        } elseif ($startDate) {
            $builder->where('DATE(payment_date) >=', $startDate);
        } else {
            $builder->where('DATE(payment_date)', date('Y-m-d'));
        }

        return [
            'total_revenue' => $builder->selectSum('paid_amount')->get()->getRow()->paid_amount ?? 0,
            'total_pending' => $this->where('payment_status', 'pending')->selectSum('total_amount')->get()->getRow()->total_amount ?? 0,
            'total_discounts' => $builder->selectSum('discount_amount')->get()->getRow()->discount_amount ?? 0,
            'payment_count' => $builder->where('payment_status', 'paid')->countAllResults()
        ];
    }

    /**
     * Generate receipt number
     */
    private function generateReceiptNumber()
    {
        $year = date('Y');
        $count = $this->where('YEAR(payment_date)', $year)
                     ->where('receipt_number IS NOT NULL')
                     ->countAllResults() + 1;
        
        return 'REC-' . $year . '-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }
}
