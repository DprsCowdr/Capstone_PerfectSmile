<?php

namespace App\Services;

use App\Controllers\Auth;

class AuthService
{
    /**
     * Check admin authentication
     */
    public static function checkAdminAuth()
    {
        if (!Auth::isAuthenticated()) {
            return redirect()->to('/login');
        }

        $user = Auth::getCurrentUser();
        
        if ($user['user_type'] !== 'admin') {
            return redirect()->to('/dashboard');
        }

        return $user;
    }
    
    /**
     * Check staff authentication
     */
    public static function checkStaffAuth()
    {
        if (!Auth::isAuthenticated()) {
            return redirect()->to('/login');
        }

        $user = Auth::getCurrentUser();
        
        if ($user['user_type'] !== 'staff') {
            return redirect()->to('/dashboard');
        }

        return $user;
    }
    
    /**
     * Check dentist authentication
     */
    public static function checkDentistAuth()
    {
        if (!Auth::isAuthenticated()) {
            return redirect()->to('/login');
        }

        $user = Auth::getCurrentUser();
        
        if ($user['user_type'] !== 'doctor') {
            return redirect()->to('/dashboard');
        }

        return $user;
    }
    
    /**
     * Check patient authentication
     */
    public static function checkPatientAuth()
    {
        if (!Auth::isAuthenticated()) {
            return redirect()->to('/login');
        }

        $user = Auth::getCurrentUser();
        
        if ($user['user_type'] !== 'patient') {
            return redirect()->to('/dashboard');
        }

        return $user;
    }
    
    /**
     * Check API authentication for admin
     */
    public static function checkAdminAuthApi()
    {
        if (!Auth::isAuthenticated()) {
            return response()->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $user = Auth::getCurrentUser();
        
        if ($user['user_type'] !== 'admin') {
            return response()->setJSON(['error' => 'Forbidden'])->setStatusCode(403);
        }

        return $user;
    }
    
    /**
     * Check API authentication for staff
     */
    public static function checkStaffAuthApi()
    {
        if (!Auth::isAuthenticated()) {
            return response()->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $user = Auth::getCurrentUser();
        
        if ($user['user_type'] !== 'staff') {
            return response()->setJSON(['error' => 'Forbidden'])->setStatusCode(403);
        }

        return $user;
    }
    
    /**
     * Check if user has admin or staff privileges
     */
    public static function checkAdminOrStaffAuth()
    {
        if (!Auth::isAuthenticated()) {
            return redirect()->to('/login');
        }

        $user = Auth::getCurrentUser();
        
        if (!in_array($user['user_type'], ['admin', 'staff'])) {
            return redirect()->to('/dashboard');
        }

        return $user;
    }
    
    /**
     * Check if user has admin or dentist privileges
     */
    public static function checkAdminOrDentistAuth()
    {
        if (!Auth::isAuthenticated()) {
            return redirect()->to('/login');
        }

        $user = Auth::getCurrentUser();
        
        if (!in_array($user['user_type'], ['admin', 'doctor'])) {
            return redirect()->to('/dashboard');
        }

        return $user;
    }
    
    /**
     * Get current user or redirect if not authenticated
     */
    public static function getCurrentUserOrRedirect()
    {
        if (!Auth::isAuthenticated()) {
            return redirect()->to('/login');
        }

        return Auth::getCurrentUser();
    }
}
