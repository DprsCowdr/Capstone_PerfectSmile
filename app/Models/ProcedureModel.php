<?php

namespace App\Models;

use CodeIgniter\Model;

class ProcedureModel extends Model
{
    protected $table            = 'procedures';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'procedure_name',
        'description',
        'procedure_date'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'user_id'        => 'required|integer',
        'procedure_name' => 'required|string|max_length[255]',
        'procedure_date' => 'required|valid_date'
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
     * Get procedures for a specific patient
     */
    public function getPatientProcedures($patientId)
    {
        return $this->where('user_id', $patientId)
                   ->orderBy('procedure_date', 'DESC')
                   ->findAll();
    }

    /**
     * Get procedures with associated services
     */
    public function getProcedureWithServices($procedureId)
    {
        $db = \Config\Database::connect();
        
        return $db->table('procedures p')
                  ->select('p.*, s.name as service_name, s.price, ps.service_id')
                  ->join('procedure_service ps', 'ps.procedure_id = p.id', 'left')
                  ->join('services s', 's.id = ps.service_id', 'left')
                  ->where('p.id', $procedureId)
                  ->get()
                  ->getResultArray();
    }

    /**
     * Schedule a new procedure
     */
    public function scheduleProcedure($data, $serviceIds = [])
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // Insert procedure
        $procedureId = $this->insert($data);

        // Link services to procedure
        if (!empty($serviceIds) && $procedureId) {
            $procedureServiceModel = new \App\Models\ProcedureServiceModel();
            foreach ($serviceIds as $serviceId) {
                $procedureServiceModel->insert([
                    'procedure_id' => $procedureId,
                    'service_id'   => $serviceId
                ]);
            }
        }

        $db->transComplete();
        return $db->transStatus() ? $procedureId : false;
    }
}
