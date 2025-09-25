<?php

namespace App\Models;

use CodeIgniter\Model;

class ProcedureModel extends Model
{
    protected $table            = 'procedures';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'procedure_name',
        'title',
        'description',
        'category',
        'fee',
        'duration_minutes',
        'procedure_date',
        'status'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'user_id'        => 'required|integer',
        'procedure_name' => 'required|string|max_length[255]',
        'procedure_date' => 'required|valid_date',
        'duration_minutes' => 'permit_empty|integer'
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
     * Get all procedures with patient display fields (name, email) joined
     */
    public function getAllWithPatientNames($orderBy = 'procedure_name', $direction = 'ASC')
    {
        $db = \Config\Database::connect();

        return $db->table('procedures p')
                  ->select('p.*, u.name AS patient_name, u.email AS patient_email')
                  ->join('user u', 'u.id = p.user_id', 'left')
                  ->orderBy('p.' . $orderBy, $direction)
                  ->get()
                  ->getResultArray();
    }

    /**
     * Get single procedure with patient fields
     */
    public function getByIdWithPatient($id)
    {
        $db = \Config\Database::connect();

        return $db->table('procedures p')
                  ->select('p.*, u.name AS patient_name, u.email AS patient_email')
                  ->join('user u', 'u.id = p.user_id', 'left')
                  ->where('p.id', $id)
                  ->get()
                  ->getRowArray();
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
        /**
     * Update a procedure record securely
     */
    public function updateProcedure($id, $data)
    {
        $db = \Config\Database::connect();
        $db->transStart();
        $this->where('id', $id)->set($data)->update();
        $db->transComplete();
        return $db->transStatus();
    }
}
