# Staff Access Fix for Latest Dental Chart

## Issue Summary

Staff users could not access the "Latest Dental Chart" feature, showing "Latest record: N/A, No teeth data recorded" instead of actual patient dental chart data.

## Root Cause

The dental chart API endpoint `/admin/patient-dental-chart/{id}` and several other patient modal endpoints were restricted to admin users only via `checkAdminAuth()` authentication checks. Staff users received 401 Unauthorized responses when trying to access patient data.

## Solution

Updated authentication checks in `AdminController.php` to use `AuthService::checkAdminOrStaffAuthApi()` instead of `checkAdminAuth()` for patient-related API endpoints.

## Files Modified

- `/app/Controllers/AdminController.php`

## Changes Made

### 1. Added AuthService Import

```php
use App\Services\AuthService;
```

### 2. Updated Authentication for Patient API Endpoints

Changed from admin-only to admin-or-staff access for:

- `getPatientDentalChart($id)` - Dental chart data access
- `getPatientInfo($id)` - Patient information modal
- `getPatientDentalRecords($id)` - Dental records history
- `getPatientAppointmentsModal($id)` - Appointment history modal
- `updatePatientNotes($id)` - Patient notes updates

### 3. Authentication Pattern Change

**Before:**

```php
$auth = $this->checkAdminAuth();
if ($auth instanceof \CodeIgniter\HTTP\RedirectResponse) {
    return $this->response->setJSON(['error' => 'Unauthorized'], 401);
}
```

**After:**

```php
$auth = AuthService::checkAdminOrStaffAuthApi();
if ($auth instanceof \CodeIgniter\HTTP\RedirectResponse ||
    (is_object($auth) && method_exists($auth, 'setStatusCode'))) {
    return $this->response->setJSON(['error' => 'Unauthorized'], 401);
}
```

## Expected Results

✅ Staff users can now access "Latest Dental Chart" with actual patient data
✅ Staff users can view patient information through all modal popups
✅ Staff users can access patient dental records and appointment history
✅ Staff users can update patient notes

## Testing

1. Login as a staff user
2. Navigate to patients table
3. Click "Latest Dental Chart" button for any patient
4. Verify chart loads with actual data instead of "N/A"
5. Test other patient modal features (info, appointments, dental records)

## API Endpoints Affected

- `GET /admin/patient-dental-chart/{id}`
- `GET /admin/patient-info/{id}`
- `GET /admin/patient-dental-records/{id}`
- `GET /admin/patient-appointments/{id}`
- `POST /admin/patient-notes/{id}`

## Security Note

This change maintains appropriate access control - only admin and staff users can access patient data. Patient users and other roles are still properly restricted.
