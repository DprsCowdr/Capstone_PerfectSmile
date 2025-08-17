<?php

namespace App\Controllers;

use App\Controllers\Auth;

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
                                               ->whereIn('status', ['pending', 'scheduled'])
                                               ->countAllResults();
        
        return view('patient/dashboard', [
            'user' => $user,
            'myAppointments' => $myAppointments,
            'upcomingAppointments' => $upcomingAppointments,
            'totalAppointments' => $totalAppointments,
            'completedTreatments' => $completedTreatments,
            'pendingAppointments' => $pendingAppointments
        ]);
    }
} 