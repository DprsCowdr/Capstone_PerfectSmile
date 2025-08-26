<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table = 'invoices';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'invoice_number',
        'patient_id',
        'procedure_id',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'discount',
        'payment_status',
        'status',
        'created_at',
        'updated_at'
    ];

    // Generate unique invoice number
    public function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $date = date('Ymd');

        $builder = $this->db->table($this->table)
            ->select('invoice_number')
            ->like('invoice_number', $prefix . $date, 'after')
            ->orderBy('id', 'DESC')
            ->limit(1);

        $result = $builder->get()->getRow();

        if ($result) {
            $last_number = intval(substr($result->invoice_number, -4));
            $new_number = str_pad($last_number + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $new_number = '0001';
        }

        return $prefix . $date . $new_number;
    }

    // Get all invoices (with optional search + pagination)
    public function getAllInvoicesWithDetails($page = 1, $limit = 10, $search = null, $status = null)
    {
        $builder = $this->db->table($this->table . ' i')
            ->select('i.id, i.invoice_number, i.procedure_id, i.total_amount, i.discount, i.payment_status, i.created_at, i.updated_at, i.paid_amount, i.balance_amount, u.name as patient_name')
            ->join('user u', 'i.patient_id = u.id', 'left');

        if ($search) {
            $builder->groupStart()
                ->like('i.invoice_number', $search)
                ->groupEnd();
        }

        if ($status) {
            $builder->where('i.payment_status', $status);
        }

        $builder->orderBy('i.created_at', 'DESC');
        $offset = ($page - 1) * $limit;
        $query = $builder->get($limit, $offset);

        return $query->getResultArray();
    }

    // Get single invoice
    public function getInvoiceWithDetails($id)
    {
        return $this->db->table($this->table . ' i')
            ->select('i.id, i.invoice_number, i.procedure_id, i.total_amount, i.discount, i.payment_status, i.created_at, i.updated_at')
            ->where('i.id', $id)
            ->get()->getRowArray();
    }

    // Get invoice statistics
    public function getInvoiceStats()
    {
        $total_invoices = $this->countAll();
        $paid_invoices = $this->where('payment_status', 'paid')->countAllResults();
        $pending_invoices = $this->where('payment_status', 'pending')->countAllResults();

        $total_amount = $this->selectSum('total_amount')->get()->getRow()->total_amount;

        return [
            'total_invoices'   => $total_invoices,
            'paid_invoices'    => $paid_invoices,
            'pending_invoices' => $pending_invoices,
            'total_amount'     => $total_amount ?: 0
        ];
    }

    // Instead of recordPayment here, use PaymentsModel to insert payments
    // Then update payment_status in invoices if fully paid
    public function updatePaymentStatus($invoiceId, $status)
    {
        return $this->update($invoiceId, [
            'payment_status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
