<?php

namespace App\Models;

use CodeIgniter\Model;

class BranchModel extends Model
{
    protected $table = 'branches';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'name', 'address', 'contact_number', 'created_at', 'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|min_length[2]',
        'address' => 'required|min_length[5]',
        'contact_number' => 'required'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Branch name is required',
            'min_length' => 'Branch name must be at least 2 characters long'
        ],
        'address' => [
            'required' => 'Address is required',
            'min_length' => 'Address must be at least 5 characters long'
        ],
        'contact_number' => [
            'required' => 'Contact number is required'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Get active branches
     */
    public function getActiveBranches()
    {
        return $this->findAll();
    }

    /**
     * Get branch by ID with full details
     */
    public function getBranchWithDetails($id)
    {
        return $this->find($id);
    }
} 