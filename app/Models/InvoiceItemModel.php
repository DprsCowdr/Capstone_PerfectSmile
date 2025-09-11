<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceItemModel extends Model
{
	protected $table = 'invoice_items';
	protected $primaryKey = 'id';
	protected $useAutoIncrement = true;
	protected $returnType = 'array';
	protected $useSoftDeletes = false;
	protected $protectFields = true;
	protected $allowedFields = [
		'invoice_id',
		'description',
		'quantity',
		'unit_price',
		'total',
		'procedure_id'
	];

	protected $useTimestamps = true;
	protected $dateFormat = 'datetime';
	protected $createdField = 'created_at';
	protected $updatedField = 'updated_at';

	protected $validationRules = [
		'invoice_id' => 'required|integer',
		'description' => 'required|string|max_length[255]',
		'quantity' => 'required|numeric',
		'unit_price' => 'required|decimal'
	];

	protected $skipValidation = false;
}

