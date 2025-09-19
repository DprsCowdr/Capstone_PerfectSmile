<?php
namespace App\Controllers;

use App\Controllers\Auth;
use App\Models\AvailabilityModel;

class Availability extends BaseController
{
    /**
     * Return availability blocks between start and end (query params or POST)
     * Accepts: start (Y-m-d or Y-m-d H:i:s), end, dentist_id (optional)
     */
    public function events()
    {
        if (!Auth::isAuthenticated()) {
            return $this->response->setStatusCode(401)->setJSON(['success'=>false,'message'=>'Unauthorized']);
        }

        $start = $this->request->getGet('start') ?? $this->request->getPost('start');
        $end = $this->request->getGet('end') ?? $this->request->getPost('end');
        $dentistId = $this->request->getGet('dentist_id') ?? $this->request->getPost('dentist_id');

        if (empty($start) || empty($end)) {
            return $this->response->setStatusCode(400)->setJSON(['success'=>false,'message'=>'Start and end are required']);
        }

        $model = new AvailabilityModel();
        $blocks = $model->getBlocksBetween($start, $end, $dentistId ?: null);

        // Map to frontend-friendly events
        // Ensure datetimes are returned in ISO8601 with Manila timezone offset (+08:00)
        $events = array_map(function($b){
            // Convert 'YYYY-MM-DD HH:MM:SS' to 'YYYY-MM-DDTHH:MM:SS+08:00'
            $fmt = function($s){
                if (empty($s)) return $s;
                // If already contains T and timezone info, return as-is
                if (strpos($s, 'T') !== false && (strpos($s, '+') !== false || strpos($s, '-') !== false)) return $s;
                $s = str_replace(' ', 'T', $s);
                // Append Manila offset if missing
                if (!preg_match('/[+-]\d\d:\d\d$/', $s)) {
                    $s = $s . '+08:00';
                }
                return $s;
            };

            return [
                'id' => $b['id'],
                'title' => ucfirst(str_replace('_',' ',$b['type'])),
                'type' => $b['type'],
                'start' => $fmt($b['start_datetime']),
                'end' => $fmt($b['end_datetime']),
                'allDay' => false,
                'user_id' => $b['user_id'],
                'notes' => $b['notes'] ?? ''
            ];
        }, $blocks);

        // Minimal logging: record parameter summary at info level only when in debug env
        if (ENVIRONMENT === 'development' || ENVIRONMENT === 'testing') {
            try {
                log_message('info', 'Availability::events - params: ' . json_encode(['start'=>$start, 'end'=>$end, 'dentist_id'=>$dentistId]));
            } catch (\Exception $e) { /* swallow */ }
        }

        return $this->response->setJSON(['success'=>true,'events'=>$events]);
    }

    /**
     * Create an availability block (dentist or admin/staff on behalf)
     * POST: user_id (dentist), type, start, end, notes
     */
    public function create()
    {
        // Add debug logging
        log_message('info', 'Availability::create() called');
        log_message('info', 'POST data: ' . json_encode($this->request->getPost()));
        
        if (!Auth::isAuthenticated()) {
            log_message('error', 'Availability::create() - Not authenticated');
            return $this->response->setStatusCode(401)->setJSON(['success'=>false,'message'=>'Unauthorized']);
        }

        $user = Auth::getCurrentUser();
        log_message('info', 'Current user: ' . json_encode($user));

        $post = $this->request->getPost();
        $targetUser = $post['user_id'] ?? $user['id'];
        $type = $post['type'] ?? null;
        $start = $post['start'] ?? null;
        $end = $post['end'] ?? null;
        $notes = $post['notes'] ?? null;

        log_message('info', 'Parsed fields - targetUser: ' . $targetUser . ', type: ' . $type . ', start: ' . $start . ', end: ' . $end);

        // Only allow dentists to create for themselves, or admins/staff to create for others
        if ($user['user_type'] !== 'dentist' && !in_array($user['user_type'], ['admin','staff'])) {
            log_message('error', 'Forbidden - user type: ' . $user['user_type']);
            return $this->response->setStatusCode(403)->setJSON(['success'=>false,'message'=>'Forbidden']);
        }
        if ($user['user_type'] === 'dentist' && $targetUser != $user['id']) {
            log_message('error', 'Dentist cannot create for other users');
            return $this->response->setStatusCode(403)->setJSON(['success'=>false,'message'=>'Cannot create availability for other users']);
        }

        if (empty($type) || empty($start) || empty($end)) {
            log_message('error', 'Missing required fields');
            return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'Type, start and end are required']);
        }

        try {
            $model = new AvailabilityModel();
            // Also populate time-only columns for legacy consumers
            $start_time = null;
            $end_time = null;
            if (!empty($start)) {
                // store legacy time-only fields in 24-hour short form for DB/legacy consumers
                $start_time = date('H:i', strtotime($start));
            }
            if (!empty($end)) {
                $end_time = date('H:i', strtotime($end));
            }

            // Derive day_of_week (weekday name) from start datetime for convenience
            $day_of_week = null;
            if (!empty($start)) {
                $day_of_week = date('l', strtotime($start)); // e.g., Monday
            }

            $createData = [
                'user_id' => $targetUser,
                'type' => $type,
                'start_datetime' => $start,
                'end_datetime' => $end,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'day_of_week' => $day_of_week,
                'notes' => $notes,
                'created_by' => $user['id']
            ];
            
            log_message('info', 'Calling createBlock with data: ' . json_encode($createData));
            $id = $model->createBlock($createData);
            // Some model insert implementations return insert ID or truthy — ensure we fetch the actual insert id
            $insertId = is_numeric($id) ? (int)$id : (int)$model->getInsertID();
            log_message('info', 'createBlock returned ID: ' . $id . ' (resolved insertId: ' . $insertId . ')');

            // Fetch the saved row to confirm persisted fields
            $saved = $model->find($insertId);
            log_message('info', 'Saved availability row: ' . json_encode($saved));

            // Detect overlapping confirmed/approved appointments for this dentist
            $db = \Config\Database::connect();
                        // Determine overlapping appointments using linked services durations when available.
                        // Subquery aggregates total_service_minutes per appointment by summing
                        // COALESCE(services.duration_max_minutes, services.duration_minutes, 0).
                        // If an appointment has no linked services, we only consider it if
                        // appointment.procedure_duration IS NOT NULL (explicitly set).

                        $sql = "SELECT a.id, a.appointment_datetime, u.name as patient_name, u.email as patient_email, u.phone as patient_phone
                                        FROM appointments a
                                        JOIN user u ON u.id = a.user_id
                                        LEFT JOIN (\n	sELECT appointment_id, SUM(COALESCE(s.duration_max_minutes, s.duration_minutes, 0)) AS total_service_minutes\n	FROM appointment_services aps\n	JOIN services s ON s.id = aps.service_id\n	GROUP BY appointment_id\n) svc ON svc.appointment_id = a.id
                                        WHERE a.dentist_id = ?
                                            AND a.appointment_datetime < ?
                                            AND (
                                                    (svc.total_service_minutes IS NOT NULL AND DATE_ADD(a.appointment_datetime, INTERVAL svc.total_service_minutes MINUTE) > ?)
                                                    OR (a.procedure_duration IS NOT NULL AND DATE_ADD(a.appointment_datetime, INTERVAL a.procedure_duration MINUTE) > ?)
                                            )
                                            AND a.status IN ('confirmed', 'checked_in', 'ongoing')
                                            AND a.approval_status IN ('approved', 'auto_approved')";

                        $conflicts = $db->query($sql, [(int)$targetUser, $end, $start, $start])->getResultArray();
            log_message('info', 'Found ' . count($conflicts) . ' conflicting appointments');

            return $this->response->setJSON(['success'=>true,'id'=>$insertId,'message'=>'Availability block created','conflicts'=>$conflicts,'record'=>$saved]);
        } catch (\Exception $e) {
            log_message('error', 'Availability::create() exception: ' . $e->getMessage());
            log_message('error', 'Exception trace: ' . $e->getTraceAsString());
            return $this->response->setStatusCode(500)->setJSON(['success'=>false,'message'=>'Failed: '.$e->getMessage()]);
        }
    }

    /**
     * Create or update recurring working hours (weekly)
     * POST: user_id, day_of_week (e.g. Monday), start_time (HH:MM), end_time (HH:MM), notes
     */
    public function createRecurring()
    {
        // Recurring working hours endpoint has been deprecated.
        // Historically this endpoint generated explicit non-recurring working_hours
        // entries (materializing recurring schedules). That behaviour has been removed
        // in favor of only storing ad-hoc availability. Keep the route for now but
        // return a 410 Gone to indicate deprecation.
        return $this->response->setStatusCode(410)->setJSON([
            'success' => false,
            'message' => 'Recurring working hours are no longer supported. Please create individual availability blocks instead.'
        ]);
    }

    /**
     * List availability records for current user
     * GET: Returns JSON list of availability records
     */
    public function list()
    {
        if (!Auth::isAuthenticated()) {
            return $this->response->setStatusCode(401)->setJSON(['success'=>false,'message'=>'Unauthorized']);
        }

        $user = Auth::getCurrentUser();
        
        try {
            $model = new AvailabilityModel();
            
            // Get all availability records for this user
            $records = $model->where('user_id', $user['id'])
                            ->orderBy('created_at', 'DESC')
                            ->findAll();
            
            return $this->response->setJSON([
                'success' => true,
                'availability' => $records
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Availability::list() exception: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success'=>false,'message'=>'Failed to load availability']);
        }
    }

    /**
     * Delete an availability row (recurring or ad-hoc)
     * POST: id
     */
    public function delete()
    {
        if (!Auth::isAuthenticated()) return $this->response->setStatusCode(401)->setJSON(['success'=>false,'message'=>'Unauthorized']);
        $user = Auth::getCurrentUser();
        $id = $this->request->getPost('id');
        if (!$id) return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'id required']);

        $model = new AvailabilityModel();
        $row = $model->find($id);
        if (!$row) return $this->response->setStatusCode(404)->setJSON(['success'=>false,'message'=>'Not found']);

        // permission: dentists can delete their own; admin/staff can delete any
        if ($user['user_type'] === 'dentist' && $row['user_id'] != $user['id']) return $this->response->setStatusCode(403)->setJSON(['success'=>false,'message'=>'Forbidden']);

        try {
            $model->delete($id);
            return $this->response->setJSON(['success'=>true,'message'=>'Deleted']);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['success'=>false,'message'=>'Failed: '.$e->getMessage()]);
        }
    }

    /**
     * Update an existing availability record
     * POST: id, type, start, end, notes
     */
    public function update()
    {
        if (!Auth::isAuthenticated()) return $this->response->setStatusCode(401)->setJSON(['success'=>false,'message'=>'Unauthorized']);
        $user = Auth::getCurrentUser();
        $post = $this->request->getPost();
        $id = $post['id'] ?? null;
        $type = $post['type'] ?? null;
        $start = $post['start'] ?? null;
        $end = $post['end'] ?? null;
        $notes = $post['notes'] ?? null;

        if (!$id) return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'id required']);

        $model = new AvailabilityModel();
        $row = $model->find($id);
        if (!$row) return $this->response->setStatusCode(404)->setJSON(['success'=>false,'message'=>'Not found']);

        // permission check
        if ($user['user_type'] === 'dentist' && $row['user_id'] != $user['id']) return $this->response->setStatusCode(403)->setJSON(['success'=>false,'message'=>'Forbidden']);

        // validate required fields
        if (empty($type) || empty($start) || empty($end)) return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'Type, start and end are required']);

        try {
            // Populate time-only fields for legacy columns
            // keep legacy DB time-only columns in 24-hour short form (HH:MM)
            $start_time = !empty($start) ? date('H:i', strtotime($start)) : null;
            $end_time = !empty($end) ? date('H:i', strtotime($end)) : null;
            $day_of_week = null;
            if (!empty($start)) {
                $day_of_week = date('l', strtotime($start));
            }

            $update = [
                'type' => $type,
                'start_datetime' => $start,
                'end_datetime' => $end,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'day_of_week' => $day_of_week,
                'notes' => $notes,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $model->update((int)$id, $update);

            // Fetch updated record to confirm persisted fields
            $updatedRow = $model->find((int)$id);
            log_message('info', 'Updated availability row: ' . json_encode($updatedRow));

            // Dispatch availability change event (used by frontends)
            return $this->response->setJSON(['success'=>true,'message'=>'Updated','record'=>$updatedRow]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['success'=>false,'message'=>'Failed: '.$e->getMessage()]);
        }
    }

    /**
     * List availability (recurring + ad-hoc) for a dentist
     * GET/POST: user_id
     */
    public function listForUser()
    {
        if (!Auth::isAuthenticated()) return $this->response->setStatusCode(401)->setJSON(['success'=>false,'message'=>'Unauthorized']);
        $user = Auth::getCurrentUser();
        $targetUser = $this->request->getGet('user_id') ?? $this->request->getPost('user_id') ?? $user['id'];

        $model = new AvailabilityModel();
        $db = $model->db;
        $builder = $db->table('availability')->where('user_id', (int)$targetUser);
        $rows = $builder->orderBy('is_recurring','DESC')->orderBy('start_datetime','ASC')->get()->getResultArray();
        return $this->response->setJSON(['success'=>true,'availability'=> $rows]);
    }

    /**
     * Mark conflicting appointments as needing reschedule and optionally notify
     * POST: appointment_ids[]
     */
    public function markConflicts()
    {
        if (!Auth::isAuthenticated()) return $this->response->setStatusCode(401)->setJSON(['success'=>false,'message'=>'Unauthorized']);
        $user = Auth::getCurrentUser();
        $ids = $this->request->getPost('appointment_ids');
        if (!$ids || !is_array($ids)) return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'appointment_ids[] required']);

    $db = \Config\Database::connect();
        $apptTb = $db->table('appointments');
        $notTb = $db->table('notifications');
        $updated = [];
        foreach ($ids as $id) {
            try {
                $apptTb->where('id',(int)$id)->update(['needs_reschedule' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
                $updated[] = (int)$id;
                // insert a simple notification if notifications table exists
                if ($db->tableExists('notifications')) {
                    $notTb->insert(['user_id' => 0, 'related_appointment_id' => (int)$id, 'message' => 'Appointment flagged for reschedule by clinic', 'created_at' => date('Y-m-d H:i:s')]);
                }
                // attempt to trigger existing appointment notification flow if available
                try {
                    if (class_exists('\App\Services\AppointmentService')) {
                        $svc = new \App\Services\AppointmentService();
                        // load appointment details
                        $appointment = $apptTb->where('id',(int)$id)->get()->getRowArray();
                        if ($appointment) {
                            // send notification with 'reschedule_requested' action if method exists
                            if (method_exists($svc, 'sendAppointmentNotification')) {
                                try { $svc->sendAppointmentNotification($appointment, 'reschedule_requested'); } catch(\Exception $e) { /* swallow */ }
                            }
                        }
                    }
                } catch(\Exception $ee) { /* swallow */ }
            } catch (\Exception $e) {
                // skip failing ids
            }
        }
        return $this->response->setJSON(['success'=>true,'updated'=>$updated]);
    }
}
