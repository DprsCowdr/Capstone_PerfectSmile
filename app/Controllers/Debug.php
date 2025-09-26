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
        echo "<h3>Today's Appointments:</h3>";
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
        
        $service = new \App\Services\AppointmentService();
        // split dt into date/time for the service
    $data['appointment_date'] = date('Y-m-d');
    $data['appointment_time'] = date('H:i', strtotime('10:00'));
    $data['created_by_role'] = 'staff';
        $res = $service->createAppointment($data);
        if (!empty($res['success']) && !empty($res['record'])) {
            $id = $res['record']['id'];
            echo "<h2>Test appointment created successfully!</h2>";
            echo "<p>Appointment ID: {$id}</p>";
            echo "<p>Date/Time: " . ($res['record']['appointment_datetime'] ?? '') . "</p>";
            echo "<p><a href='" . base_url('checkin') . "'>Go to Check-in Dashboard</a></p>";
        } else {
            echo "<h2>Failed to create test appointment</h2>";
            echo "<pre>" . print_r($res, true) . "</pre>";
        }
    }

    /**
     * Approve an appointment by ID (debug only)
     * URL: /debug/approve-test/{id}
     */
    public function approveTestAppointment($id = null)
    {
        $id = (int)$id;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid appointment id']);
            return;
        }

        // Allow a force-approve path in development/testing to bypass business rules
        $force = $this->request->getGet('force');
        if (defined('ENVIRONMENT') && in_array(ENVIRONMENT, ['development', 'testing']) && $force == '1') {
            // Directly mark appointment as approved in DB for test purposes
            $appointmentModel = new \App\Models\AppointmentModel();
            $now = date('Y-m-d H:i:s');
            $data = [
                'approval_status' => 'approved',
                'status' => 'confirmed',
                'approved_at' => $now,
                'updated_at' => $now,
            ];
            try {
                $updated = $appointmentModel->update($id, $data);
                if ($updated) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Appointment force-approved (dev mode)', 'id' => $id]);
                    return;
                }
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to update appointment record', 'id' => $id]);
                return;
            } catch (\Exception $e) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Exception updating appointment: ' . $e->getMessage()]);
                return;
            }
        }

        $service = new \App\Services\AppointmentService();
        $res = $service->approveAppointment($id, null);
        header('Content-Type: application/json');
        echo json_encode($res);
    }

    /**
     * List recent branch notifications (debug only)
     * URL: /debug/branch-notifications
     */
    public function listBranchNotifications()
    {
        if (!class_exists('\App\Models\BranchNotificationModel')) {
            echo json_encode(['success' => false, 'message' => 'BranchNotificationModel not available']);
            return;
        }

        $bnModel = new \App\Models\BranchNotificationModel();
        $list = $bnModel->orderBy('created_at', 'DESC')->limit(20)->findAll();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'notifications' => $list]);
    }

    /**
     * Run a quick smoke test over HTTP to exercise AppointmentService flows (dev-only).
     * URL: /debug/smoke-run
     */
    public function smokeRun()
    {
        $service = new \App\Services\AppointmentService();
        $results = [];

        $patientData = [
            'user_id' => 999997,
            'appointment_date' => date('Y-m-d', strtotime('+2 days')),
            'appointment_time' => '09:45',
            'branch_id' => 1,
            'dentist_id' => 1,
            'appointment_type' => 'scheduled',
            'created_by_role' => 'patient',
        ];
        $results['patient_create'] = $service->createAppointment($patientData);

        $staffData = $patientData;
        $staffData['user_id'] = 999998;
        $staffData['appointment_time'] = '10:15';
        $staffData['created_by_role'] = 'staff';
        $results['staff_create'] = $service->createAppointment($staffData);

        $approveId = $results['staff_create']['record']['id'] ?? null;
        if ($approveId) {
            $results['approve_result'] = $service->approveAppointment($approveId, null);
        } else {
            $results['approve_result'] = ['success' => false, 'message' => 'No staff-created appointment id to approve'];
        }

        // Include recent branch notifications
        $bnList = [];
        if (class_exists('App\\Models\\BranchNotificationModel')) {
            $bnModel = new \App\Models\BranchNotificationModel();
            $bnList = $bnModel->orderBy('created_at', 'DESC')->limit(10)->findAll();
        }
        $results['branch_notifications'] = $bnList;

        header('Content-Type: application/json');
        echo json_encode($results, JSON_PRETTY_PRINT);
    }
}