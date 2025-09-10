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
        // Use request/response fallbacks in case the controller was constructed manually
        $request = $this->request ?? \Config\Services::request();
        $response = $this->response ?? \Config\Services::response();

        // Early detection for AJAX/JSON client so validation errors can be returned as JSON
        $isXhr = $request->isAJAX() || strtolower($request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest' || stripos($request->getHeaderLine('Accept'), 'application/json') !== false;
        // Log incoming payload for diagnostics
        try {
            log_message('debug', 'Guest::submitAppointment payload: ' . json_encode($request->getPost()));
        } catch (\Exception $e) {
            // ignore logging failures
        }

        // Server-side validation
        $validation =  \Config\Services::validation();

        // If the requester is an authenticated patient, prefer their session values
        $session = session();
        $currentUser = null;
        try {
            if ($session->get('user_type') === 'patient') {
                $currentUser = \App\Controllers\Auth::getCurrentUser();
            }
        } catch (\Exception $e) {
            // ignore
            $currentUser = null;
        }
        $validation->setRules([
            'patient_name' => 'required|min_length[2]|max_length[100]',
            'patient_email' => 'required|valid_email',
            'patient_phone' => 'required|min_length[10]|max_length[20]',
            'appointment_date' => 'required|valid_date',
            'appointment_time' => 'required',
            // appointment table stores services in the appointment_services join table; require service_id
            'service_id' => 'required',
            // dentist_id is optional but when present must be numeric; we'll further verify it exists and is a dentist
            'dentist_id' => 'permit_empty|numeric',
            // booking form uses 'remarks' as the textarea name
            'remarks' => 'max_length[500]'
        ]);
        
        // Resolve service_id: prefer posted service_id, then posted 'service' field, then fallback to first available service
        $availableServices = $this->serviceModel->findAll();
        $defaultServiceId = !empty($availableServices) && isset($availableServices[0]['id']) ? $availableServices[0]['id'] : null;

    $dentistRaw = $request->getPost('dentist_id');
    // Normalize dentist_id: convert empty strings to null, otherwise keep the posted value
    $dentistNormalized = ($dentistRaw === '' || $dentistRaw === null) ? null : $dentistRaw;

    $formData = [
            'patient_name' => $request->getPost('patient_name') ?: ($currentUser['name'] ?? null),
            'patient_email' => $request->getPost('patient_email') ?: ($currentUser['email'] ?? null),
            'patient_phone' => $request->getPost('patient_phone') ?: ($currentUser['phone'] ?? null),
            'appointment_date' => $request->getPost('appointment_date'),
            'appointment_time' => $request->getPost('appointment_time'),
            // prefer service_id (used to link to appointment services); fall back to 'service' if provided or default
            'service_id' => $request->getPost('service_id') ?: $request->getPost('service') ?: $defaultServiceId,
            // dentist selected from booking form (may be empty)
            'dentist_id' => $dentistNormalized,
            'remarks' => $request->getPost('remarks')
        ];

        if (!$validation->run($formData)) {
            // If AJAX/JSON client, return JSON with validation errors instead of redirecting
            if ($isXhr) {
                return $response->setJSON([
                    'success' => false,
                    'errors' => $validation->getErrors()
                ])->setStatusCode(422);
            }
            return redirect()->back()->withInput()->with('error', $validation->getErrors());
        }

        // If dentist_id was provided, verify it exists and is a dentist in the system
        $dentistId = $formData['dentist_id'] ?? null;
        if (!empty($dentistId)) {
            $userModel = new \App\Models\UserModel();
            // ensure numeric id
            $dentist = is_numeric($dentistId) ? $userModel->find((int)$dentistId) : null;
            if (!$dentist || (isset($dentist['user_type']) && $dentist['user_type'] !== 'dentist')) {
                $err = ['dentist_id' => 'Selected dentist is invalid or not available.'];
                if ($isXhr) {
                    return $response->setJSON(['success' => false, 'errors' => $err])->setStatusCode(422);
                }
                return redirect()->back()->withInput()->with('error', $err);
            }
        }

        // Create appointment data - the model will combine date/time into appointment_datetime
        $appointmentData = [
            'patient_name' => $request->getPost('patient_name') ?: ($currentUser['name'] ?? null),
            'patient_email' => $request->getPost('patient_email') ?: ($currentUser['email'] ?? null),
            'patient_phone' => $request->getPost('patient_phone') ?: ($currentUser['phone'] ?? null),
            'appointment_date' => $request->getPost('appointment_date'), // model will handle conversion
            'appointment_time' => $request->getPost('appointment_time'), // model will handle conversion
            'branch_id' => $request->getPost('branch_id'),
            'procedure_duration' => $request->getPost('procedure_duration') ?? $request->getPost('duration') ?? null,
            // persist dentist_id if provided and validated above (store as int)
            'dentist_id' => is_numeric($formData['dentist_id']) ? (int)$formData['dentist_id'] : null,
            // do not store service on appointment table; services are linked in appointment_services
            'remarks' => $request->getPost('remarks') ?? $formData['remarks'] ?? null,
            'user_id' => $currentUser['id'] ?? null,
            'status' => 'pending',
            'approval_status' => 'pending'
        ];

        // Log appointment data before insert
        try {
            log_message('debug', 'Guest::submitAppointment appointmentData: ' . json_encode($appointmentData));
        } catch (\Exception $e) {
            // ignore
        }

        $appointmentId = $this->appointmentModel->insert($appointmentData);

        // Link service to appointment using resolved service_id from $formData
        if ($appointmentId) {
            $appointmentServiceModel = new \App\Models\AppointmentServiceModel();
            if (!empty($formData['service_id'])) {
                $appointmentServiceModel->insert([
                    'appointment_id' => $appointmentId,
                    'service_id' => $formData['service_id']
                ]);
            }
        }

        // Attempt to notify branch staff: non-blocking
        try {
            $branchId = $appointmentData['branch_id'] ?? null;
            if ($branchId) {
                $branch = $this->branchModel->find($branchId);
                $notificationPayload = [
                    'branch_id' => $branchId,
                    'appointment_id' => $appointmentId,
                    'payload' => json_encode($appointmentData),
                    'sent' => 0,
                ];
                // If branch has contact_email and mailer is configured, attempt to send (best-effort)
                if (!empty($branch['contact_email'])) {
                    // Use simple mail() call as best-effort; if not configured, fallback to storing notification
                    $to = $branch['contact_email'];
                    $subject = 'New appointment request';
                    $body = "A new appointment was created:\n\n" . print_r($appointmentData, true);
                    $headers = 'From: no-reply@localhost' . "\r\n";
                    $sent = false;
                    try {
                        if (function_exists('mail')) {
                            $sent = mail($to, $subject, $body, $headers);
                        }
                    } catch (\Exception $e) {
                        // ignore
                    }
                    $notificationPayload['sent'] = $sent ? 1 : 0;
                    if ($sent) $notificationPayload['sent_at'] = date('Y-m-d H:i:s');
                }
                // Store notification record for branch dashboard to show
                $bnModel = new \App\Models\BranchNotificationModel();
                $bnModel->insert($notificationPayload);
            }
        } catch (\Exception $e) {
            // swallow notification errors; don't block booking
            log_message('error', 'Branch notification error: ' . $e->getMessage());
        }

        // Prepare success flash
        $successMsg = 'Appointment booked successfully! We will contact you soon to confirm.';
        session()->setFlashdata('success', $successMsg);

    // Robust AJAX detection: CI's isAJAX plus header checks and Accept: application/json
    $isXhr = $request->isAJAX() || strtolower($request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest' || stripos($request->getHeaderLine('Accept'), 'application/json') !== false;
    // If AJAX request (or JSON-accepting client), return JSON with created appointment info so client can update UI without redirect
    if ($isXhr) {
            try {
                $created = $this->appointmentModel->find($appointmentId);
                // If requester is a patient session or origin flagged as 'patient', strip identifying fields
                $session = session();
                $userType = $session->get('user_type') ?? null;
                $origin = $request->getPost('origin') ?? null;
                if ($userType === 'patient' || $origin === 'patient') {
                    unset($created['patient_name']);
                    unset($created['patient_email']);
                    unset($created['patient_phone']);
                }
                return $this->response->setJSON([
                    'success' => true,
                    'message' => $successMsg,
                    'appointment' => $created
                ])->setStatusCode(201);
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Appointment created but failed to retrieve record.'
                ])->setStatusCode(500);
            }
        }

        // If the form came from the patient dashboard, redirect back to patient booking
        $origin = $request->getPost('origin');
        if ($origin === 'patient') {
            return redirect()->to('/patient/book-appointment');
        }

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
    $request = $this->request ?? \Config\Services::request();
    $date = $request->getGet('date');
    // prefer explicit param, then session selected branch
    $branchId = $request->getGet('branch_id') ?: session('selected_branch_id');
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