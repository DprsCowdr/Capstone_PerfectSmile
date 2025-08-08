<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceModel extends Model
{
    protected $table = 'services';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'name', 'description', 'price', 'created_at', 'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|min_length[2]',
        'price' => 'required|numeric'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Service name is required',
            'min_length' => 'Service name must be at least 2 characters long'
        ],
        'price' => [
            'required' => 'Price is required',
            'numeric' => 'Price must be a valid number'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Get active services
     */
    public function getActiveServices()
    {
        return $this->findAll();
    }

    /**
     * Get service by ID with price formatting
     */
    public function getServiceWithPrice($id)
    {
        $service = $this->find($id);
        if ($service) {
            $service['formatted_price'] = '$' . number_format($service['price'], 2);
        }
        return $service;
    }
} 