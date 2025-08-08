<?php

namespace App\Controllers;

use App\Controllers\Auth;

class Patient extends BaseController
{
    public function dashboard()
    {
        // Check if user is logged in and is patient
        if (!Auth::isAuthenticated()) {
            return redirect()->to('/login');
        }

        $user = Auth::getCurrentUser();
        
        // Check if user is patient
        if ($user['user_type'] !== 'patient') {
            return redirect()->to('/dashboard');
        }
        
        return view('patient/dashboard', [
            'user' => $user
        ]);
    }
} 