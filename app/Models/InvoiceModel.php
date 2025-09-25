<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
	protected $table = 'invoices';
	protected $primaryKey = 'id';
	protected $useAutoIncrement = true;
	protected $returnType = 'array';
	protected $useSoftDeletes = false;
	protected $protectFields = true;
	protected $allowedFields = [
		'invoice_number',
		'patient_id',
		'user_id',
		'service_id',
		'total_amount',
		'discount',
		'final_amount',
		'created_at',
		'updated_at'
	];

	protected $useTimestamps = true;
	protected $dateFormat = 'datetime';
	protected $createdField = 'created_at';
	protected $updatedField = 'updated_at';

	protected $validationRules = [
		// Server-side controller/service will enforce that a patient exists.
		// Make model tolerant: patient_id may be provided or legacy user_id may be used at DB level.
		'patient_id' => 'permit_empty|integer',
		'user_id' => 'permit_empty|integer',
		'service_id' => 'permit_empty|integer',
		'total_amount' => 'required|decimal'
	];

	protected $skipValidation = false;

	/**
	 * Return basic statistics for invoices.
	 */
	public function getInvoiceStats()
	{
		try {
			// Use a fresh instance to avoid query builder conflicts
			$freshModel = new self();
			$total = $freshModel->countAllResults();
			// Note: status columns removed from database
			return ['total' => $total, 'paid' => 0, 'unpaid' => 0];
		} catch (\Throwable $e) {
			return ['total' => 0, 'paid' => 0, 'unpaid' => 0];
		}
	}
}

