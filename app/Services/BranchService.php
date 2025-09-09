<?php
namespace App\Services;

use App\Models\BranchModel;

class BranchService
{
    protected $model;

    public function __construct()
    {
        $this->model = new BranchModel();
    }

    public function getAll($filters = [])
    {
    // Use the model builder and exclude soft-deleted rows
    $builder = $this->model->builder();
    $builder->where('deleted_at', null);
        
        // Apply filters
        if (!empty($filters['search'])) {
            $builder->like('name', $filters['search']);
        }
        
        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }
        
        if (!empty($filters['city'])) {
            $builder->like('address', $filters['city']);
        }
        
        return $builder->orderBy('name', 'ASC')->get()->getResultArray();
    }

    public function get($id)
    {
        return $this->model->getBranchWithDetails($id);
    }

    public function create(array $data)
    {
        return $this->model->insert($data);
    }

    public function update($id, array $data)
    {
        return (bool) $this->model->update($id, $data);
    }

    public function delete($id)
    {
    // Model is configured to use soft deletes; this will set deleted_at
    return (bool) $this->model->delete($id);
    }
}
