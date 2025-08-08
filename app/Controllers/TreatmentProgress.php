<?php

namespace App\Controllers;

use App\Models\AppointmentModel;
use App\Models\DentalRecordModel;
use App\Models\DentalChartModel;
use App\Models\ProcedureModel;
use App\Controllers\Auth;

class TreatmentProgress extends BaseController
{
    protected $appointmentModel;
    protected $dentalRecordModel;
    protected $dentalChartModel;
    protected $procedureModel;

    public function __construct()
    {
        $this->appointmentModel = new AppointmentModel();
        $this->dentalRecordModel = new DentalRecordModel();
        $this->dentalChartModel = new DentalChartModel();
        $this->procedureModel = new ProcedureModel();
    }

    /**
     * Show treatment progress for a patient
     */
    public function index($patientId)
    {
        $user = Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['doctor', 'admin', 'patient'])) {
            return redirect()->to('/login');
        }

        // If patient, can only view their own progress
        if ($user['user_type'] === 'patient' && $user['id'] != $patientId) {
            return redirect()->to('/patient/dashboard');
        }

        $userModel = new \App\Models\UserModel();
        $patient = $userModel->find($patientId);
        
        if (!$patient || $patient['user_type'] !== 'patient') {
            session()->setFlashdata('error', 'Patient not found');
            return redirect()->back();
        }

        // Get all appointments with progress
        $appointments = $this->appointmentModel
            ->select('appointments.*, dentist.name as dentist_name, branches.name as branch_name')
            ->join('user as dentist', 'dentist.id = appointments.dentist_id', 'left')
            ->join('branches', 'branches.id = appointments.branch_id', 'left')
            ->where('appointments.user_id', $patientId)
            ->orderBy('appointment_datetime', 'DESC')
            ->findAll();

        // Get dental records with charts
        $dentalRecords = $this->dentalRecordModel->getPatientRecords($patientId);
        
        // Get teeth needing treatment
        $teethNeedingTreatment = $this->dentalChartModel->getTeethNeedingTreatment($patientId);
        
        // Get scheduled procedures
        $procedures = $this->procedureModel->getPatientProcedures($patientId);

        // Calculate treatment progress
        $totalTeethExamined = $this->dentalChartModel->where('dental_record_id IN (SELECT id FROM dental_record WHERE user_id = ' . $patientId . ')')->countAllResults();
        $healthyTeeth = $this->dentalChartModel->where('dental_record_id IN (SELECT id FROM dental_record WHERE user_id = ' . $patientId . ')')->where('condition', 'healthy')->countAllResults();
        $treatedTeeth = $this->dentalChartModel->where('dental_record_id IN (SELECT id FROM dental_record WHERE user_id = ' . $patientId . ')')->whereNotIn('status', ['none', 'healthy'])->countAllResults();

        $progressStats = [
            'total_appointments' => count($appointments),
            'completed_appointments' => count(array_filter($appointments, fn($a) => $a['status'] === 'completed')),
            'total_teeth_examined' => $totalTeethExamined,
            'healthy_teeth' => $healthyTeeth,
            'teeth_needing_treatment' => count($teethNeedingTreatment),
            'treatments_completed' => $treatedTeeth
        ];

        return view('treatment/progress', [
            'user' => $user,
            'patient' => $patient,
            'appointments' => $appointments,
            'dentalRecords' => $dentalRecords,
            'teethNeedingTreatment' => $teethNeedingTreatment,
            'procedures' => $procedures,
            'progressStats' => $progressStats
        ]);
    }

    /**
     * Update treatment status
     */
    public function updateStatus($appointmentId)
    {
        $user = Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['doctor', 'admin'])) {
            return redirect()->to('/login');
        }

        $status = $this->request->getPost('status');
        $notes = $this->request->getPost('notes');

        $result = $this->appointmentModel->update($appointmentId, [
            'treatment_status' => $status,
            'treatment_notes' => $notes,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => 'Treatment status updated']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to update status']);
        }
    }
}
