<?php

namespace App\Controllers;

use App\Models\AppointmentModel;
use App\Models\UserModel;
use App\Models\AuditModel;
use App\Models\BranchModel;
use App\Controllers\Auth;

class TreatmentQueue extends BaseController
{
    protected $appointmentModel;
    protected $userModel;

    public function __construct()
    {
        $this->appointmentModel = new AppointmentModel();
        $this->userModel = new UserModel();
    }

    /**
     * Treatment queue dashboard (for dentists)
     */
    public function index()
    {
    $user = Auth::getCurrentUser();
    if (!$user || !in_array($user['user_type'], ['dentist', 'doctor', 'admin', 'staff'])) {
            return redirect()->to('/login');
        }

        // Get checked-in patients waiting for treatment
        $waitingQuery = $this->appointmentModel
            ->select('appointments.*, user.name as patient_name, user.phone as patient_phone, 
                     patient_checkins.checked_in_at,
                     TIMESTAMPDIFF(MINUTE, patient_checkins.checked_in_at, NOW()) as waiting_time')
            ->join('user', 'user.id = appointments.user_id')
            ->join('patient_checkins', "patient_checkins.appointment_id = appointments.id AND patient_checkins.removed_at IS NULL")
            ->where('DATE(appointment_datetime)', date('Y-m-d'))
            ->where('appointments.status', 'checked_in');

        // Only filter by dentist if the current user is a dentist/doctor
        if (in_array($user['user_type'], ['dentist', 'doctor'])) {
            $waitingQuery = $waitingQuery->where('appointments.dentist_id', $user['id']);
        }

        $waitingPatients = $waitingQuery->orderBy('patient_checkins.checked_in_at', 'ASC')->findAll();

        // Get ongoing treatments
        $ongoingQuery = $this->appointmentModel
            ->select('appointments.*, user.name as patient_name, 
                     treatment_sessions.started_at,
                     TIMESTAMPDIFF(MINUTE, treatment_sessions.started_at, NOW()) as treatment_duration')
            ->join('user', 'user.id = appointments.user_id')
            ->join('treatment_sessions', 'treatment_sessions.appointment_id = appointments.id')
            ->where('DATE(appointment_datetime)', date('Y-m-d'))
            ->where('appointments.status', 'ongoing');

        // Only filter by dentist if the current user is a dentist/doctor
        if (in_array($user['user_type'], ['dentist', 'doctor'])) {
            $ongoingQuery = $ongoingQuery->where('appointments.dentist_id', $user['id']);
        }

        $ongoingTreatments = $ongoingQuery->orderBy('treatment_sessions.started_at', 'ASC')->findAll();

        return view('queue/dashboard', [
            'user' => $user,
            'waitingPatients' => $waitingPatients,
            'ongoingTreatments' => $ongoingTreatments
        ]);
    }

    /**
     * Call next patient for treatment
     */
    public function callNext($appointmentId)
    {
        $user = Auth::getCurrentUser();
        
        // Log that this method was called (protect against null user)
        log_message('debug', "callNext method called for appointment ID: {$appointmentId}, User type: " . ($user ? $user['user_type'] : 'null'));

        if (!$user || !in_array($user['user_type'], ['dentist', 'doctor', 'admin', 'staff'])) {
            log_message('error', "Unauthorized user tried to call patient. User type: " . ($user ? $user['user_type'] : 'null'));
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(401)->setJSON(['success' => false, 'error' => 'Unauthorized']);
            }
            return redirect()->to('/login');
        }

        // Find the appointment
        $appointment = $this->appointmentModel->find($appointmentId);
        if (!$appointment) {
            log_message('error', "Appointment not found: {$appointmentId}");
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'error' => 'Appointment not found']);
            }
            session()->setFlashdata('error', 'Appointment not found');
            return redirect()->back();
        }
        
        // Check if status is valid for calling next
        if ($appointment['status'] !== 'checked_in') {
            log_message('error', "Invalid appointment status: {$appointment['status']}");
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON(['success' => false, 'error' => 'Invalid appointment or patient not checked in']);
            }
            session()->setFlashdata('error', 'Invalid appointment or patient not checked in');
            return redirect()->back();
        }

        // Delegate to internal starter so callNextAuto can reuse
        return $this->startTreatmentInternal($appointmentId, $user);
    }

    /**
     * Internal helper to start treatment for a given appointment id and user
     */
    protected function startTreatmentInternal($appointmentId, $user)
    {
        try {
            // Start database transaction
            $db = \Config\Database::connect();
            $db->transStart();

            // Update appointment status to ongoing
            $appointmentResult = $this->appointmentModel->update($appointmentId, [
                'status' => 'ongoing'
            ]);

            if (!$appointmentResult) {
                throw new \Exception('Failed to update appointment status');
            }

            // Create treatment session record using model helper
            $treatmentSessionModel = new \App\Models\TreatmentSessionModel();
            $sessionResult = $treatmentSessionModel->startSession(
                $appointmentId,
                $user['id'],
                $user['id'] ?? null,
                'normal',
                null
            );

            if (!$sessionResult) {
                $tErrors = method_exists($treatmentSessionModel, 'errors') ? $treatmentSessionModel->errors() : [];
                log_message('error', 'TreatmentSession insert failed. Model errors: ' . json_encode($tErrors));
                throw new \Exception('Failed to create treatment session');
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            log_message('debug', "Successfully called patient for treatment: {$appointmentId}");

            // Prepare success payload
            $payload = ['success' => true, 'appointmentId' => $appointmentId, 'message' => 'Patient called for treatment'];
            // If AJAX, return JSON so client JS can handle it
            if ($this->request->isAJAX()) {
                log_message('info', "callNext AJAX success for appointment: {$appointmentId}");
                return $this->response->setJSON($payload);
            }

            // If the user is a dentist/doctor, redirect to checkup module
            if (in_array($user['user_type'], ['dentist', 'doctor'])) {
                log_message('info', "Dentist called patient for treatment: {$appointmentId}");
                return redirect()->to("/checkup/patient/{$appointmentId}")
                    ->with('success', 'Patient called for treatment');
            } else {
                // Staff and admin users should be redirected back to the check-in dashboard
                log_message('info', "Staff/admin sent patient to treatment: {$appointmentId}");
                session()->setFlashdata('success', 'Patient sent to treatment queue');
                return redirect()->back();
            }

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', "Exception calling patient: {$appointmentId}. " . $e->getMessage());
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'error' => 'Failed to call patient: ' . $e->getMessage()]);
            }
            session()->setFlashdata('error', 'Failed to call patient: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Automatically pick the next appointment for the current dentist (or branch) and call them.
     * This implements FCFS: expire overdue scheduled appointments, prefer checked-in patients,
     * otherwise pick earliest scheduled appointment. Returns JSON (AJAX) or redirects.
     */
    public function callNextAuto()
    {
        $user = Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['dentist', 'doctor', 'admin', 'staff'])) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(401)->setJSON(['success' => false, 'error' => 'Unauthorized']);
            }
            return redirect()->to('/login');
        }

        // Expire overdue scheduled appointments first (grace 15 minutes)
        $this->appointmentModel->expireOverdueScheduled(15);

        // Determine dentist filter if dentist user
        $dentistId = in_array($user['user_type'], ['dentist', 'doctor']) ? $user['id'] : null;

        // Get next candidate
        $next = $this->appointmentModel->getNextAppointmentForDentist($dentistId);
        if (!$next) {
            if ($this->request->isAJAX()) return $this->response->setJSON(['success' => false, 'message' => 'No patients in queue']);
            session()->setFlashdata('info', 'No patients in queue');
            return redirect()->back();
        }

        // If candidate is not checked_in, ensure they are allowed to be started (staff may want to call)
        // For walk-ins they should be checked-in; scheduled may be started if staff chooses.

        return $this->startTreatmentInternal($next['id'], $user);
    }

    /**
     * Get queue status (AJAX)
     */
    public function getQueueStatus()
    {
        $user = Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['dentist', 'doctor', 'admin', 'staff'])) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $waitingQuery = $this->appointmentModel
            ->where('DATE(appointment_datetime)', date('Y-m-d'))
            ->where('appointments.status', 'checked_in');
            
        // Only filter by dentist if the current user is a dentist/doctor
        if (in_array($user['user_type'], ['dentist', 'doctor'])) {
            $waitingQuery = $waitingQuery->where('appointments.dentist_id', $user['id']);
        }
        
        $waitingCount = $waitingQuery->countAllResults();

        $ongoingQuery = $this->appointmentModel
            ->where('DATE(appointment_datetime)', date('Y-m-d'))
            ->where('appointments.status', 'ongoing');
            
        // Only filter by dentist if the current user is a dentist/doctor
        if (in_array($user['user_type'], ['dentist', 'doctor'])) {
            $ongoingQuery = $ongoingQuery->where('appointments.dentist_id', $user['id']);
        }
        
        $ongoingCount = $ongoingQuery->countAllResults();

        return $this->response->setJSON([
            'waiting' => $waitingCount,
            'ongoing' => $ongoingCount,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Complete a treatment
     */
    public function completeTreatment($appointmentId)
    {
        $user = Auth::getCurrentUser();
        
        log_message('debug', "completeTreatment method called for appointment ID: {$appointmentId}");
        
        if (!$user || !in_array($user['user_type'], ['dentist', 'doctor', 'admin', 'staff'])) {
            log_message('error', "Unauthorized user tried to complete treatment. User type: " . ($user ? $user['user_type'] : 'null'));
            return redirect()->to('/login');
        }

        $appointment = $this->appointmentModel->find($appointmentId);
        if (!$appointment) {
            log_message('error', "Appointment not found: {$appointmentId}");
            session()->setFlashdata('error', 'Appointment not found');
            return redirect()->back();
        }

        if ($appointment['status'] !== 'ongoing') {
            log_message('error', "Invalid appointment status for completion: {$appointment['status']}");
            session()->setFlashdata('error', 'Treatment is not currently ongoing');
            return redirect()->back();
        }

        try {
            $data = [
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s'),
                'completed_by' => $user['id']
            ];
            
            log_message('debug', "Completing treatment with data: " . json_encode($data));
            
            $result = $this->appointmentModel->update($appointmentId, $data);

            if ($result) {
                log_message('info', "Treatment completed successfully: {$appointmentId}");
                session()->setFlashdata('success', 'Treatment completed successfully');
            } else {
                log_message('error', "Failed to complete treatment: {$appointmentId}. Validation errors: " . print_r($this->appointmentModel->errors(), true));
                session()->setFlashdata('error', 'Failed to complete treatment: ' . implode(', ', $this->appointmentModel->errors()));
            }
        } catch (\Exception $e) {
            log_message('error', "Exception completing treatment: {$appointmentId}. " . $e->getMessage());
            session()->setFlashdata('error', 'Error: ' . $e->getMessage());
        }

        return redirect()->back();
    }

    /**
     * Reschedule a bumped appointment to the next available slot on the same day.
     * Expects AJAX POST with { appointmentId: int } and returns JSON { success, newTime }
     */
    public function rescheduleAppointment()
    {
        $user = Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['dentist', 'doctor', 'admin', 'staff'])) {
            if ($this->request->isAJAX()) return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Unauthorized']);
            return redirect()->to('/login');
        }

        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        $appointmentId = isset($payload['appointmentId']) ? (int)$payload['appointmentId'] : null;
        $confirm = isset($payload['confirm']) ? (bool)$payload['confirm'] : false;
        if (!$appointmentId) return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Missing appointmentId']);

        $appointment = $this->appointmentModel->find($appointmentId);
        if (!$appointment) return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Appointment not found']);

        // Compute suggested slots on the same date
        $date = substr($appointment['appointment_datetime'], 0, 10);
        $preferredTime = date('H:i', strtotime($appointment['appointment_datetime']));
        $suggestions = [];
        // Primary suggestion
        $primary = $this->appointmentModel->findNextAvailableSlot($date, $preferredTime, 15, 180, $appointment['dentist_id'] ?? null);
        if ($primary) $suggestions[] = $primary;
        // Additional small lookahead suggestions (increment by 5 minutes up to 3 alternatives)
        $ts = strtotime($date . ' ' . $preferredTime . ':00');
        for ($i = 1; $i <= 3 && count($suggestions) < 4; $i++) {
            $cand = date('H:i', $ts + ($i * 5 * 60));
            if (!in_array($cand, $suggestions)) {
                $conflict = $this->appointmentModel->isTimeConflictingWithGrace($date . ' ' . $cand . ':00', 15, $appointment['dentist_id'] ?? null, $appointmentId);
                if (!$conflict) $suggestions[] = $cand;
            }
        }

    if (!$suggestions) return $this->response->setJSON(['success' => false, 'message' => 'No available slot found to reschedule']);

        // If confirm flag set, perform update to the first suggested (or a client-chosen time via payload)
        if ($confirm) {
            // Support either chosenTime or a chosenDate+chosenTime pair sent from the client
            if (!empty($payload['chosenDate']) && !empty($payload['chosenTime'])) {
                $chosen = trim($payload['chosenDate'] . ' ' . $payload['chosenTime']);
            } else {
                $chosen = isset($payload['chosenTime']) ? trim($payload['chosenTime']) : trim($suggestions[0]);
            }

            if (empty($chosen)) {
                return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'No chosen time provided']);
            }

            // Support either time-only (HH:MM) or full datetime/ISO string from client.
            $isTimeOnly = preg_match('/^\d{2}:\d{2}$/', $chosen);
            $newDatetime = null;
            $chosenTimeOnly = null; // HH:MM used for lookup/advice

            if ($isTimeOnly) {
                list($hh, $mm) = explode(':', $chosen);
                $hh = (int)$hh; $mm = (int)$mm;
                if ($hh < 0 || $hh > 23 || $mm < 0 || $mm > 59) {
                    return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid time values. Hour must be 00-23 and minute 00-59']);
                }
                $chosenTimeOnly = sprintf('%02d:%02d', $hh, $mm);
                $newDatetime = $date . ' ' . sprintf('%02d:%02d:00', $hh, $mm);
            } else {
                // Try to parse full datetime string
                $ts = strtotime($chosen);
                if ($ts === false) {
                    return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid time or datetime format']);
                }
                $newDate = date('Y-m-d', $ts);
                $newTime = date('H:i:s', $ts);
                $newDatetime = $newDate . ' ' . $newTime;
                $chosenTimeOnly = date('H:i', $ts);
            }

            // Business hours validation: check branch operating hours if available
            // Elevated users (staff/admin/dentist/doctor) are allowed to bypass branch hours.
            $branchId = $appointment['branch_id'] ?? null;
            $branchOk = true;
            $actor = Auth::getCurrentUser();
            $isElevated = $actor && in_array($actor['user_type'] ?? '', ['dentist', 'doctor', 'admin', 'staff']);
            if ($branchId && !$isElevated) {
                try {
                    $branchModel = new BranchModel();
                    $branch = $branchModel->find($branchId);
                    if (!empty($branch['operating_hours'])) {
                        $oh = json_decode($branch['operating_hours'], true);
                        $weekday = strtolower(date('l', strtotime($date)));
                        if (isset($oh[$weekday]) && !empty($oh[$weekday]['enabled'])) {
                            $open = $oh[$weekday]['open'];
                            $close = $oh[$weekday]['close'];
                            // normalize to HH:MM
                            $openTs = strtotime($date . ' ' . $open . ':00');
                            $closeTs = strtotime($date . ' ' . $close . ':00');
                            $chosenTs = strtotime($newDatetime);
                            if ($chosenTs < $openTs || $chosenTs > $closeTs) {
                                return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Chosen time is outside branch operating hours']);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Failed to validate branch hours: ' . $e->getMessage());
                }
            }

            // Conflict check: ensure the chosen time doesn't conflict with other appointments (uses model helper)
            $dentistId = $appointment['dentist_id'] ?? null;
            $conflict = $this->appointmentModel->isTimeConflictingWithGrace($newDatetime, 15, $dentistId, $appointmentId);
            if ($conflict) {
                // Find an adjusted next available slot and return it to the client for auto-adjust
                $adjusted = $this->appointmentModel->findNextAvailableSlot($date, $chosenTimeOnly ?? $chosen, 15, 180, $dentistId);
                return $this->response->setStatusCode(409)->setJSON(['success' => false, 'message' => 'Chosen time conflicts with existing appointments', 'code' => 'conflict', 'adjusted_time' => $adjusted]);
            }
            $db = \Config\Database::connect();
            $db->transStart();
            try {
                // Preserve existing appointment fields that are not part of the reschedule form
                $originalAppointment = $appointment; // snapshot before changes
                // Prepare update payload by merging original row with new values
                $updateData = $originalAppointment;
                // Remove fields that should not be mass-updated
                foreach (['id','created_at'] as $rm) { if (array_key_exists($rm, $updateData)) unset($updateData[$rm]); }
                $updateData['appointment_datetime'] = $newDatetime;
                $updateData['status'] = 'scheduled';
                $updateData['updated_at'] = date('Y-m-d H:i:s');

                $updated = $this->appointmentModel->update($appointmentId, $updateData);

                if (!$updated) {
                    $db->transRollback();
                    return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Failed to update appointment']);
                }

                // Reload the full, updated appointment row to return to client and include in notifications/audit
                $updatedAppointment = $this->appointmentModel->find($appointmentId);

                // Remove the patient_checkins row if present so they are no longer in the waiting queue
                try {
                    $actor = Auth::getCurrentUser();
                    $db->table('patient_checkins')->where('appointment_id', $appointmentId)->update([
                        'removed_at' => date('Y-m-d H:i:s'),
                        'removed_by' => $actor['id'] ?? null,
                        'removed_reason' => 'rescheduled by staff'
                    ]);
                } catch (\Exception $e) {
                    // Non-fatal but worth logging
                    log_message('error', 'Failed to soft-remove patient_checkins for appointment ' . $appointmentId . ': ' . $e->getMessage());
                }

                // Persist branch notification payloads for downstream workers or manual review
                try {
                    if (class_exists('\App\\Models\\BranchNotificationModel')) {
                        $bnModel = new \App\Models\BranchNotificationModel();
                        $payload = json_encode([
                            'type' => 'reschedule',
                            'appointment_id' => $appointmentId,
                            'before' => $appointment, // full original row
                            'after' => $updatedAppointment ?? null, // full updated row
                            'actor' => Auth::getCurrentUser()['name'] ?? null
                        ]);
                        $bnModel->insert([
                            'branch_id' => $appointment['branch_id'] ?? null,
                            'appointment_id' => $appointmentId,
                            'payload' => $payload,
                            'sent' => 0,
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Failed to persist branch notification: ' . $e->getMessage());
                }

                // Insert an audit log
                try {
                    $audit = new AuditModel();
                    $actor = Auth::getCurrentUser();
                    $audit->insert([
                        'actor_id' => $actor['id'] ?? null,
                        'actor_name' => $actor['name'] ?? null,
                        'role_id' => $actor['user_type'] ?? null,
                        'action' => 'reschedule',
                        'changes' => json_encode(['appointment_id' => $appointmentId, 'before' => $appointment, 'after' => $updatedAppointment ?? null]),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                } catch (\Exception $e) {
                    log_message('error', 'Failed to write audit log for reschedule: ' . $e->getMessage());
                }

                $db->transComplete();

                if ($db->transStatus() === false) {
                    return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Transaction failed while rescheduling']);
                }

                // Build basic messages for client display (and for notification workers)
                $oldPretty = date('Y-m-d H:i:s', strtotime($appointment['appointment_datetime']));
                $newPretty = date('Y-m-d H:i:s', strtotime($newDatetime));
                $grace = 15;
                $patient_message = 'Your appointment has been rescheduled from ' . $oldPretty . ' to ' . $newPretty . '. Please arrive on time. A ' . $grace . '-minute grace period still applies.';
                $staff_message = 'Appointment rescheduled for ' . ($appointment['patient_name'] ?? 'patient') . '. Old: ' . $oldPretty . ', New: ' . $newPretty . '. Please update patient flow and treatment queue.';
                $admin_message = 'System log: Appointment ID ' . $appointmentId . ' was rescheduled. Old time: ' . $oldPretty . ', New time: ' . $newPretty . '. Changed by ' . ($actor['name'] ?? 'system') . '.';

                // Return both the raw chosen time (HH:MM when possible) and a server-normalized datetime
                $resp = [
                    'success' => true,
                    'newTime' => $chosenTimeOnly ?? $chosen,
                    'newDatetime' => $newDatetime,
                    'newTimeFormatted' => date('g:i A', strtotime($newDatetime)),
                    'message' => 'Appointment rescheduled to ' . ($chosenTimeOnly ?? $chosen),
                    'removed_from_queue' => true,
                    'patient_message' => $patient_message,
                    'staff_message' => $staff_message,
                    'admin_message' => $admin_message,
                    'appointment' => $updatedAppointment // full updated row
                ];
                return $this->response->setJSON($resp);
            } catch (\Exception $e) {
                $db->transRollback();
                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
        }

        // Otherwise return suggestions (no DB write)
        // Include original time so client can show system prompt
        return $this->response->setJSON(['success' => true, 'suggestions' => $suggestions, 'old_time' => $appointment['appointment_datetime'], 'message' => 'Suggested slots computed']);
    }

    /**
     * Reinstate a previously rescheduled (soft-removed) checkin or create a new checkin
     * Expects AJAX POST with { appointmentId: int }
     */
    public function recheckinAppointment()
    {
        $user = Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['dentist', 'doctor', 'admin', 'staff'])) {
            if ($this->request->isAJAX()) return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Unauthorized']);
            return redirect()->to('/login');
        }

        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        $appointmentId = isset($payload['appointmentId']) ? (int)$payload['appointmentId'] : null;
        if (!$appointmentId) return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Missing appointmentId']);

        $appointment = $this->appointmentModel->find($appointmentId);
        if (!$appointment) return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Appointment not found']);

        $db = \Config\Database::connect();
        $db->transStart();
        try {
            // Try to find existing patient_checkins row for this appointment (prefer latest)
            $pc = $db->table('patient_checkins')->where('appointment_id', $appointmentId)->orderBy('id', 'DESC')->limit(1)->get()->getRowArray();

            $now = date('Y-m-d H:i:s');
            if ($pc) {
                // If it was soft-removed, clear removed_at and update checked_in_at/by
                $update = [
                    'checked_in_at' => $now,
                    'checked_in_by' => $user['id'] ?? null,
                    'updated_at' => $now,
                ];
                if (!empty($pc['removed_at'])) {
                    $update['removed_at'] = null;
                    $update['removed_by'] = null;
                    $update['removed_reason'] = null;
                }
                $db->table('patient_checkins')->where('id', $pc['id'])->update($update);
            } else {
                // No prior checkin row — create one
                $db->table('patient_checkins')->insert([
                    'appointment_id' => $appointmentId,
                    'checked_in_at' => $now,
                    'checked_in_by' => $user['id'] ?? null,
                    'self_checkin' => 0,
                    'checkin_method' => 'staff',
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            }

            // Update appointment status to checked_in and set checked_in_at/by
            $this->appointmentModel->update($appointmentId, [
                'status' => 'checked_in',
                'checked_in_at' => $now,
                'checked_in_by' => $user['id'] ?? null,
                'updated_at' => $now
            ]);

            // Audit
            try {
                $audit = new AuditModel();
                $audit->insert([
                    'actor_id' => $user['id'] ?? null,
                    'actor_name' => $user['name'] ?? null,
                    'role_id' => $user['user_type'] ?? null,
                    'action' => 'recheckin',
                    'changes' => json_encode(['appointment_id' => $appointmentId, 'action' => 'recheckin']),
                    'created_at' => $now
                ]);
            } catch (\Exception $e) {
                log_message('error', 'Failed to write audit log for recheckin: ' . $e->getMessage());
            }

            $db->transComplete();
            if ($db->transStatus() === false) {
                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Transaction failed while re-checking in']);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Appointment reinstated to queue']);
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Exception in recheckinAppointment: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
