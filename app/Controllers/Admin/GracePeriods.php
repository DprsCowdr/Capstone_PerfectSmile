<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseAdminController;
use App\Traits\AdminAuthTrait;

class GracePeriods extends BaseAdminController
{
    use AdminAuthTrait;

    public function save()
    {
        $user = $this->getAuthenticatedUser();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) return $user;

        if (($user['user_type'] ?? '') !== 'admin') {
            return redirect()->to('/admin')->with('error', 'Forbidden');
        }

        $post = $this->request->getPost();
        $default = isset($post['default_grace']) ? (int)$post['default_grace'] : null;

        // Validate default grace
        if ($default === null || $default < 0 || $default > 120) {
            return redirect()->back()->with('error', 'Default grace must be between 0 and 120 minutes');
        }

        $data = [
            'default' => $default,
            'updated_by' => $user['id'] ?? null,
            'updated_at' => date('c')
        ];

        $path = WRITEPATH . 'grace_periods.json';
        try {
            file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return redirect()->back()->with('success', 'Grace period saved');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to save grace period: ' . $e->getMessage());
        }
    }

    protected function getAuthenticatedUser()
    {
        return $this->checkAdminAuth();
    }

    protected function getAuthenticatedUserApi()
    {
        return $this->checkAdminAuthApi();
    }
}