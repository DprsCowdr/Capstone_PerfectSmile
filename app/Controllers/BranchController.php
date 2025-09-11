<?php
namespace App\Controllers;

use App\Models\BranchModel;
use App\Models\BranchStaffModel;
use App\Services\BranchService;
use App\Traits\AdminAuthTrait;

class BranchController extends BaseAdminController
{
    use AdminAuthTrait;

    protected $branchModel;
    protected $staffModel;
    protected $service;

    public function __construct()
    {
        parent::__construct();
        $this->branchModel = new BranchModel();
        $this->staffModel = new BranchStaffModel();
        $this->service = new BranchService();
    }

    protected function getAuthenticatedUser()
    {
        return $this->checkAdminAuth();
    }

    protected function getAuthenticatedUserApi()
    {
        return $this->checkAdminAuthApi();
    }

    public function index()
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) return $user;

        $filters = [
            'search' => $this->request->getGet('search'),
            'status' => $this->request->getGet('status'),
            'city' => $this->request->getGet('city')
        ];

        $branches = $this->service->getAll($filters);

        $content = view('branches/index', [
            'user' => $user, 
            'branches' => $branches,
            'filters' => $filters
        ]);
        return view('templates/admin_layout', [
            'title' => 'Branches - Perfect Smile',
            'content' => $content,
            'user' => $user
        ]);
    }

    public function create()
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) return $user;

        $content = view('branches/create', ['user' => $user]);
        return view('templates/admin_layout', [
            'title' => 'New Branch - Perfect Smile',
            'content' => $content,
            'user' => $user
        ]);
    }

    public function store()
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) return $user;
        // validate input
        $rules = [
            'name' => 'required|min_length[2]',
            'contact_number' => 'permit_empty|max_length[50]',
            'email' => 'permit_empty|valid_email',
            'status' => 'permit_empty|in_list[active,inactive]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

    $post = $this->request->getPost();
        // If the edit form posts individual day fields like 'monday_open', assemble operating_hours
        if (! isset($post['operating_hours'])) {
            $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
            $oh = [];
            foreach ($days as $d) {
                $enabledKey = $d . '_enabled';
                $openKey = $d . '_open';
                $closeKey = $d . '_close';

                $enabled = $this->request->getPost($enabledKey);
                // HTML checkboxes may be missing when unchecked; treat missing as '0'
                $oh[$d] = [
                    'enabled' => $enabled === null ? (isset($post[$enabledKey]) ? (bool)$post[$enabledKey] : true) : (bool)$enabled,
                    'open' => $this->request->getPost($openKey) ?? ($post[$openKey] ?? '09:00'),
                    'close' => $this->request->getPost($closeKey) ?? ($post[$closeKey] ?? '17:00'),
                ];
            }
            $post['operating_hours'] = $oh;
        }
        $data = [
            'name' => $post['name'] ?? null,
            'address' => $post['address'] ?? null,
            'contact_number' => $post['contact_number'] ?? null,
            'email' => $post['email'] ?? null,
            'status' => $post['status'] ?? 'active',
        ];
        if (isset($post['operating_hours']) && is_array($post['operating_hours'])) {
            // validate operating hours server-side
            $ohErrors = $this->validateOperatingHours($post['operating_hours']);
            if (!empty($ohErrors)) {
                return redirect()->back()->withInput()->with('errors', $ohErrors);
            }
            $data['operating_hours'] = json_encode($post['operating_hours']);
        } elseif (isset($post['operating_hours']) && is_string($post['operating_hours'])) {
            $data['operating_hours'] = $post['operating_hours'];
        }
        // Persist operating_hours (array from form) as JSON
        if (isset($post['operating_hours']) && is_array($post['operating_hours'])) {
            $data['operating_hours'] = json_encode($post['operating_hours']);
        } elseif (isset($post['operating_hours']) && is_string($post['operating_hours'])) {
            // assume JSON string
            $data['operating_hours'] = $post['operating_hours'];
        }

        $insertId = $this->service->create($data);
        if (!$insertId) return redirect()->back()->with('error', 'Failed to create branch')->withInput();

        return redirect()->to('/admin/branches')->with('success', 'Branch created');
    }

    public function show($id)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) return $user;

        $branch = $this->service->get($id);
        if (!$branch) return redirect()->back()->with('error', 'Not found');

        $staff = $this->staffModel->where('branch_id', $id)->findAll();
        
        // Get analytics data for this branch
        $analytics = [
            'appointments_today' => $this->getAppointmentsToday($id),
            'active_patients' => $this->getActivePatients($id),
            // Replace monthly_revenue placeholder with a treatment total (invoices.total_amount for current month)
            'treatment_total' => $this->getTreatmentTotal($id),
            'staff_count' => count($staff)
        ];

        // Recent activity / notifications for the branch
        $notifications = $this->getRecentActivity($id);

        $content = view('branches/show', [
            'user' => $user, 
            'branch' => $branch, 
            'staff' => $staff,
            'analytics' => $analytics,
            'notifications' => $notifications,
        ]);
        return view('templates/admin_layout', [
            'title' => 'Branch - ' . ($branch['name'] ?? $id),
            'content' => $content,
            'user' => $user
        ]);
    }
    
    private function getAppointmentsToday($branchId)
    {
        $appointmentModel = new \App\Models\AppointmentModel();
        return $appointmentModel->where('branch_id', $branchId)
                               ->where('DATE(appointment_datetime)', date('Y-m-d'))
                               ->countAllResults();
    }
    
    private function getActivePatients($branchId)
    {
        $appointmentModel = new \App\Models\AppointmentModel();
        // Use the query builder's distinct method to avoid quoting DISTINCT as a column
        return $appointmentModel->distinct()
                               ->select('user_id')
                               ->where('branch_id', $branchId)
                               ->where('appointment_datetime >=', date('Y-m-d', strtotime('-6 months')))
                               ->countAllResults();
    }
    
    private function getTreatmentTotal($branchId)
    {
        // Sum invoice total_amount for the current month for this branch
        $db = \Config\Database::connect();
        try {
            // Guard: ensure invoices table exists and has branch_id column to avoid SQL errors on older schemas
            $tables = $db->query("SHOW TABLES LIKE 'invoices'");
            if (! $tables || $tables->getNumRows() === 0) {
                return 0;
            }

            $col = $db->query("SHOW COLUMNS FROM `invoices` LIKE 'branch_id'");
            if (! $col || $col->getNumRows() === 0) {
                return 0;
            }

            $row = $db->table('invoices')
                      ->select('COALESCE(SUM(total_amount),0) as total')
                      ->where('branch_id', (int) $branchId)
                      ->where('YEAR(created_at)', date('Y'))
                      ->where('MONTH(created_at)', date('n'))
                      ->get()
                      ->getRowArray();
            return (float) ($row['total'] ?? 0);
        } catch (\Exception $e) {
            // If invoices table/fields don't exist or other DB error, fallback to 0
            log_message('error', 'Failed to compute treatment total for branch ' . $branchId . ': ' . $e->getMessage());
            return 0;
        }
    }

    // Keep existing method name for compatibility if other code calls it
    private function getMonthlyRevenue($branchId)
    {
        return $this->getTreatmentTotal($branchId);
    }

    /**
     * Return recent activity for a branch: upcoming/last appointments and staff changes.
     * This returns a small array of notification-like items consumed by the view.
     */
    private function getRecentActivity($branchId, $limit = 6)
    {
        $appointmentModel = new \App\Models\AppointmentModel();
        // Upcoming appointments for next 7 days
        $now = date('Y-m-d H:i:s');
        $upcoming = $appointmentModel->select('appointments.*, user.name as patient_name')
                                   ->join('user', 'user.id = appointments.user_id')
                                   ->where('branch_id', $branchId)
                                   ->where('appointment_datetime >=', $now)
                                   ->where('approval_status', 'approved')
                                   ->orderBy('appointment_datetime', 'ASC')
                                   ->findAll($limit);

        $items = [];
        foreach ($upcoming as $a) {
            $items[] = [
                'type' => 'appointment',
                'message' => sprintf('%s - %s', $a['patient_name'] ?? 'Patient', date('M d, g:i A', strtotime($a['appointment_datetime']))),
                'time' => date('M d', strtotime($a['appointment_datetime'])),
            ];
        }

        // If not enough items, add staff info
        if (count($items) < $limit) {
            $staffList = $this->staffModel->where('branch_id', $branchId)->findAll(3);
            foreach ($staffList as $s) {
                $items[] = [
                    'type' => 'staff',
                    'message' => ($s['user_id'] ?? 'Staff') . ' assigned',
                    'time' => 'recent'
                ];
                if (count($items) >= $limit) break;
            }
        }

        return $items;
    }

    /**
     * Validate operating_hours structure: ensures times are HH:MM and open < close when enabled.
     * Returns array of errors (field => message) or empty array on success.
     */
    private function validateOperatingHours(array $oh)
    {
        $errors = [];
        $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
        foreach ($days as $d) {
            if (!isset($oh[$d])) continue;
            $entry = $oh[$d];
            $enabled = isset($entry['enabled']) ? (bool)$entry['enabled'] : true;
            $open = $entry['open'] ?? null;
            $close = $entry['close'] ?? null;

            // If disabled, skip time checks
            if (!$enabled) continue;

            // Basic HH:MM 24-hour format check
            if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $open ?? '')) {
                $errors[$d . '_open'] = ucfirst($d) . ' open time must be in HH:MM format';
            }
            if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $close ?? '')) {
                $errors[$d . '_close'] = ucfirst($d) . ' close time must be in HH:MM format';
            }

            // Check logical order if both times valid
            if (preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $open ?? '') && preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $close ?? '')) {
                $openSeconds = strtotime($open);
                $closeSeconds = strtotime($close);
                if ($openSeconds >= $closeSeconds) {
                    $errors[$d . '_order'] = ucfirst($d) . ' open time must be before close time';
                }
            }
        }
        return $errors;
    }

    public function edit($id)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) return $user;

        $branch = $this->service->get($id);
        if (!$branch) return redirect()->back()->with('error', 'Not found');

        // Decode operating_hours JSON for the edit form
        if (!empty($branch['operating_hours']) && is_string($branch['operating_hours'])) {
            $decoded = json_decode($branch['operating_hours'], true);
            if ($decoded) {
                $branch['operating_hours'] = $decoded;
            }
        }

        $content = view('branches/edit', ['user' => $user, 'branch' => $branch]);
        return view('templates/admin_layout', [
            'title' => 'Edit Branch - ' . ($branch['name'] ?? $id),
            'content' => $content,
            'user' => $user
        ]);
    }

    public function update($id)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) return $user;
        // validate input
        $rules = [
            'name' => 'required|min_length[2]',
            'contact_number' => 'permit_empty|max_length[50]',
            'email' => 'permit_empty|valid_email',
            'status' => 'permit_empty|in_list[active,inactive]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

    $post = $this->request->getPost();
        // If the edit form posts individual day fields like 'monday_open', assemble operating_hours
        if (! isset($post['operating_hours'])) {
            $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
            $oh = [];
            foreach ($days as $d) {
                $enabledKey = $d . '_enabled';
                $openKey = $d . '_open';
                $closeKey = $d . '_close';

                $enabled = $this->request->getPost($enabledKey);
                $oh[$d] = [
                    'enabled' => $enabled === null ? (isset($post[$enabledKey]) ? (bool)$post[$enabledKey] : true) : (bool)$enabled,
                    'open' => $this->request->getPost($openKey) ?? ($post[$openKey] ?? '09:00'),
                    'close' => $this->request->getPost($closeKey) ?? ($post[$closeKey] ?? '17:00'),
                ];
            }
            $post['operating_hours'] = $oh;
        }
        $data = [
            'name' => $post['name'] ?? null,
            'address' => $post['address'] ?? null,
            'contact_number' => $post['contact_number'] ?? null,
            'email' => $post['email'] ?? null,
            'status' => $post['status'] ?? 'active',
        ];
        
        // Add operating_hours to data if it was assembled or provided
        if (isset($post['operating_hours']) && is_array($post['operating_hours'])) {
            $ohErrors = $this->validateOperatingHours($post['operating_hours']);
            if (!empty($ohErrors)) {
                return redirect()->back()->withInput()->with('errors', $ohErrors);
            }
            $data['operating_hours'] = json_encode($post['operating_hours']);
        } elseif (isset($post['operating_hours']) && is_string($post['operating_hours'])) {
            $data['operating_hours'] = $post['operating_hours'];
        }

        $ok = $this->service->update($id, $data);
        if (!$ok) return redirect()->back()->with('error', 'Failed to update')->withInput();

        return redirect()->to('/admin/branches/'.$id)->with('success', 'Branch updated');
    }

    public function delete($id)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) return $user;

        $this->service->delete($id);
    // Redirect to branches index so the user sees the list and the flash message
    return redirect()->to('/admin/branches')->with('success', 'Branch deleted');
    }
}
