<?php

namespace App\Controllers;

use App\Controllers\Auth;
use App\Models\PatientModel;
use App\Models\PatientMedicalHistoryModel;

class Patient extends BaseController
{
    public function dashboard()
    {
        // Check if user is logged in and is patient
        if (!Auth::isAuthenticated()) {
            return redirect()->to('/login');
        }

        $user = Auth::getCurrentUser();
        
        // Check if user is patient
        if ($user['user_type'] !== 'patient') {
            return redirect()->to('/dashboard');
        }
        
        // Get patient's appointments
        $appointmentModel = new \App\Models\AppointmentModel();
        $myAppointments = $appointmentModel->select('appointments.*, branches.name as branch_name')
                                          ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                          ->where('appointments.user_id', $user['id'])
                                          ->orderBy('appointments.appointment_datetime', 'DESC')
                                          ->limit(5)
                                          ->findAll();
        
        // Get upcoming appointments
        $upcomingAppointments = $appointmentModel->select('appointments.*, branches.name as branch_name')
                                                ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                                ->where('appointments.user_id', $user['id'])
                                                ->where('appointments.appointment_datetime >=', date('Y-m-d H:i:s'))
                                                ->whereIn('appointments.status', ['confirmed', 'scheduled'])
                                                ->orderBy('appointments.appointment_datetime', 'ASC')
                                                ->limit(3)
                                                ->findAll();
        
        // Get total appointment count
        $totalAppointments = $appointmentModel->where('user_id', $user['id'])->countAllResults();
        
        // Get completed treatments count
        $completedTreatments = $appointmentModel->where('user_id', $user['id'])
                                               ->where('status', 'completed')
                                               ->countAllResults();
        
        // Get pending appointments count
        $pendingAppointments = $appointmentModel->where('user_id', $user['id'])
                                               ->where('approval_status', 'pending')
                                               ->countAllResults();
        
        // Get rejected appointments count
        $rejectedAppointments = $appointmentModel->where('user_id', $user['id'])
                                                ->where('approval_status', 'declined')
                                                ->countAllResults();
        
        // Get approved appointments count
        $approvedAppointments = $appointmentModel->where('user_id', $user['id'])
                                                ->where('approval_status', 'approved')
                                               ->countAllResults();
        
        return view('patient/dashboard', [
            'user' => $user,
            'myAppointments' => $myAppointments,
            'upcomingAppointments' => $upcomingAppointments,
            'totalAppointments' => $totalAppointments,
            'completedTreatments' => $completedTreatments,
            'pendingAppointments' => $pendingAppointments,
            'rejectedAppointments' => $rejectedAppointments,
            'approvedAppointments' => $approvedAppointments
        ]);
    }

    public function bookAppointment()
    {
        if (!Auth::isAuthenticated() || Auth::getCurrentUser()['user_type'] !== 'patient') {
            return redirect()->to('/login');
        }

        $user = Auth::getCurrentUser();
        
        // Get available branches and dentists
        $branchModel = new \App\Models\BranchModel();
        $userModel = new \App\Models\UserModel();
        
        $branches = $branchModel->findAll(); // No status column in branches table
        $dentists = $userModel->where('user_type', 'dentist')->where('status', 'active')->findAll(); // Fixed: Use 'dentist' not 'doctor'
        
        return view('patient/book_appointment', [
            'user' => $user,
            'branches' => $branches,
            'dentists' => $dentists
        ]);
    }

    public function calendar()
    {
        if (!Auth::isAuthenticated() || Auth::getCurrentUser()['user_type'] !== 'patient') {
            return redirect()->to('/login');
        }

        $user = Auth::getCurrentUser();
        
        // Get available branches and dentists
        $branchModel = new \App\Models\BranchModel();
        $userModel = new \App\Models\UserModel();
        $appointmentModel = new \App\Models\AppointmentModel();
        
        $branches = $branchModel->findAll(); // No status column in branches table
        $dentists = $userModel->where('user_type', 'dentist')->where('status', 'active')->findAll(); // Fixed: Use 'dentist' not 'doctor'
        
        // Get existing appointments to show on calendar (for current month and next month)
        $currentDate = date('Y-m-01'); // First day of current month
        $nextMonthDate = date('Y-m-01', strtotime('+2 months')); // First day of month after next
        
        $appointments = $appointmentModel->select('appointments.*, branches.name as branch_name, dentist.name as dentist_name')
                                        ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                        ->join('user as dentist', 'dentist.id = appointments.dentist_id', 'left')
                                        ->where('appointments.appointment_datetime >=', $currentDate)
                                        ->where('appointments.appointment_datetime <', $nextMonthDate)
                                        ->whereIn('appointments.approval_status', ['approved', 'pending'])
                                        ->findAll();
        
        return view('patient/calendar', [
            'user' => $user,
            'branches' => $branches,
            'dentists' => $dentists,
            'appointments' => $appointments
        ]);
    }

    public function submitAppointment()
    {
        if (!Auth::isAuthenticated() || Auth::getCurrentUser()['user_type'] !== 'patient') {
            return redirect()->to('/login');
        }

        $user = Auth::getCurrentUser();
        $appointmentModel = new \App\Models\AppointmentModel();
        
        $dentistId = $this->request->getPost('dentist_id');
        
        // If no specific dentist selected, auto-assign an available dentist from the selected branch
        if (empty($dentistId)) {
            $userModel = new \App\Models\UserModel();
            $branchId = $this->request->getPost('branch_id');
            
            // Find available dentists for the selected branch
            $availableDentists = $userModel->where('user_type', 'dentist') // Fixed: Use 'dentist' not 'doctor'
                                          ->where('status', 'active')
                                          ->findAll();
            
            if (!empty($availableDentists)) {
                // For now, assign the first available dentist
                // TODO: Could implement more sophisticated logic (least busy, preferred, etc.)
                $dentistId = $availableDentists[0]['id'];
                log_message('info', "Auto-assigned dentist ID {$dentistId} for patient appointment");
            } else {
                log_message('warning', 'No available dentists found for auto-assignment');
            }
        }

        $data = [
            'user_id' => $user['id'], // Patient books for themselves
            'branch_id' => $this->request->getPost('branch_id'),
            'dentist_id' => $dentistId, // Either selected or auto-assigned
            'appointment_date' => $this->request->getPost('appointment_date'),
            'appointment_time' => $this->request->getPost('appointment_time'),
            'appointment_type' => 'scheduled',
            'remarks' => $this->request->getPost('remarks'),
            'approval_status' => 'pending', // Patient bookings need approval
            'status' => 'pending'
        ];

        // Validate required fields
        if (empty($data['branch_id']) || empty($data['appointment_date']) || empty($data['appointment_time'])) {
            session()->setFlashdata('error', 'Please fill in all required fields');
            return redirect()->back()->withInput();
        }

        try {
            $appointmentModel->insert($data);
            session()->setFlashdata('success', 'Appointment request submitted successfully! Please wait for confirmation.');
            return redirect()->to('/patient/appointments');
        } catch (\Exception $e) {
            session()->setFlashdata('error', 'Failed to submit appointment: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function appointments()
    {
        if (!Auth::isAuthenticated() || Auth::getCurrentUser()['user_type'] !== 'patient') {
            return redirect()->to('/login');
        }

        $user = Auth::getCurrentUser();
        $appointmentModel = new \App\Models\AppointmentModel();
        
        // Get all patient's appointments
        $appointments = $appointmentModel->select('appointments.*, branches.name as branch_name, dentist.name as dentist_name')
                                        ->join('branches', 'branches.id = appointments.branch_id', 'left')
                                        ->join('user as dentist', 'dentist.id = appointments.dentist_id', 'left')
                                        ->where('appointments.user_id', $user['id'])
                                        ->orderBy('appointments.appointment_datetime', 'DESC')
                                        ->findAll();
        
        return view('patient/appointments', [
            'user' => $user,
            'appointments' => $appointments
        ]);
    }
    public function records()
    {
        if (!Auth::isAuthenticated() || Auth::getCurrentUser()['user_type'] !== 'patient') {
            return redirect()->to('/login');
        }

        $user = Auth::getCurrentUser();
        
        // Get patient's dental records
        $dentalRecordModel = new \App\Models\DentalRecordModel();
        $records = $dentalRecordModel->where('user_id', $user['id'])->findAll();
        
        return view('patient/records', [
            'user' => $user,
            'records' => $records
        ]);
    }

    public function profile()
    {
        if (!Auth::isAuthenticated() || Auth::getCurrentUser()['user_type'] !== 'patient') {
            return redirect()->to('/login');
        }

        $user = Auth::getCurrentUser();
        
        return view('patient/profile', [
            'user' => $user
        ]);
    }

    // Debug method to show all dentists
    public function showDentists()
    {
        $userModel = new \App\Models\UserModel();
        $dentists = $userModel->where('user_type', 'dentist')->findAll(); // Fixed: Use 'dentist' not 'doctor'
        
        echo "<h2>All Dentists in System:</h2>";
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr style='background-color: #f0f0f0;'><th>ID</th><th>Name</th><th>Email</th><th>Status</th><th>Created</th></tr>";
        
        foreach ($dentists as $dentist) {
            $statusColor = $dentist['status'] === 'active' ? 'green' : 'red';
            echo "<tr>";
            echo "<td>" . $dentist['id'] . "</td>";
            echo "<td>" . $dentist['name'] . "</td>";
            echo "<td>" . $dentist['email'] . "</td>";
            echo "<td style='color: {$statusColor}; font-weight: bold;'>" . $dentist['status'] . "</td>";
            echo "<td>" . $dentist['created_at'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        echo "<h3>📊 Summary:</h3>";
        $activeDentists = $userModel->where('user_type', 'dentist')->where('status', 'active')->findAll(); // Fixed: Use 'dentist' not 'doctor'
        $inactiveDentists = $userModel->where('user_type', 'dentist')->where('status', 'inactive')->findAll(); // Fixed: Use 'dentist' not 'doctor'
        
        echo "<p><strong>Total Dentists:</strong> " . count($dentists) . "</p>";
        echo "<p><strong>Active Dentists:</strong> " . count($activeDentists) . "</p>";
        echo "<p><strong>Inactive Dentists:</strong> " . count($inactiveDentists) . "</p>";
        
        if (count($activeDentists) > 0) {
            echo "<h3>🟢 Active Dentists Available for Auto-Assignment:</h3>";
            foreach ($activeDentists as $dentist) {
                echo "<p>✅ ID: {$dentist['id']}, Name: <strong>{$dentist['name']}</strong>, Email: {$dentist['email']}</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ <strong>No active dentists found!</strong> This is why dentist_id might be NULL.</p>";
        }
    }
} 