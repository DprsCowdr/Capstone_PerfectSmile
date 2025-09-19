<?php

namespace App\Models;

use CodeIgniter\Model;

class AvailabilityModel extends Model
{
    // Use existing `availability` table to keep schema consistent with DB dump
    protected $table = 'availability';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id','day_of_week','start_time','end_time','type','start_datetime','end_datetime','is_recurring','notes','created_by','created_at','updated_at'];
    protected $useTimestamps = true;

    /**
     * Create a block (day off, urgent, emergency, custom)
     * $data: [user_id, type, start_datetime, end_datetime, notes, created_by]
     */
    public function createBlock(array $data)
    {
        // Basic create block; keep only essential error logging
        
        // basic validation
        if (empty($data['user_id']) || empty($data['type']) || empty($data['start_datetime']) || empty($data['end_datetime'])) {
            log_message('error', 'AvailabilityModel::createBlock() - Missing required fields');
            throw new \InvalidArgumentException('Missing required availability fields');
        }

        // Insert non-recurring ad-hoc block into the shared availability table
        $insert = [
            'user_id' => (int)$data['user_id'],
            'type' => $data['type'],
            'start_datetime' => $data['start_datetime'],
            'end_datetime' => $data['end_datetime'],
            'is_recurring' => 0,
            'notes' => $data['notes'] ?? null,
            'created_by' => $data['created_by'] ?? $data['user_id'] ?? null
        ];

        
        try {
            $result = $this->insert($insert);
            // Minimal verification: ensure insert gave an ID
            if ($returnId = $this->getInsertID()) {
                // optional: no verbose logging
            }
            
            return $result;
        } catch (\Exception $e) {
            log_message('error', 'AvailabilityModel::createBlock() - Insert failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get blocks between two datetimes (inclusive)
     * Optionally filter by user (dentist)
     */
    public function getBlocksBetween(string $start, string $end, $userId = null)
    {
        $builder = $this->builder();
        // include ad-hoc (is_recurring=0) blocks that intersect range
        $builder->groupStart()
                ->where('is_recurring', 0)
                ->where("NOT (end_datetime < '$start' OR start_datetime > '$end')")
            ->groupEnd();
        if ($userId) $builder->where('user_id', (int)$userId);
        $rows = $builder->orderBy('start_datetime','ASC')->get()->getResultArray();

        // Recurrence support has been removed: only return ad-hoc (is_recurring = 0) rows.
        // Previously this method expanded is_recurring rules into concrete occurrences.
        // Keeping recurring metadata in the DB for historical purposes, but not expanding them.
        // Return the ad-hoc rows already queried above.
        return $rows;
    }

    /**
     * Check if a dentist is blocked for a given datetime range
     * $datetime start string and duration minutes
     */
    public function isBlocked($userId, string $datetime, int $durationMinutes = 30)
    {
        $start = $datetime;
        $endDt = date('Y-m-d H:i:s', strtotime("{$datetime} +{$durationMinutes} minutes"));
        $builder = $this->builder();
    $builder->where('user_id', (int)$userId)
        ->where('is_recurring', 0)
        ->where("NOT (end_datetime <= '$start' OR start_datetime >= '$endDt')");
    $count = $builder->countAllResults();
        return $count > 0;
    }

    /**
     * Override insert to add debugging
     */
    public function insert($data = null, bool $returnID = true)
    {
        log_message('info', 'AvailabilityModel::insert() called');
        log_message('info', 'Insert data: ' . json_encode($data));
        log_message('info', 'Return ID: ' . ($returnID ? 'true' : 'false'));
        log_message('info', 'Table: ' . $this->table);
        log_message('info', 'Allowed fields: ' . json_encode($this->allowedFields));
        
        try {
            $result = parent::insert($data, $returnID);
            log_message('info', 'AvailabilityModel::insert() result: ' . json_encode($result));
            log_message('info', 'AvailabilityModel::insert() insertID: ' . $this->getInsertID());
            
            // Check if data was actually inserted
            if ($returnID && $this->getInsertID()) {
                $verifyQuery = $this->db->table($this->table)->where('id', $this->getInsertID())->get()->getRowArray();
                log_message('info', 'AvailabilityModel::insert() verification: ' . json_encode($verifyQuery));
            }
            
            return $result;
        } catch (\Exception $e) {
            log_message('error', 'AvailabilityModel::insert() exception: ' . $e->getMessage());
            log_message('error', 'AvailabilityModel::insert() trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }
}
