<?php

namespace App\Traits;

use App\Controllers\Auth;

trait AdminAuthTrait
{
    protected function checkAdminAuth()
    {
        $user = Auth::getCurrentUser();
        if ($user['user_type'] !== 'admin') {
            return redirect()->to('/dashboard');
        }
        return $user;
    }
    
    protected function checkAdminAuthApi()
    {
        $user = Auth::getCurrentUser();
        if ($user['user_type'] !== 'admin') {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }
        return $user;
    }
} 