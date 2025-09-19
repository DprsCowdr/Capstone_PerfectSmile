<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseAdminController;
use App\Traits\AdminAuthTrait;

class MessageTemplates extends BaseAdminController
{
    use AdminAuthTrait;
    public function index()
    {
        // Redirect to the Settings page where the message templates editor is embedded
        return redirect()->to('/admin/settings');
    }

    public function save()
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) return $user;

        if (($user['user_type'] ?? '') !== 'admin') {
            return redirect()->to('/admin')->with('error', 'Forbidden');
        }

        $post = $this->request->getPost();
        $templates = [];
        foreach ($post as $k => $v) {
            // sanitize keys and values minimally
            $templates[$k] = (string)$v;
        }

        // Server-side placeholder validation rules per template key
        $requiredPlaceholders = [
            'patient' => ['{when}', '{grace}'],
            'staff' => ['{when}', '{grace}'],
            'admin' => ['{when}', '{grace}'],
            'adjusted_note' => ['{adjusted_time}'],
            // duplicate_note and other freeform keys are allowed to be arbitrary
        ];

        // {appointment_length} is optional but supported; do not fail validation if missing.

        $errors = [];
        foreach ($requiredPlaceholders as $key => $placeholders) {
            if (isset($templates[$key])) {
                $value = $templates[$key];
                foreach ($placeholders as $ph) {
                    if (strpos($value, $ph) === false) {
                        $errors[] = "Template '{$key}' must include placeholder {$ph}.";
                    }
                }
            }
        }

        if (!empty($errors)) {
            return redirect()->back()->with('error', implode(' ', $errors));
        }

        $path = WRITEPATH . 'appointment_messages.json';
        try {
            file_put_contents($path, json_encode($templates, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            // If request is AJAX, return JSON
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'ok', 'message' => 'Message templates saved']);
            }
            return redirect()->back()->with('success', 'Message templates saved');
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to save templates: ' . $e->getMessage()]);
            }
            return redirect()->back()->with('error', 'Failed to save templates: ' . $e->getMessage());
        }
    }

    /**
     * Return templates as JSON for AJAX consumption.
     */
    public function fetch()
    {
        $user = $this->getAuthenticatedUserApi();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) return $user;

        $path = WRITEPATH . 'appointment_messages.json';
        if (is_file($path)) {
            $json = file_get_contents($path);
            $templates = json_decode($json, true) ?: [];
        } else {
            $cfg = config('AppointmentMessages');
            $templates = $cfg->templates;
        }
        return $this->response->setJSON(['status' => 'ok', 'templates' => $templates]);
    }

    // Implement abstract methods from BaseAdminController by delegating to AdminAuthTrait helpers
    protected function getAuthenticatedUser()
    {
        return $this->checkAdminAuth();
    }

    protected function getAuthenticatedUserApi()
    {
        return $this->checkAdminAuthApi();
    }
}
