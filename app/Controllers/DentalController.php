<?php

namespace App\Controllers;

use App\Traits\AdminAuthTrait;

class DentalController extends BaseAdminController
{
    use AdminAuthTrait;
    
    protected function getAuthenticatedUser()
    {
        return $this->checkAdminAuth();
    }
    
    protected function getAuthenticatedUserApi()
    {
        return $this->checkAdminAuthApi();
    }

    // ==================== DENTAL RECORDS ====================
    
    /**
     * View all dental records
     */
    public function dentalRecords()
    {
        $user = $this->checkAdminAuth();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $dentalRecordModel = new \App\Models\DentalRecordModel();
        $appointmentModel = new \App\Models\AppointmentModel();
        
        // Get all dental records with patient and dentist information
        $records = $dentalRecordModel->select('dental_record.*, patient.name as patient_name, patient.email as patient_email, dentist.name as dentist_name, appointments.appointment_datetime')
                                   ->join('user as patient', 'patient.id = dental_record.user_id')
                                   ->join('user as dentist', 'dentist.id = dental_record.dentist_id')
                                   ->join('appointments', 'appointments.id = dental_record.appointment_id', 'left')
                                   ->orderBy('record_date', 'DESC')
                                   ->findAll();

        // Get appointments without dental records (only approved appointments)
        $appointmentsWithoutRecords = $appointmentModel->select('appointments.*, patient.name as patient_name, dentist.name as dentist_name, branches.name as branch_name')
                                                      ->join('user as patient', 'patient.id = appointments.user_id')
                                                      ->join('user as dentist', 'dentist.id = appointments.dentist_id')
                                                      ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                                      ->where('appointments.status', 'confirmed')
                                                      ->where('appointments.approval_status', 'approved') // Only approved appointments
                                                      ->whereNotIn('appointments.id', function($builder) {
                                                          $builder->select('appointment_id')->from('dental_record');
                                                      })
                                                      ->orderBy('appointment_datetime', 'DESC')
                                                      ->findAll();

        return view('admin/dental/records', [
            'user' => $user,
            'records' => $records,
            'appointmentsWithoutRecords' => $appointmentsWithoutRecords
        ]);
    }

    /**
     * View specific dental record with chart
     */
    public function viewRecord($recordId)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $dentalRecordModel = new \App\Models\DentalRecordModel();
        
        $record = $dentalRecordModel->getRecordWithChart($recordId);
        
        if (!$record) {
            session()->setFlashdata('error', 'Dental record not found');
            return redirect()->to('/admin/dental-records');
        }

        return view('admin/dental/view_record', [
            'user' => $user,
            'record' => $record
        ]);
    }

    /**
     * Show form to create a new dental record
     */
    public function createRecord($appointmentId)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $appointmentModel = new \App\Models\AppointmentModel();
        
        $appointment = $appointmentModel->select('appointments.*, patient.name as patient_name, dentist.name as dentist_name, branches.name as branch_name')
                                      ->join('user as patient', 'patient.id = appointments.user_id')
                                      ->join('user as dentist', 'dentist.id = appointments.dentist_id')
                                      ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                      ->find($appointmentId);

        if (!$appointment) {
            session()->setFlashdata('error', 'Appointment not found');
            return redirect()->to('/admin/dental-records');
        }

        $dentalRecordModel = new \App\Models\DentalRecordModel();
        $existingRecord = $dentalRecordModel->where('appointment_id', $appointmentId)->first();
        
        if ($existingRecord) {
            return redirect()->to('/admin/dental-records/' . $existingRecord['id'])
                           ->with('info', 'Dental record already exists for this appointment.');
        }

        return view('admin/dental/create_record', [
            'user' => $user,
            'appointment' => $appointment
        ]);
    }

    // ==================== DENTAL CHARTS ====================
    
    /**
     * View all dental charts
     */
    public function charts()
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $dentalChartModel = new \App\Models\DentalChartModel();
        $dentalRecordModel = new \App\Models\DentalRecordModel();
        $appointmentModel = new \App\Models\AppointmentModel();
        
        $appointments = $appointmentModel->select('appointments.*, patient.name as patient_name, dentist.name as dentist_name, branches.name as branch_name')
                                        ->join('user as patient', 'patient.id = appointments.user_id')
                                        ->join('user as dentist', 'dentist.id = appointments.dentist_id')
                                        ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                        ->where('appointments.status', 'confirmed')
                                        ->where('appointments.approval_status', 'approved')
                                        ->orderBy('appointment_datetime', 'DESC')
                                        ->findAll();

        foreach ($appointments as &$appointment) {
            $dentalRecord = $dentalRecordModel->where('appointment_id', $appointment['id'])->first();
            
            if ($dentalRecord) {
                $appointment['dental_record_id'] = $dentalRecord['id'];
                $appointment['has_chart'] = $dentalChartModel->where('dental_record_id', $dentalRecord['id'])->countAllResults() > 0;
            } else {
                $appointment['dental_record_id'] = null;
                $appointment['has_chart'] = false;
            }
        }

        return view('admin/dental/charts', [
            'user' => $user,
            'appointments' => $appointments
        ]);
    }

    /**
     * View specific dental chart
     */
    public function viewChart($appointmentId)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $appointmentModel = new \App\Models\AppointmentModel();
        $dentalChartModel = new \App\Models\DentalChartModel();
        $dentalRecordModel = new \App\Models\DentalRecordModel();
        
        $appointment = $appointmentModel->select('appointments.*, patient.name as patient_name, dentist.name as dentist_name, branches.name as branch_name')
                                      ->join('user as patient', 'patient.id = appointments.user_id')
                                      ->join('user as dentist', 'dentist.id = appointments.dentist_id')
                                      ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                      ->find($appointmentId);

        if (!$appointment) {
            session()->setFlashdata('error', 'Appointment not found');
            return redirect()->to('/admin/dental-charts');
        }

        $dentalRecord = $dentalRecordModel->where('appointment_id', $appointmentId)->first();
        $dentalChart = $dentalRecord ? $dentalChartModel->getRecordChart($dentalRecord['id']) : [];
        $teethLayout = $dentalChartModel->getToothLayout();

        return view('admin/dental/view_chart', [
            'user' => $user,
            'appointment' => $appointment,
            'dentalChart' => $dentalChart,
            'dentalRecord' => $dentalRecord,
            'teethLayout' => $teethLayout
        ]);
    }

    /**
     * Show dental charting form for a specific appointment
     */
    public function createChart($appointmentId)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $appointmentModel = new \App\Models\AppointmentModel();
        $serviceModel = new \App\Models\ServiceModel();
        
        $appointment = $appointmentModel->select('appointments.*, patient.name as patient_name, dentist.name as dentist_name, branches.name as branch_name')
                                      ->join('user as patient', 'patient.id = appointments.user_id')
                                      ->join('user as dentist', 'dentist.id = appointments.dentist_id')
                                      ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                      ->find($appointmentId);

        if (!$appointment) {
            session()->setFlashdata('error', 'Appointment not found');
            return redirect()->to('/admin/dental-charts');
        }

        $services = $serviceModel->findAll();
        $toothLayout = \App\Models\DentalChartModel::getToothLayout();
        $toothConditions = \App\Models\DentalChartModel::getToothConditions();

        return view('admin/dental/create_chart', [
            'user' => $user,
            'appointment' => $appointment,
            'services' => $services,
            'toothLayout' => $toothLayout,
            'toothConditions' => $toothConditions
        ]);
    }

    /**
     * Edit existing dental chart
     */
    public function editChart($appointmentId)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $appointmentModel = new \App\Models\AppointmentModel();
        $dentalChartModel = new \App\Models\DentalChartModel();
        $dentalRecordModel = new \App\Models\DentalRecordModel();
        $serviceModel = new \App\Models\ServiceModel();
        
        $appointment = $appointmentModel->select('appointments.*, patient.name as patient_name, dentist.name as dentist_name, branches.name as branch_name')
                                      ->join('user as patient', 'patient.id = appointments.user_id')
                                      ->join('user as dentist', 'dentist.id = appointments.dentist_id')
                                      ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                      ->find($appointmentId);

        if (!$appointment) {
            session()->setFlashdata('error', 'Appointment not found');
            return redirect()->to('/admin/dental-charts');
        }

        $dentalRecord = $dentalRecordModel->where('appointment_id', $appointmentId)->first();
        $dentalChart = $dentalChartModel->getAppointmentChart($appointmentId);
        $services = $serviceModel->findAll();
        $toothLayout = \App\Models\DentalChartModel::getToothLayout();
        $toothConditions = \App\Models\DentalChartModel::getToothConditions();

        return view('admin/dental/edit_chart', [
            'user' => $user,
            'appointment' => $appointment,
            'dentalRecord' => $dentalRecord,
            'dentalChart' => $dentalChart,
            'services' => $services,
            'toothLayout' => $toothLayout,
            'toothConditions' => $toothConditions
        ]);
    }

    // ==================== PATIENT CHECKUPS ====================
    
    /**
     * Patient checkup overview
     */
    public function patientCheckups()
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $userModel = new \App\Models\UserModel();
        $dentalRecordModel = new \App\Models\DentalRecordModel();
        $appointmentModel = new \App\Models\AppointmentModel();
        $dentalChartModel = new \App\Models\DentalChartModel();
        
        $patients = $userModel->where('user_type', 'patient')
                             ->where('status', 'active')
                             ->orderBy('name', 'ASC')
                             ->findAll();
        
        foreach ($patients as &$patient) {
            $patient['total_appointments'] = $appointmentModel->where('user_id', $patient['id'])->countAllResults();
            $patient['total_records'] = $dentalRecordModel->where('user_id', $patient['id'])->countAllResults();
            
            $lastRecord = $dentalRecordModel->where('user_id', $patient['id'])
                                          ->orderBy('record_date', 'DESC')
                                          ->first();
            $patient['last_checkup'] = $lastRecord ? $lastRecord['record_date'] : null;
            
            $patient['teeth_needing_treatment'] = $dentalChartModel->getTeethNeedingTreatment($patient['id']);
            $patient['treatment_count'] = count($patient['teeth_needing_treatment']);
        }

        return view('admin/patients/checkups', [
            'user' => $user,
            'patients' => $patients
        ]);
    }

    /**
     * Test 3D dental model viewer
     */
    public function test3DViewer()
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        return view('admin/dental/test_3d_viewer', [
            'user' => $user
        ]);
    }

    // ==================== STORE/UPDATE METHODS ====================
    // Add methods for storing and updating dental records/charts
    // These would contain the form processing logic
}
