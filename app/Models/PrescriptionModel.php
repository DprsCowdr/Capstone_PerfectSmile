<?php
namespace App\Models;

use CodeIgniter\Model;

class PrescriptionModel extends Model
{
    protected $table            = 'prescriptions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields = [
        'dentist_id',
    'dentist_name',
    'license_no',
    'ptr_no',
        'patient_id',
        'appointment_id',
        'issue_date',
        'next_appointment',
        'status',
    'notes',
        'signature_url',
    ];

    // Enable automatic timestamps
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
