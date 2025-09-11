<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseAdminController;
use App\Services\ProcedureService;
use App\Traits\AdminAuthTrait;

class ProcedureController extends BaseAdminController
{
    use AdminAuthTrait;
    
    protected $procedureService;
    
    public function __construct()
    {
        parent::__construct();
        $this->procedureService = new ProcedureService();
    }
    
    /**
     * Get authenticated user for web requests
     */
    protected function getAuthenticatedUser()
    {
        return $this->checkAdminAuth();
    }
    
    /**
     * Get authenticated user for API requests
     */
    protected function getAuthenticatedUserApi()
    {
        return $this->checkAdminAuthApi();
    }

    /**
     * Display procedures list
     */
    public function index()
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $page = $this->request->getGet('page') ?? 1;
        $search = $this->request->getGet('search') ?? null;
        $limit = $this->request->getGet('limit') ?? 10;

        $data = $this->procedureService->getAllProcedures($page, $limit, $search);

        return view('admin/procedures/index', [
            'user' => $user,
            'procedures' => $data['procedures'],
            'pagination' => [
                'total' => $data['total'],
                'pages' => $data['pages'],
                'current_page' => $data['current_page'],
                'limit' => $limit
            ],
            'search' => $search
        ]);
    }

    /**
     * Show create procedure form
     */
    public function create()
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $patients = $this->procedureService->getPatients();

        return view('admin/procedures/create', [
            'user' => $user,
            'patients' => $patients
        ]);
    }

    /**
     * Store new procedure
     */
    public function store()
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'procedure_name' => $this->request->getPost('procedure_name'),
            'description' => $this->request->getPost('description'),
            'user_id' => $this->request->getPost('user_id'),
            'category' => $this->request->getPost('category'),
            'fee' => $this->request->getPost('fee'),
            'treatment_area' => $this->request->getPost('treatment_area'),
            'procedure_date' => $this->request->getPost('procedure_date'),
            'status' => $this->request->getPost('status') ?? 'scheduled'
        ];

        $result = $this->procedureService->createProcedure($data);

        if ($result['success']) {
            session()->setFlashdata('success', $result['message']);
            return redirect()->to('/admin/procedures');
        } else {
            session()->setFlashdata('error', $result['message']);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Show procedure details
     */
    public function show($id)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $result = $this->procedureService->getProcedureDetails($id);
        
        if (!$result['success']) {
            session()->setFlashdata('error', $result['message']);
            return redirect()->to('/admin/procedures');
        }

        return view('admin/procedures/view_edit', [
            'user' => $user,
            'procedure' => $result['data'],
            'validation' => \Config\Services::validation()
        ]);
    }

    /**
     * Show edit procedure form
     */
    public function edit($id)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $result = $this->procedureService->getProcedureDetails($id);
        if (!$result['success']) {
            session()->setFlashdata('error', $result['message']);
            return redirect()->to('/admin/procedures');
        }
        return view('admin/procedures/view_edit', [
            'user' => $user,
            'procedure' => $result['data'],
            'validation' => \Config\Services::validation()
        ]);
    }

    /**
     * Update procedure
     */
    public function update($id)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $isAdmin = ($user['user_type'] === 'admin');
        $rules = [
            'procedure_date' => 'required|valid_date',
            'treatment_area' => 'required',
            'status' => 'required'
        ];
        if ($isAdmin) {
            $rules['title'] = 'required|max_length[100]';
            $rules['category'] = 'required';
            $rules['fee'] = 'numeric';
        }

        if (!$this->validate($rules)) {
            $result = $this->procedureService->getProcedureDetails($id);
            $patients = $this->procedureService->getPatients();
            return view('admin/procedures/view_edit', [
                'user' => $user,
                'procedure' => $result['data'],
                'validation' => $this->validator
            ]);
        }

        $data = [
            'procedure_date' => $this->request->getPost('procedure_date'),
            'treatment_area' => $this->request->getPost('treatment_area'),
            'status' => $this->request->getPost('status') ?? 'pending'
        ];
        if ($isAdmin) {
            $data['title'] = $this->request->getPost('title');
            $data['category'] = $this->request->getPost('category');
            $data['fee'] = $this->request->getPost('fee');
        }

        $result = $this->procedureService->updateProcedure($id, $data);

        if ($result['success']) {
            session()->setFlashdata('success', $result['message']);
            return redirect()->to('/admin/procedures');
        } else {
            session()->setFlashdata('error', $result['message']);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Delete procedure
     */
    public function delete($id)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $result = $this->procedureService->deleteProcedure($id);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON($result);
        }

        if ($result['success']) {
            session()->setFlashdata('success', $result['message']);
        } else {
            session()->setFlashdata('error', $result['message']);
        }

        return redirect()->to('/admin/procedures');
    }
}
