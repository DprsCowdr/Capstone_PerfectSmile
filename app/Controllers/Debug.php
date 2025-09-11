<?php

namespace App\Controllers;

use App\Models\AppointmentModel;

class Debug extends BaseController
{
    public function checkAppointments()
    {
        $appointmentModel = new AppointmentModel();
        
        echo "<h2>Debug: Checking Appointments</h2>";
        echo "<p>Today's date: " . date('Y-m-d') . "</p>";
        echo "<p>Current datetime: " . date('Y-m-d H:i:s') . "</p>";
        
        // Get all appointments
        echo "<h3>All Appointments (last 10):</h3>";
        $allAppointments = $appointmentModel
            ->select('id, appointment_datetime, status, user_id')
            ->orderBy('appointment_datetime', 'DESC')
            ->limit(10)
            ->findAll();
            
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Date/Time</th><th>Status</th><th>User ID</th></tr>";
        foreach($allAppointments as $apt) {
            echo "<tr>";
            echo "<td>{$apt['id']}</td>";
            echo "<td>{$apt['appointment_datetime']}</td>";
            echo "<td>{$apt['status']}</td>";
            echo "<td>{$apt['user_id']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check today's appointments with the exact same query
        echo "<h3>Today's Appointments (using same query as PatientCheckin):</h3>";
        $todayAppointments = $appointmentModel
            ->select('appointments.*, user.name as patient_name')
            ->join('user', 'user.id = appointments.user_id')
            ->where('DATE(appointment_datetime)', date('Y-m-d'))
            ->whereIn('appointments.status', ['scheduled', 'confirmed', 'checked_in', 'ongoing'])
            ->findAll();
            
        echo "<p>Query found " . count($todayAppointments) . " appointments for today</p>";
        
        if (count($todayAppointments) > 0) {
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Date/Time</th><th>Status</th><th>Patient</th></tr>";
            foreach($todayAppointments as $apt) {
                echo "<tr>";
                echo "<td>{$apt['id']}</td>";
                echo "<td>{$apt['appointment_datetime']}</td>";
                echo "<td>{$apt['status']}</td>";
                echo "<td>{$apt['patient_name']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        // Check what statuses exist
        echo "<h3>All appointment statuses:</h3>";
        $statuses = $appointmentModel
            ->distinct()
            ->select('status')
            ->findAll();
        foreach($statuses as $status) {
            echo "<p>Status: {$status['status']}</p>";
        }
    }
    
    public function addTestAppointment()
    {
        $appointmentModel = new AppointmentModel();
        
        $data = [
            'branch_id' => 1,
            'dentist_id' => 2,
            'user_id' => 3,
            'appointment_datetime' => date('Y-m-d') . ' 10:00:00',
            'status' => 'confirmed',
            'appointment_type' => 'scheduled',
            'approval_status' => 'approved',
            'remarks' => 'Test appointment for today',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $appointmentModel->insert($data);
        
        if ($result) {
            echo "<h2>Test appointment created successfully!</h2>";
            echo "<p>Appointment ID: {$result}</p>";
            echo "<p>Date/Time: " . $data['appointment_datetime'] . "</p>";
            echo "<p><a href='" . base_url('checkin') . "'>Go to Check-in Dashboard</a></p>";
        } else {
            echo "<h2>Failed to create test appointment</h2>";
            echo "<pre>" . print_r($appointmentModel->errors(), true) . "</pre>";
        }
    }
}
