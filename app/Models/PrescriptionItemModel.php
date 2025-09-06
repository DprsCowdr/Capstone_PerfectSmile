<?php
namespace App\Models;

use CodeIgniter\Model;

class PrescriptionItemModel extends Model
{
    protected $table            = 'prescription_items';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';

    protected $allowedFields = [
        'prescription_id',
        'medicine_name',
        'dosage',
        'frequency',
        'duration',
        'instructions',
    ];

    // Disable automatic timestamps - prescription_items table doesn't have created_at/updated_at
    protected $useTimestamps = false;
}
