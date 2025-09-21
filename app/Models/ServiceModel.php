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
        'name', 'description', 'price', 'duration_minutes', 'duration_max_minutes', 'created_at', 'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|min_length[2]',
        'price' => 'required|numeric',
        'duration_minutes' => 'permit_empty|integer|greater_than[0]',
        'duration_max_minutes' => 'permit_empty|integer|greater_than[0]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Service name is required',
            'min_length' => 'Service name must be at least 2 characters long'
        ],
        'price' => [
            'required' => 'Price is required',
            'numeric' => 'Price must be a valid number'
        ],
        'duration_minutes' => [
            'integer' => 'Duration must be a valid number',
            'greater_than' => 'Duration must be greater than 0 minutes'
        ],
        'duration_max_minutes' => [
            'integer' => 'Maximum duration must be a valid number',
            'greater_than' => 'Maximum duration must be greater than 0 minutes'
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

    /**
     * Search services by name or description
     */
    public function searchServices($searchTerm = '', $limit = 50)
    {
        $builder = $this->builder();
        
        if (!empty($searchTerm)) {
            $builder->groupStart()
                   ->like('name', $searchTerm)
                   ->orLike('description', $searchTerm)
                   ->groupEnd();
        }
        
        return $builder->orderBy('name', 'ASC')
                      ->limit($limit)
                      ->get()
                      ->getResultArray();
    }

    /**
     * Get services for checkup selection
     */
    public function getServicesForSelection()
    {
        return $this->select('id, name, description, price')
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    /**
     * Get services for selection and include optional metadata if columns exist
     * This is defensive so it won't break if DB doesn't have duration/treatment columns yet.
     */
    public function getServicesForSelectionWithMeta()
    {
        $db = \Config\Database::connect();
    $hasDuration = false;
    $durationColumn = null;
    $hasTreatmentFlag = false;
    $hasDurationMax = false;
    $durationMaxColumn = null;

        try {
            $cols = $db->query("SHOW COLUMNS FROM `services`")->getResultArray();
            foreach ($cols as $c) {
                if (!isset($c['Field'])) continue;
                // detect duration column (accept common names)
                if (in_array($c['Field'], ['duration', 'duration_minutes', 'default_duration_minutes', 'default_duration'])) {
                    $hasDuration = true;
                    $durationColumn = $c['Field'];
                }
                if (in_array($c['Field'], ['duration_max', 'duration_max_minutes', 'max_duration_minutes'])) {
                    $hasDurationMax = true;
                    $durationMaxColumn = $c['Field'];
                }
                if (in_array($c['Field'], ['treatment_area_required', 'requires_treatment_area', 'requires_treatment_area_flag'])) {
                    $hasTreatmentFlag = true;
                }
            }
        } catch (\Throwable $e) {
            // ignore — we'll just return the base fields
        }

        $services = $this->select('id, name, description, price')->orderBy('name', 'ASC')->findAll();

        // If no extra metadata, return as-is
        if (!$hasDuration && !$hasTreatmentFlag) return $services;

        // Re-query including extra columns if present
    $select = ['id', 'name', 'description', 'price'];
    if ($hasDuration && $durationColumn) $select[] = $durationColumn;
    if ($hasDurationMax && $durationMaxColumn) $select[] = $durationMaxColumn;
    if ($hasTreatmentFlag) $select[] = 'treatment_area_required';

        $sel = implode(', ', array_filter($select));
        $rows = $this->select($sel)->orderBy('name', 'ASC')->findAll();

        // Normalize column names in result to expected keys for the front-end
        if ($hasDuration && $durationColumn) {
            foreach ($rows as &$r) {
                if (isset($r[$durationColumn])) {
                    // normalize to explicit keys expected by front-end
                    $r['duration_minutes'] = (int)$r[$durationColumn];
                    // also expose 'duration' for legacy callers
                    $r['duration'] = (int)$r[$durationColumn];
                }
                if ($hasDurationMax && $durationMaxColumn && isset($r[$durationMaxColumn])) {
                    $r['duration_max_minutes'] = (int)$r[$durationMaxColumn];
                }
            }
            unset($r);
        }

        // If duration column wasn't present but duration_max exists, still normalize it
        if (!$hasDuration && $hasDurationMax && $durationMaxColumn) {
            foreach ($rows as &$r) {
                if (isset($r[$durationMaxColumn])) {
                    $r['duration_max_minutes'] = (int)$r[$durationMaxColumn];
                }
            }
            unset($r);
        }

        // treatment_area_required is already present if detected, keep as-is
        return $rows;
    }
} 