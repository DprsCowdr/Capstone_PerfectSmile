<?php

namespace App\Models;

use CodeIgniter\Model;

class DentalChartModel extends Model
{
    protected $table = 'dental_chart';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'dental_record_id',
        'tooth_number',
        'tooth_type',
        'condition',
        'status',
        'notes',
        'recommended_service_id',
        'priority',
        'estimated_cost',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'dental_record_id' => 'required|integer',
        'tooth_number' => 'required|integer|greater_than[0]|less_than[33]',
        'condition' => 'permit_empty|in_list[healthy,cavity,missing,filled,crown,root_canal,extraction_needed,other]',
        'status' => 'permit_empty|in_list[none,cleaning,filling,crown,root_canal,extraction,whitening,other]'
    ];

    protected $validationMessages = [
        'dental_record_id' => [
            'required' => 'Dental record ID is required',
            'integer' => 'Invalid record ID'
        ],
        'tooth_number' => [
            'required' => 'Tooth number is required',
            'integer' => 'Tooth number must be a number',
            'greater_than' => 'Tooth number must be between 1-32',
            'less_than' => 'Tooth number must be between 1-32'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Get dental chart for a specific record
     */
    public function getRecordChart($recordId)
    {
        return $this->where('dental_record_id', $recordId)
                   ->orderBy('tooth_number', 'ASC')
                   ->findAll();
    }

    /**
     * Save dental chart data
     */
    public function saveChart($recordId, $chartData)
    {
        // Delete existing chart data for this record
        $this->where('dental_record_id', $recordId)->delete();
        
        // Build a deduplicated set per tooth to avoid duplicates in the same record
        $byTooth = [];
        foreach ($chartData as $toothNumber => $data) {
            // Check if there's any meaningful data for this tooth
            $hasCondition = !empty($data['condition']) && $data['condition'] !== '';
            $hasTreatment = !empty($data['treatment']) && $data['treatment'] !== '';
            $hasNotes = !empty($data['notes']) && trim($data['notes']) !== '';
            
            if ($hasCondition || $hasTreatment || $hasNotes) {
                $byTooth[(int)$toothNumber] = [
                    'dental_record_id' => $recordId,
                    'tooth_number' => $toothNumber,
                    'tooth_type' => 'permanent',
                    'condition' => $data['condition'] ?? null,
                    'status' => $data['treatment'] ?? null, // Treatment maps to status field
                    'notes' => $data['notes'] ?? null,
                    'priority' => $this->getPriorityFromTreatment($data['treatment'] ?? ''),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
        }
        $insertData = array_values($byTooth);
        
        if (!empty($insertData)) {
            log_message('info', "Saving dental chart data for record {$recordId}: " . json_encode($insertData));
            $result = $this->insertBatch($insertData);
            log_message('info', "Dental chart save result: " . ($result ? 'success' : 'failed'));
            return $result;
        }
        
        log_message('info', "No dental chart data to save for record {$recordId}");
        return true;
    }

    /**
     * Get tooth layout for dental chart display
     */
    public static function getToothLayout()
    {
        return [
            'upper_right' => [18, 17, 16, 15, 14, 13, 12, 11],
            'upper_left' => [21, 22, 23, 24, 25, 26, 27, 28],
            'lower_left' => [38, 37, 36, 35, 34, 33, 32, 31],
            'lower_right' => [41, 42, 43, 44, 45, 46, 47, 48]
        ];
    }

    /**
     * Get tooth conditions for dropdown
     */
    public static function getToothConditions()
    {
        return [
            'healthy' => 'Healthy',
            'cavity' => 'Cavity',
            'missing' => 'Missing',
            'filled' => 'Filled',
            'crown' => 'Crown',
            'root_canal' => 'Root Canal',
            'extraction_needed' => 'Extraction Needed',
            'other' => 'Other'
        ];
    }

    /**
     * Get treatment options for dropdown
     */
    public static function getTreatmentOptions()
    {
        return [
            'none' => 'None',
            'cleaning' => 'Cleaning',
            'filling' => 'Filling',
            'crown' => 'Crown',
            'root_canal' => 'Root Canal',
            'extraction' => 'Extraction',
            'whitening' => 'Whitening',
            'other' => 'Other'
        ];
    }

    /**
     * Get priority based on treatment type
     */
    private function getPriorityFromTreatment($treatment)
    {
        switch ($treatment) {
            case 'extraction':
            case 'root_canal':
                return 'high';
            case 'crown':
            case 'filling':
                return 'medium';
            case 'cleaning':
            case 'whitening':
                return 'low';
            default:
                return 'low';
        }
    }

    /**
     * Get chart summary for a record
     */
    public function getChartSummary($recordId)
    {
        $chart = $this->getRecordChart($recordId);
        
        $summary = [
            'total_teeth' => count($chart),
            'healthy_teeth' => 0,
            'cavities' => 0,
            'missing_teeth' => 0,
            'filled_teeth' => 0,
            'crowns' => 0,
            'root_canals' => 0,
            'treatments_needed' => []
        ];
        
        foreach ($chart as $tooth) {
            switch ($tooth['condition']) {
                case 'healthy':
                    $summary['healthy_teeth']++;
                    break;
                case 'cavity':
                    $summary['cavities']++;
                    break;
                case 'missing':
                    $summary['missing_teeth']++;
                    break;
                case 'filled':
                    $summary['filled_teeth']++;
                    break;
                case 'crown':
                    $summary['crowns']++;
                    break;
                case 'root_canal':
                    $summary['root_canals']++;
                    break;
            }
            
            if (!empty($tooth['status']) && $tooth['status'] !== 'none') {
                $summary['treatments_needed'][] = [
                    'tooth' => $tooth['tooth_number'],
                    'treatment' => $tooth['status'],
                    'notes' => $tooth['notes']
                ];
            }
        }
        
        return $summary;
    }

    /**
     * Get teeth that need treatment for a patient
     */
    public function getTeethNeedingTreatment($patientId)
    {
        return $this->select('dental_chart.*, dental_record.record_date, dental_record.diagnosis')
                   ->join('dental_record', 'dental_record.id = dental_chart.dental_record_id')
                   ->where('dental_record.user_id', $patientId)
                   ->where('dental_chart.status !=', 'none')
                   ->where('dental_chart.status IS NOT NULL')
                   ->orderBy('dental_record.record_date', 'DESC')
                   ->orderBy('dental_chart.tooth_number', 'ASC')
                   ->findAll();
    }

    /**
     * Get patient's complete dental history (all charts from all records)
     */
    public function getPatientDentalHistory($patientId)
    {
        return $this->select('dental_chart.*, dental_record.record_date, dental_record.diagnosis, dental_record.treatment')
                   ->join('dental_record', 'dental_record.id = dental_chart.dental_record_id')
                   ->where('dental_record.user_id', $patientId)
                   ->orderBy('dental_record.record_date', 'DESC')
                   ->orderBy('dental_chart.tooth_number', 'ASC')
                   ->findAll();
    }

    public function getAppointmentChart($appointmentId)
    {
        return $this->where('appointment_id', $appointmentId)->findAll();
    }
}
