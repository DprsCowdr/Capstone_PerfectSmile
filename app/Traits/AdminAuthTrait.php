<?php

namespace App\Traits;

use App\Controllers\Auth;

trait AdminAuthTrait
{
    protected function checkAdminAuth()
    {
        log_message('debug', "AdminAuthTrait::checkAdminAuth - Starting authentication check");
        
        $user = Auth::getCurrentUser();
        
        log_message('debug', "AdminAuthTrait::checkAdminAuth - getCurrentUser result: " . ($user ? 'user found' : 'null'));
        
        // Handle null user (not authenticated)
        if (!$user) {
            log_message('debug', "AdminAuthTrait::checkAdminAuth - No user found, redirecting to login");
            session()->setFlashdata('error', 'Please log in to access this page');
            return redirect()->to('/login');
        }
        
        log_message('debug', "AdminAuthTrait::checkAdminAuth - User data: " . json_encode($user));
        
        // Handle user without user_type (data issue)
        if (!isset($user['user_type'])) {
            log_message('error', 'User ' . ($user['id'] ?? 'unknown') . ' has no user_type set');
            session()->setFlashdata('error', 'Account configuration error. Please contact administrator.');
            return redirect()->to('/login');
        }
        
        // Allow both 'admin' and 'dentist' roles to access admin-style pages that manage patients/clinic data.
        // NOTE: this is a conscious, minimal change to unblock dentist access to patient modules.
        if (! in_array($user['user_type'], ['admin', 'dentist'])) {
            log_message('debug', "AdminAuthTrait::checkAdminAuth - User type '{$user['user_type']}' is not admin or dentist, redirecting");
            session()->setFlashdata('error', 'Admin or Dentist access required for this page');
            return redirect()->to('/dashboard');
        }
        
        log_message('debug', "AdminAuthTrait::checkAdminAuth - Authentication successful for admin user");
        return $user;
    }
    
    protected function checkAdminAuthApi()
    {
        $user = Auth::getCurrentUser();
        
        if (!$user || !isset($user['user_type']) || ! in_array($user['user_type'], ['admin', 'dentist'])) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }
        return $user;
    }
} 