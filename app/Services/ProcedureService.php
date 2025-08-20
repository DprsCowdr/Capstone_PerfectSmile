<?php

namespace App\Services;

use App\Models\ProcedureModel;
use App\Models\UserModel;

class ProcedureService
{
    protected $procedureModel;
    protected $userModel;

    public function __construct()
    {
        $this->procedureModel = new ProcedureModel();
        $this->userModel = new UserModel();
    }

    public function getAllProcedures($page = 1, $limit = 10, $search = null)
    {
        $offset = ($page - 1) * $limit;
        
        $builder = $this->procedureModel->builder();
        $builder->select('procedures.*, user.name as patient_name');
        $builder->join('user', 'user.id = procedures.user_id', 'left');
        // Only fetch non-deleted records
        $builder->where('procedures.deleted_at', null);

        if ($search) {
            $builder->groupStart();
            $builder->like('procedures.title', $search);
            $builder->orLike('procedures.procedure_name', $search);
            $builder->orLike('user.name', $search);
            $builder->groupEnd();
        }

        $total = $builder->countAllResults(false);
        $procedures = $builder->limit($limit, $offset)->get()->getResultArray();
        
        return [
            'procedures' => $procedures,
            'total' => $total,
            'pages' => ceil($total / $limit),
            'current_page' => $page
        ];
    }

    public function createProcedure($data)
    {
        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            $result = $this->procedureModel->insert($data);
            if ($db->transStatus() === false || !$result) {
                $db->transRollback();
                return [
                    'success' => false,
                    'message' => 'Failed to create procedure (transaction rolled back).'
                ];
            }
            $db->transCommit();
            return [
                'success' => true,
                'message' => 'Procedure created successfully.'
            ];
        } catch (\Exception $e) {
            $db->transRollback();
            return [
                'success' => false,
                'message' => 'Failed to create procedure: ' . $e->getMessage()
            ];
        }
    }

    public function getProcedureDetails($id)
    {
    // Allow admin to view soft-deleted procedures
    $procedure = $this->procedureModel->withDeleted()->find($id);
        
        if (!$procedure) {
            return [
                'success' => false,
                'message' => 'Procedure not found.'
            ];
        }
        
        // Get patient name
        $patient = $this->userModel->find($procedure['user_id']);
        $procedure['patient_name'] = $patient ? $patient['name'] : 'Unknown Patient';
        
        return [
            'success' => true,
            'data' => $procedure
        ];
    }

    public function updateProcedure($id, $data)
    {
        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $result = $this->procedureModel->update($id, $data);
            if ($db->transStatus() === false || !$result) {
                $db->transRollback();
                return [
                    'success' => false,
                    'message' => 'Failed to update procedure (transaction rolled back).'
                ];
            }
            $db->transCommit();
            return [
                'success' => true,
                'message' => 'Procedure updated successfully.'
            ];
        } catch (\Exception $e) {
            $db->transRollback();
            return [
                'success' => false,
                'message' => 'Failed to update procedure: ' . $e->getMessage()
            ];
        }
    }

    public function deleteProcedure($id)
    {
        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $result = $this->procedureModel->delete($id);
            if ($db->transStatus() === false || !$result) {
                $db->transRollback();
                return [
                    'success' => false,
                    'message' => 'Failed to delete procedure (transaction rolled back).'
                ];
            }
            $db->transCommit();
            return [
                'success' => true,
                'message' => 'Procedure deleted successfully.'
            ];
        } catch (\Exception $e) {
            $db->transRollback();
            return [
                'success' => false,
                'message' => 'Failed to delete procedure: ' . $e->getMessage()
            ];
        }
    }

    public function getPatients()
    {
        return $this->userModel->where('user_type', 'patient')->findAll();
    }

    public function getServices()
    {
        // Return empty array for now - can be expanded later
        return [];
    }
}
