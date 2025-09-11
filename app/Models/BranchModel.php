<?php
namespace App\Models;

use CodeIgniter\Model;

class BranchModel extends Model
{
    protected $table = 'branches';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    // match the existing DB columns (plus timestamps)
    protected $allowedFields = [
        'name', 'address', 'contact_number', 'email', 'status', 'operating_hours', 'created_at', 'updated_at'
    ];

    // enable automatic timestamps (created_at/updated_at). Migration
    // `AddBackfillBranchTimestamps` will add and backfill these columns
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Enable soft deletes for branches
    protected $useSoftDeletes = true;
    protected $deletedField  = 'deleted_at';

    // basic validation rules
    protected $validationRules = [
        'name' => 'required|min_length[2]'
    ];

    protected $skipValidation = false;

    public function getActiveBranches()
    {
        return $this->orderBy('name', 'ASC')->findAll();
    }

    public function getBranchWithDetails($id)
    {
        return $this->find($id);
    }
}