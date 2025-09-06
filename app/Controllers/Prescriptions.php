<?php
namespace App\Controllers;

use App\Models\PrescriptionModel;
use App\Models\PrescriptionItemModel;
use App\Traits\AdminAuthTrait;

class Prescriptions extends BaseAdminController
{
    use AdminAuthTrait;
    protected $presModel;
    protected $itemModel;

    public function __construct()
    {
        parent::__construct();
        $this->presModel = new PrescriptionModel();
        $this->itemModel = new PrescriptionItemModel();
    }

    // AUTH: implement abstract methods from BaseAdminController
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

        $prescriptions = $this->presModel->orderBy('issue_date','DESC')->findAll();
        // Merge patient and dentist display names for the list view
        $userModel = new \App\Models\UserModel();
        foreach ($prescriptions as &$pr) {
            $patient = $userModel->find($pr['patient_id'] ?? null);
            $pr['patient_name'] = $patient['name'] ?? 'Unknown';

            // Prefer saved dentist snapshot; otherwise use user record
            if (empty($pr['dentist_name'])) {
                $dentistUser = $userModel->find($pr['dentist_id'] ?? null);
                $pr['dentist_name'] = $dentistUser['name'] ?? 'Unknown';
            }
        }
        unset($pr);

        $content = view('prescriptions/index', ['user' => $user, 'prescriptions' => $prescriptions]);
        return view('templates/admin_layout', [
            'title' => 'Prescriptions - Perfect Smile',
            'content' => $content,
            'user' => $user
        ]);
    }

    public function create()
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) return $user;

        // Minimal data: patients list for select
        $userModel = new \App\Models\UserModel();
        $patients = $userModel->where('user_type','patient')->orderBy('name','ASC')->findAll();

        // Sanitize patient address: clear if it looks like an email or equals the email field
        foreach ($patients as &$p) {
            $addr = $p['address'] ?? '';
            $email = $p['email'] ?? '';
            if (!$addr || strpos($addr, '@') !== false || $addr === $email) {
                $p['address'] = '';
            }
        }
        unset($p);

        // Get current user (dentist) information for prefilling
        $dentist = $userModel->find($user['id']);
        $dentistInfo = [
            'name' => $dentist['name'] ?? $user['name'] ?? '',
            'license_no' => $dentist['license_no'] ?? '',
            'ptr_no' => $dentist['ptr_no'] ?? ''
        ];

        $content = view('prescriptions/create', ['user' => $user, 'patients' => $patients, 'dentist' => $dentistInfo]);
        return view('templates/admin_layout', [
            'title' => 'New Prescription - Perfect Smile',
            'content' => $content,
            'user' => $user
        ]);
    }

    public function store()
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) return $user;

        $post = $this->request->getPost();
        $data = [
            'dentist_id' => $user['id'] ?? null,
            'dentist_name' => $post['dentist_name'] ?? ($user['name'] ?? ''),
            'license_no' => $post['license_no'] ?? null,
            'ptr_no' => $post['ptr_no'] ?? null,
            'patient_id' => $post['patient_id'] ?? null,
            'appointment_id' => $post['appointment_id'] ?? null,
            'next_appointment' => $post['next_appointment'] ?? null,
            'issue_date' => $post['issue_date'] ?? date('Y-m-d'),
            // store instructions in DB column 'notes' (existing schema)
            'notes' => $post['instructions'] ?? null,
        ];

        $result = $this->presModel->insert($data);
        if (!$result) {
            return redirect()->back()->with('error', 'Failed to create prescription')->withInput();
        }
        
        $presId = $this->presModel->getInsertID();

        // Update patient information if provided
        if (!empty($post['patient_id'])) {
            $userModel = new \App\Models\UserModel();
            $patientData = [];
            
            if (!empty($post['age'])) $patientData['age'] = $post['age'];
            if (!empty($post['gender'])) $patientData['gender'] = $post['gender'];
            if (!empty($post['address'])) $patientData['address'] = $post['address'];
            
            if (!empty($patientData)) {
                $userModel->update($post['patient_id'], $patientData);
            }
        }

        // items
        $items = $post['items'] ?? [];
        if (is_array($items)) {
            foreach ($items as $it) {
                if (empty($it['medicine_name'])) continue;
                $itemResult = $this->itemModel->insert(array_merge($it, ['prescription_id' => $presId]));
                if (!$itemResult) {
                    log_message('error', 'Failed to insert prescription item: ' . print_r($it, true));
                }
            }
        }

        return redirect()->to('/admin/prescriptions')->with('success','Prescription saved');
    }

    public function show($id)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) return $user;

        $pres = $this->presModel->find($id);
        if (!$pres) return redirect()->back()->with('error','Not found');
        $items = $this->itemModel->where('prescription_id',$id)->findAll();

    // Get patient and dentist information
        $userModel = new \App\Models\UserModel();
        $patient = $userModel->find($pres['patient_id']);
        $dentist = $userModel->find($pres['dentist_id']);

        // Merge patient and dentist info into prescription data
        $pres['patient_name'] = $patient['name'] ?? 'Unknown';
        $pres['patient_age'] = $patient['age'] ?? '';
        $pres['patient_gender'] = $patient['gender'] ?? '';
        // Ensure we don't show an email as an address
        $patientAddress = $patient['address'] ?? '';
        if (strpos($patientAddress, '@') !== false || $patientAddress === ($patient['email'] ?? '')) {
            $patientAddress = '';
        }
    $pres['patient_address'] = $patientAddress;
    // map legacy DB 'notes' to 'instructions' for views
    $pres['instructions'] = $pres['notes'] ?? null;
    // Prefer snapshot values stored on the prescription; fall back to dentist user profile
    $pres['dentist_name'] = $pres['dentist_name'] ?? ($dentist['name'] ?? 'Unknown');
    $pres['license_no'] = $pres['license_no'] ?? ($dentist['license_no'] ?? '');
    $pres['ptr_no'] = $pres['ptr_no'] ?? ($dentist['ptr_no'] ?? '');

        $content = view('prescriptions/show', ['user' => $user, 'prescription' => $pres, 'items' => $items]);
        return view('templates/admin_layout', [
            'title' => 'Prescription #' . $id . ' - Perfect Smile',
            'content' => $content,
            'user' => $user
        ]);
    }

    public function edit($id)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) return $user;

        $pres = $this->presModel->find($id);
        if (!$pres) return redirect()->back()->with('error','Not found');

        $items = $this->itemModel->where('prescription_id',$id)->findAll();

        // Patients list for select
        $userModel = new \App\Models\UserModel();
        $patients = $userModel->where('user_type','patient')->orderBy('name','ASC')->findAll();

        // Sanitize patient address values for the select list
        foreach ($patients as &$pt) {
            $addr = $pt['address'] ?? '';
            $email = $pt['email'] ?? '';
            if (!$addr || strpos($addr, '@') !== false || $addr === $email) {
                $pt['address'] = '';
            }
        }
        unset($pt);

        // Get patient and dentist information for prefilling
        $patient = $userModel->find($pres['patient_id']);
        $dentist = $userModel->find($pres['dentist_id']);

        // Merge patient and dentist info into prescription data
        $pres['patient_name'] = $patient['name'] ?? 'Unknown';
        $pres['patient_age'] = $patient['age'] ?? '';
        $pres['patient_gender'] = $patient['gender'] ?? '';
        // Ensure we don't show an email as an address
        $patientAddress = $patient['address'] ?? '';
        if (strpos($patientAddress, '@') !== false || $patientAddress === ($patient['email'] ?? '')) {
            $patientAddress = '';
        }
    $pres['patient_address'] = $patientAddress;
    $pres['instructions'] = $pres['notes'] ?? null;
    $pres['dentist_name'] = $pres['dentist_name'] ?? ($dentist['name'] ?? 'Unknown');
    $pres['license_no'] = $pres['license_no'] ?? ($dentist['license_no'] ?? '');
    $pres['ptr_no'] = $pres['ptr_no'] ?? ($dentist['ptr_no'] ?? '');

        $content = view('prescriptions/edit', ['user' => $user, 'prescription' => $pres, 'items' => $items, 'patients' => $patients]);
        return view('templates/admin_layout', [
            'title' => 'Edit Prescription - #' . $id,
            'content' => $content,
            'user' => $user
        ]);
    }

    public function update($id)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) return $user;

        $pres = $this->presModel->find($id);
        if (!$pres) return redirect()->back()->with('error','Not found');

        $post = $this->request->getPost();
        
        // Update prescription data
        $data = [
            'patient_id' => $post['patient_id'] ?? $pres['patient_id'],
            'appointment_id' => $post['appointment_id'] ?? $pres['appointment_id'],
            'next_appointment' => $post['next_appointment'] ?? $pres['next_appointment'] ?? null,
            'issue_date' => $post['issue_date'] ?? $pres['issue_date'],
            // persist into existing 'notes' column
            'notes' => $post['instructions'] ?? $pres['notes'] ?? null,
            'dentist_name' => $post['dentist_name'] ?? $pres['dentist_name'] ?? null,
            'license_no' => $post['license_no'] ?? $pres['license_no'] ?? null,
            'ptr_no' => $post['ptr_no'] ?? $pres['ptr_no'] ?? null,
            'status' => $post['status'] ?? $pres['status'] ?? 'draft'
        ];

        $result = $this->presModel->update($id, $data);
        if (!$result) {
            return redirect()->back()->with('error', 'Failed to update prescription')->withInput();
        }

        // Update patient information if provided
        if (!empty($post['patient_id'])) {
            $userModel = new \App\Models\UserModel();
            $patientData = [];
            
            if (!empty($post['age'])) $patientData['age'] = $post['age'];
            if (!empty($post['gender'])) $patientData['gender'] = $post['gender'];
            if (!empty($post['address'])) $patientData['address'] = $post['address'];
            
            if (!empty($patientData)) {
                $userModel->update($post['patient_id'], $patientData);
            }
        }

        // Replace items: delete old and insert new
        $this->itemModel->where('prescription_id', $id)->delete();
        $items = $post['items'] ?? [];
        if (is_array($items)) {
            foreach ($items as $it) {
                if (empty($it['medicine_name'])) continue;
                $itemResult = $this->itemModel->insert(array_merge($it, ['prescription_id' => $id]));
                if (!$itemResult) {
                    log_message('error', 'Failed to insert prescription item: ' . print_r($it, true));
                }
            }
        }

        return redirect()->to('/admin/prescriptions/'.$id)->with('success','Prescription updated');
    }

    public function downloadPdf($id)
    {
    // Keep this method as HTML preview + layout for backward compatibility
    $pres = $this->presModel->find($id);
    if (!$pres) return redirect()->back()->with('error','Not found');
    $items = $this->itemModel->where('prescription_id',$id)->findAll();

        // Get patient and dentist from UserModel (consistent with other methods)
        $userModel = new \App\Models\UserModel();
        $patient = $userModel->find($pres['patient_id'] ?? null);
        $dentist = $userModel->find($pres['dentist_id'] ?? null);

        // Sanitize patient address like in other methods
        $patientAddress = $patient['address'] ?? '';
        if (strpos($patientAddress, '@') !== false || $patientAddress === ($patient['email'] ?? '')) {
            $patientAddress = '';
        }

        // Prefill prescription array with proper info
        $pres['patient_name']   = $patient['name'] ?? 'Unknown';
        $pres['patient_address'] = $patientAddress;
        $pres['address']        = $patientAddress;
        $pres['patient_age']    = $patient['age'] ?? '';
        $pres['age']            = $patient['age'] ?? '';
        $pres['patient_gender'] = $patient['gender'] ?? '';
        $pres['gender']         = $patient['gender'] ?? '';

    // Prefer snapshot (saved) dentist info; fall back to dentist user values
    $pres['dentist_name']   = $pres['dentist_name'] ?? ($dentist['name'] ?? 'Unknown');
    $pres['license_no']     = $pres['license_no'] ?? ($dentist['license_no'] ?? '');
    $pres['ptr_no']         = $pres['ptr_no'] ?? ($dentist['ptr_no'] ?? '');

        $content = view('prescriptions/pdf', [
            'prescription' => $pres,
            'items' => $items
        ]);

        return view('templates/admin_layout', [
            'title'   => 'Prescription PDF - ' . $id,
            'content' => $content,
            'user'    => $this->getAuthenticatedUser()
        ]);
    }

    /**
     * AJAX: return the HTML-only PDF preview (no admin layout) so the sidebar/font styles aren't affected
     */
    public function previewPdf($id)
    {
        $pres = $this->presModel->find($id);
        if (!$pres) return $this->response->setStatusCode(404)->setBody('Not found');
        $items = $this->itemModel->where('prescription_id',$id)->findAll();

        // Prefill like downloadPdf
        $userModel = new \App\Models\UserModel();
        $patient = $userModel->find($pres['patient_id'] ?? null);
        $dentist = $userModel->find($pres['dentist_id'] ?? null);
        $patientAddress = $patient['address'] ?? '';
        if (strpos($patientAddress, '@') !== false || $patientAddress === ($patient['email'] ?? '')) {
            $patientAddress = '';
        }
        $pres['patient_name'] = $patient['name'] ?? 'Unknown';
        $pres['patient_address'] = $patientAddress;
        $pres['patient_age'] = $patient['age'] ?? '';
        $pres['patient_gender'] = $patient['gender'] ?? '';
        $pres['instructions'] = $pres['notes'] ?? null;
        $pres['dentist_name'] = $pres['dentist_name'] ?? ($dentist['name'] ?? 'Unknown');
        $pres['license_no'] = $pres['license_no'] ?? ($dentist['license_no'] ?? '');
        $pres['ptr_no'] = $pres['ptr_no'] ?? ($dentist['ptr_no'] ?? '');

        // Return the PDF template for modal preview (the modal already has its own download button)
        return view('prescriptions/pdf', ['prescription' => $pres, 'items' => $items]);
    }

    /**
     * Generate and stream a real PDF file sized to Half Bond (A5 / 5.5" x 8.5")
     * Uses Dompdf if available; otherwise returns the HTML preview.
     */
    public function downloadPdfFile($id)
    {
        $pres = $this->presModel->find($id);
        if (!$pres) return redirect()->back()->with('error','Not found');
        $items = $this->itemModel->where('prescription_id',$id)->findAll();

        // Prepare and merge patient/dentist info (same as previewPdf)
        $userModel = new \App\Models\UserModel();
        $patient = $userModel->find($pres['patient_id'] ?? null);
        $dentist = $userModel->find($pres['dentist_id'] ?? null);

        $patientAddress = $patient['address'] ?? '';
        if (strpos($patientAddress, '@') !== false || $patientAddress === ($patient['email'] ?? '')) {
            $patientAddress = '';
        }

        $pres['patient_name'] = $patient['name'] ?? 'Unknown';
        $pres['patient_address'] = $patientAddress;
        $pres['patient_age'] = $patient['age'] ?? '';
        $pres['patient_gender'] = $patient['gender'] ?? '';
        
        // Map DB 'notes' to 'instructions' for PDF template
        $pres['instructions'] = $pres['notes'] ?? null;

        // Prefer snapshot (saved) dentist info; fall back to dentist user values
        $pres['dentist_name'] = $pres['dentist_name'] ?? ($dentist['name'] ?? 'Unknown');
        $pres['license_no'] = $pres['license_no'] ?? ($dentist['license_no'] ?? '');
        $pres['ptr_no'] = $pres['ptr_no'] ?? ($dentist['ptr_no'] ?? '');

        // Prepare view HTML using the download template with inline CSS
        $html = view('prescriptions/pdf_download', ['prescription' => $pres, 'items' => $items]);

        if (!class_exists('\Dompdf\Dompdf')) {
            // Dompdf not installed — fall back to rendering the HTML in browser
            return $this->response->setBody($html);
        }

        // Generate PDF with Dompdf
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->set_option('isPhpEnabled', true);
        $dompdf->set_option('isRemoteEnabled', true);
        
        // Bond paper short size in landscape: 11" wide x 8.5" tall
        $dompdf->setPaper(array(0, 0, 792, 612), 'landscape');
        $dompdf->loadHtml($html);
        $dompdf->render();

        $pdfOutput = $dompdf->output();

        // Stream with download header
        return $this->response->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="prescription_'.$id.'.pdf"')
            ->setBody($pdfOutput);
    }


    public function delete($id)
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) return $user;

        $this->itemModel->where('prescription_id',$id)->delete();
        $this->presModel->delete($id);

        return redirect()->back()->with('success','Prescription deleted');
    }
}
