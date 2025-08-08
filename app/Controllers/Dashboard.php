<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Controllers\Auth;

class Dashboard extends BaseController
{
    public function index()
    {
        // Check if user is logged in
        if (!Auth::isAuthenticated()) {
            return redirect()->to('/auth');
        }

        $user = Auth::getCurrentUser();
        
        return view('dashboard', [
            'user' => $user
        ]);
    }
} 