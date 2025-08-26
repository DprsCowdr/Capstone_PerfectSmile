# Check-in / Treatment Queue / Checkup — Quick Fix

Date: 2025-08-26

Purpose: minimal, step-by-step fixes for check-in → treatment → checkup failures.

## Symptoms

- Check-in button: duplicate confirms or no action
- Send-to-Treatment: UI shows in-treatment but no DB row
- Missing ongoing sessions in treatment queue

## Root causes

- View: inline/global JS caused double confirms and inconsistent AJAX
- Controllers: direct inserts used fields outside model `allowedFields`
- TreatmentQueue: unconditional dentist filter hid records
- Endpoints: non-JSON responses broke fetch-based clients

## Fix steps (exact files)

1. `app/Views/checkin/dashboard.php`
   - Add `class="checkin-form"` and `class="send-to-treatment"` to forms
   - Add `data-patient-name="..."` to forms/buttons
   - Remove inline `onclick` confirms
   - Add scoped JS: confirm(name) → fetch(form.action, { method:'POST', body:new FormData(form), headers:{'X-Requested-With':'XMLHttpRequest'}, credentials:'same-origin' })
   - Show toast on JSON { success:true/false }; fallback to `form.submit()` on non-JSON

2. `app/Controllers/PatientCheckin.php`
   - Replace raw insert with `PatientCheckinModel::checkInPatient($appointmentId, $userId, ...)`
   - Return JSON on AJAX: `{ success:true }` or `{ success:false, error:'...' }` with proper HTTP status
   - Log model validation errors when insert fails

3. `app/Controllers/TreatmentQueue.php`
   - Replace raw insert with `TreatmentSessionModel::startSession($appointmentId, $callerId, $dentistId, ...)`
   - Update `appointments.status` -> 'ongoing' inside DB transaction
   - Return JSON on AJAX success/error; log model errors
   - Apply `dentist_id` filter only when current user role is dentist

4. `app/Models/PatientCheckinModel.php` and `app/Models/TreatmentSessionModel.php`
   - Ensure `allowedFields` include all columns helpers write
   - Implement/confirm `checkInPatient()` and `startSession()` helpers
   - Surface validation errors via `$model->errors()` or logging

## Verify (minimal)

1. From UI: POST `/checkin/process/{id}` → JSON `{ success:true }`, DB `patient_checkins` created, `appointments.status` = 'checked_in'
2. From UI: POST `/queue/call/{id}` → JSON `{ success:true }`, DB `treatment_sessions` created, `appointments.status` = 'ongoing'
3. Treatment queue (admin/staff) shows ongoing sessions

## Logs to check

- `PatientCheckin insert failed. Model errors:`
- `TreatmentSession insert failed. Model errors:`
- `Check-in failed:`
- `Exception calling patient:`

## Follow-ups

- Add PHPUnit test for check-in → send-to-treatment (happy path + validation fail)
- Add small API response trait for consistent JSON

Document: `docs/checkin_treatment_checkup_fixes.md`
