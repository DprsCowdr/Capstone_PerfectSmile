<?php
namespace App\Controllers;

use App\Controllers\Auth;

class DentistAvailability extends BaseController
{
    public function index()
    {
        if (!Auth::isAuthenticated()) {
            return redirect()->to('/login');
        }

        $user = Auth::getCurrentUser();
        if ($user['user_type'] !== 'dentist') {
            return redirect()->to('/dashboard');
        }

        return view('dentist/availability', ['user' => $user]);
    }
}