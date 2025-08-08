<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        if (\App\Controllers\Auth::isAuthenticated()) {
            $user = \App\Controllers\Auth::getCurrentUser();
            switch ($user['user_type']) {
                case 'admin':
                    return redirect()->to('/admin/dashboard');
                case 'doctor':
                    return redirect()->to('/dentist/dashboard');
                case 'patient':
                    return redirect()->to('/patient/dashboard');
                case 'staff':
                    return redirect()->to('/staff/dashboard');
                default:
                    return redirect()->to('/login');
            }
        }
        // If not logged in, show login page
        return redirect()->to('/login');
    }
    
    public function debug()
    {
        return view('debug');
    }
}
