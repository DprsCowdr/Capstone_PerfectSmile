<?php

namespace App\Controllers;

use App\Models\AppointmentModel;
use App\Models\ServiceModel;
use App\Models\BranchModel;

class Guest extends BaseController
{
    protected $appointmentModel;
    protected $serviceModel;
    protected $branchModel;

    public function __construct()
    {
        $this->appointmentModel = new \App\Models\AppointmentModel();
        $this->serviceModel = new \App\Models\ServiceModel();
        $this->branchModel = new \App\Models\BranchModel();
    }

    /**
     * Display appointment booking form for guests
     */
    public function bookAppointment()
    {
        $data = [
            'services' => $this->serviceModel->findAll(),
            'branches' => $this->branchModel->findAll()
        ];

        return view('guest/book_appointment', $data);
    }

    /**
     * Handle guest appointment submission
     */
    public function submitAppointment()
    {
        // Server-side validation
        $validation =  \Config\Services::validation();
        $validation->setRules([
            'patient_name' => 'required|min_length[2]|max_length[100]',
            'patient_email' => 'required|valid_email',
            'patient_phone' => 'required|min_length[10]|max_length[20]',
            'appointment_date' => 'required|valid_date',
            'appointment_time' => 'required',
            'service' => 'required',
            'message' => 'max_length[500]'
        ]);
        
        $formData = [
            'patient_name' => $this->request->getPost('patient_name'),
            'patient_email' => $this->request->getPost('patient_email'),
            'patient_phone' => $this->request->getPost('patient_phone'),
            'appointment_date' => $this->request->getPost('appointment_date'),
            'appointment_time' => $this->request->getPost('appointment_time'),
            'service' => $this->request->getPost('service'),
            'message' => $this->request->getPost('message')
        ];

        if (!$validation->run($formData)) {
            return redirect()->back()->withInput()->with('error', $validation->getErrors());
        }

        // Create appointment data - the model will combine date/time into appointment_datetime
        $appointmentData = [
            'patient_name' => $this->request->getPost('patient_name'),
            'patient_email' => $this->request->getPost('patient_email'),
            'patient_phone' => $this->request->getPost('patient_phone'),
            'appointment_date' => $this->request->getPost('appointment_date'), // model will handle conversion
            'appointment_time' => $this->request->getPost('appointment_time'), // model will handle conversion
            'service' => $this->request->getPost('service'),
            'message' => $this->request->getPost('message'),
            'status' => 'pending',
            'approval_status' => 'pending'
        ];

        $appointmentId = $this->appointmentModel->insert($appointmentData);

        // Link service to appointment
        if ($appointmentId) {
            $appointmentServiceModel = new \App\Models\AppointmentServiceModel();
            $appointmentServiceModel->insert([
                'appointment_id' => $appointmentId,
                'service_id' => $this->request->getPost('service_id')
            ]);
        }

        session()->setFlashdata('success', 'Appointment booked successfully! We will contact you soon to confirm.');
        return redirect()->to('/guest/book-appointment');
    }

    /**
     * Display available services
     */
    public function services()
    {
        $data = [
            'services' => $this->serviceModel->findAll()
        ];

        return view('guest/services', $data);
    }

    /**
     * Display branch locations
     */
    public function branches()
    {
        $data = [
            'branches' => $this->branchModel->findAll()
        ];

        return view('guest/branches', $data);
    }

    /**
     * Return available times for a given date and branch (AJAX)
     */
    public function availableTimes()
    {
        $date = $this->request->getGet('date');
        $branchId = $this->request->getGet('branch_id');
        if (!$date || !$branchId) {
            return $this->response->setJSON([]);
        }
        $allTimes = ['08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00'];
        $booked = $this->appointmentModel
            ->where('DATE(appointment_datetime)', $date)
            ->where('branch_id', $branchId)
            ->whereIn('status', ['confirmed', 'scheduled', 'ongoing'])
            ->select('TIME(appointment_datetime) as time')
            ->findAll();
        $bookedTimes = array_column($booked, 'time');
        $available = array_values(array_diff($allTimes, $bookedTimes));
        return $this->response->setJSON($available);
    }
} 