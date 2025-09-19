<?php

namespace App\Services;

class DashboardService
{
    protected $userModel;
    protected $branchModel;
    
    public function __construct()
    {
        $this->userModel = new \App\Models\UserModel();
        $this->branchModel = new \App\Models\BranchModel();
    }
    
    public function getStatistics()
    {
        return [
            'totalUsers' => $this->userModel->countAll(),
            'totalPatients' => $this->userModel->where('user_type', 'patient')->countAllResults(),
            'totalDentists' => $this->userModel->where('user_type', 'dentist')->countAllResults(),
            'totalBranches' => $this->branchModel->countAll(),
            'totalTreatments' => (int) \Config\Database::connect()->table('treatment_sessions')->countAllResults()
        ];
    }
    
    public function getFormData()
    {
        return [
            'patients' => $this->userModel->where('user_type', 'patient')->findAll(),
            'branches' => $this->branchModel->findAll(),
            'dentists' => $this->userModel->where('user_type', 'dentist')->where('status', 'active')->findAll(),
            'availability' => [] // For future implementation
        ];
    }

    /**
     * Get branch-scoped totals used by staff dashboard.
     * Accepts an array of branch IDs. If empty, returns zeros.
     */
    public function getBranchTotals(array $branchIds = [])
    {
        // if no branches assigned, return zeros
        if (empty($branchIds)) {
            return [
                'total_patients' => 0,
                'total_appointments' => 0,
                'total_treatments' => 0,
            ];
        }

        // Normalize IDs and prepare cache key
        $branchIds = array_map('intval', $branchIds);
        sort($branchIds);
        $cacheKey = 'staff_branch_totals_' . md5(implode(',', $branchIds));

        // Use CodeIgniter cache service (short TTL)
        try {
            $cache = \Config\Services::cache();
            $cached = $cache->get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        } catch (\Exception $e) {
            // If cache service unavailable, fall back to live calculation
            $cache = null;
        }

        $db = \Config\Database::connect();

        // total_appointments
        $appointmentCount = (int) $db->table('appointments')
            ->whereIn('branch_id', $branchIds)
            ->countAllResults();

        // total_treatments (join treatment_sessions -> appointments)
        $treatmentCountQuery = $db->table('treatment_sessions ts')
            ->select('COUNT(ts.id) as cnt')
            ->join('appointments a', 'a.id = ts.appointment_id', 'left')
            ->whereIn('a.branch_id', $branchIds)
            ->get()
            ->getRowArray();
        $treatmentCount = (int) ($treatmentCountQuery['cnt'] ?? 0);

        // total_patients: when branch-scoped, count DISTINCT users who have appointments in those branches
        if (!empty($branchIds)) {
            $patientCountQuery = $db->table('appointments')
                ->select('COUNT(DISTINCT user_id) as cnt')
                ->whereIn('branch_id', $branchIds)
                ->get()
                ->getRowArray();
            $patientCount = (int) ($patientCountQuery['cnt'] ?? 0);
        } else {
            // fallback to registered patient users when no branch scope provided
            try {
                $patientCount = (int) $this->userModel->where('user_type', 'patient')->countAllResults();
            } catch (\Exception $e) {
                $patientCount = 0;
            }
        }

        $result = [
            'total_patients' => $patientCount,
            'total_appointments' => $appointmentCount,
            'total_treatments' => $treatmentCount,
        ];

        // store in cache for short TTL (5 seconds) to balance freshness and DB load
        if (isset($cache)) {
            try { $cache->save($cacheKey, $result, 5); } catch (\Exception $e) { /* ignore cache save errors */ }
        }

        return $result;
    }
} 