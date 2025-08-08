<?php

namespace App\Models;

use CodeIgniter\Model;

class ProcedureServiceModel extends Model
{
    protected $table            = 'procedure_service';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'procedure_id',
        'service_id'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'procedure_id' => 'required|integer',
        'service_id'   => 'required|integer'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Get services for a specific procedure
     */
    public function getProcedureServices($procedureId)
    {
        return $this->select('procedure_service.*, services.name, services.description, services.price')
                   ->join('services', 'services.id = procedure_service.service_id')
                   ->where('procedure_id', $procedureId)
                   ->findAll();
    }

    /**
     * Link multiple services to a procedure
     */
    public function linkServices($procedureId, $serviceIds)
    {
        $data = [];
        foreach ($serviceIds as $serviceId) {
            $data[] = [
                'procedure_id' => $procedureId,
                'service_id'   => $serviceId
            ];
        }
        return $this->insertBatch($data);
    }

    /**
     * Remove all services from a procedure
     */
    public function removeServicesFromProcedure($procedureId)
    {
        return $this->where('procedure_id', $procedureId)->delete();
    }
}
