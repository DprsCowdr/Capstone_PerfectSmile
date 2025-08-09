<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_type', 'name', 'address', 'email', 'date_of_birth',
        'gender', 'password', 'phone', 'created_at', 'updated_at', 'occupation', 'nationality', 'age', 'status'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
        'email' => 'required|valid_email|is_unique[user.email,id,{id}]',
        'phone' => 'required|min_length[10]|max_length[15]',
        'password' => 'permit_empty|min_length[6]',
        'gender' => 'required|in_list[male,female,other]',
        'user_type' => 'required|in_list[admin,doctor,patient,staff,guest]',
        'status' => 'permit_empty|in_list[active,inactive]'
    ];

    protected $validationMessages = [
        'email' => [
            'required' => 'Email is required',
            'valid_email' => 'Please enter a valid email address',
            'is_unique' => 'This email is already registered'
        ],
        'password' => [
            'required' => 'Password is required',
            'min_length' => 'Password must be at least 6 characters long'
        ],
        'name' => [
            'required' => 'Name is required',
            'min_length' => 'Name must be at least 2 characters long'
        ],
        'user_type' => [
            'required' => 'User type is required',
            'in_list' => 'Invalid user type'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Authenticate user login
     */
    public function authenticate($email, $password)
    {
        $user = $this->where('email', $email)->first();
        
        // Check if user exists, password is correct, and user is active
        if ($user && password_verify($password, $user['password']) && $user['status'] === 'active') {
            return $user;
        }
        
        return false;
    }

    /**
     * Hash password before saving
     */
    protected function hashPassword($data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    /**
     * Before insert callback
     */
    protected function beforeInsert(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    /**
     * Before update callback
     */
    protected function beforeUpdate(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    /**
     * Get user by email
     */
    public function getUserByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Get users by type
     */
    public function getUsersByType($userType)
    {
        return $this->where('user_type', $userType)->findAll();
    }
} 