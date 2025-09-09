<?php
namespace App\Models;

use CodeIgniter\Model;

class BranchStaffModel extends Model
{
    // After migration we standardize on `branch_staff` table name.
    protected $table = 'branch_staff';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    // match DB columns: user_id, branch_id, position
    protected $allowedFields = [
        'user_id', 'branch_id', 'position'
    ];

    // Dates
    // table does not include created_at/updated_at in the current schema
    protected $useTimestamps = false;

    // Validation
    protected $validationRules = [
        'user_id' => 'required|integer',
        'branch_id' => 'required|integer',
        'position' => 'required|min_length[2]'
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'User ID is required',
            'integer' => 'User ID must be a number'
        ],
        'branch_id' => [
            'required' => 'Branch ID is required',
            'integer' => 'Branch ID must be a number'
        ],
        'position' => [
            'required' => 'Position is required',
            'min_length' => 'Position must be at least 2 characters'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Get all branches for a user
     */
    public function getUserBranches($userId)
    {
        return $this->select('branch_staff.*, branches.name as branch_name')
                    ->join('branches', 'branches.id = branch_staff.branch_id')
                    ->where('branch_staff.user_id', $userId)
                    ->findAll();
    }

    /**
     * Get all users for a branch
     */
    public function getBranchUsers($branchId)
    {
        return $this->select('branch_staff.*, user.name as user_name, user.email, user.user_type')
                    ->join('user', 'user.id = branch_staff.user_id')
                    ->where('branch_staff.branch_id', $branchId)
                    ->findAll();
    }

    /**
     * Check if user is assigned to branch
     */
    public function isUserAssignedToBranch($userId, $branchId)
    {
        return $this->where('user_id', $userId)
                    ->where('branch_id', $branchId)
                    ->countAllResults() > 0;
    }

    /**
     * Get user's primary branch (first assigned)
     */
    public function getUserPrimaryBranch($userId)
    {
        return $this->select('branch_staff.*, branches.name as branch_name')
                    ->join('branches', 'branches.id = branch_staff.branch_id')
                    ->where('branch_staff.user_id', $userId)
                    ->first();
    }
}
