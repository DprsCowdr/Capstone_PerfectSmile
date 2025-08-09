<?php
namespace App\Controllers;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Redirect to appropriate dashboard based on user type
        $userType = session()->get('user_type');
        switch ($userType) {
            case 'admin':
                return redirect()->to('/admin/dashboard');
            case 'doctor':
                return redirect()->to('/dentist/dashboard');
            case 'patient':
                return redirect()->to('/patient/dashboard');
            case 'staff':
                return redirect()->to('/staff/dashboard');
            default:
                // If user type is unknown, logout and redirect to login
                session()->destroy();
                return redirect()->to('/login');
        }
    }
} 