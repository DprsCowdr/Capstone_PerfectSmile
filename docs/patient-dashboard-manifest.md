# Patient Dashboard Manifest

Purpose: inventory of files used by the patient dashboard/calendar, duplication notes, and recommended cleanup actions.

## 1) Controllers

- `app/Controllers/Patient.php`
  - Renders patient pages: dashboard, calendar, book_appointment, appointments, records, profile, billing, messages, forms, prescriptions, treatment_plan
- `app/Controllers/Api/PatientAppointments.php`
  - API endpoints: `/api/patient/appointments` and `/api/patient/check-conflicts`
- `app/Controllers/Guest.php` (submitAppointment wrapper)

## 2) Views (patient pages)

- `app/Views/patient/dashboard.php`
- `app/Views/patient/calendar.php` (legacy patient calendar)
- `app/Views/patient/book_appointment.php`
  - NOTE: now includes `procedure_duration` select (15/30/45/60)
- `app/Views/patient/appointments.php`
- `app/Views/patient/records.php`
- `app/Views/patient/profile.php`
- `app/Views/patient/billing.php`
- `app/Views/patient/messages.php`
- `app/Views/patient/forms.php`
- `app/Views/patient/prescriptions.php`
- `app/Views/patient/treatment_plan.php`

## 3) Calendar templates / partials

- `app/Views/templates/calendar/core.php`
- `app/Views/templates/calendar/patient.php`
- `app/Views/templates/calendar/header.php`
- `app/Views/templates/calendar/day_view.php`
- `app/Views/templates/calendar/week_view.php`
- `app/Views/templates/calendar/month_view.php`
- `app/Views/templates/calendar/panels.php`
- `app/Views/templates/calendar/appointments_toggle.php`
- `app/Views/templates/calendar/styles.php`
- `app/Views/templates/calendar/scripts.php`
  - NOTE: admin/staff inline functions (edit/delete/approve/decline) were moved to `public/js/calendar-admin.js` and `scripts.php` now delegates to `window.calendarAdmin` for admin actions and conditionally includes the external script for non-patients.

## 4) Public JS (calendar + patient-specific)

- `public/js/calendar-core.js` (shared helpers)
- `public/js/calendar-patient.js` (patient-specific handlers)
- `public/js/calendar-admin.js` (admin/staff handlers; consolidated from inline templates)
- `public/js/calendar-day-view-modal.js`
- `public/js/patientsTable.js`
- `public/js/records-management.js`
- `public/js/modules/*` (modal-controller.js, data-loader.js, etc.)

### Duplication notes

- Legacy/duplicate files previously observed under `public/js/Patient-calendar/` were not present as a directory; ensure no stale copies remain in the repo.
- Some older references still include `public/js/patient-calendar.js` in patient views; consolidate references to `public/js/calendar-patient.js` and remove legacy files.

## 5) Models the patient dashboard depends on

- `app/Models/AppointmentModel.php`
- `app/Models/PatientModel.php`
- `app/Models/PatientMedicalHistoryModel.php`
- `app/Models/BranchModel.php`
- `app/Models/PaymentModel.php`
- `app/Models/DentalRecordModel.php`
- `app/Models/ProcedureModel.php`
- `app/Models/UserModel.php`

## 6) Routes and config

- `app/Config/Routes.php` (patient routes + `api/patient` group)
- `app/Config/App.php` (feature flag: `enableCalendarRefactor`)
- `app/Config/Database.php` (DB settings for integration tests)

## 7) Tests

- `tests/unit/CalendarSlotTest.php`
- `tests/integration/PatientBookingTest.php`

## Recommended cleanup steps (priority order)

1) Consolidate JS imports in views:
   - Ensure patient pages include only `public/js/calendar-core.js` and `public/js/calendar-patient.js`.
   - Ensure admin/staff pages include `public/js/calendar-core.js` and `public/js/calendar-admin.js`.
2) Verify admin flows after removing inline admin functions from `templates/calendar/scripts.php` (done) and test admin actions.
3) Remove any duplicate/legacy files under `public/js/Patient-calendar/` if they exist.
4) Add unit tests for procedure duration and conflict detection (edge cases for boundary overlaps).
5) Run integration tests against MySQL and add seed data for consistent results.
6) After verification, remove legacy `app/Views/patient/calendar.php` if `templates/calendar/patient.php` fully replaces it.

If you want, I can now:

- Run a quick build (`npm run build`) and Node check for JS syntax.
- Update views to point to consolidated `public/js/calendar-patient.js` where legacy references remain.
- Remove any detected duplicate JS files and run the test/build.

Generated: 2025-08-29
