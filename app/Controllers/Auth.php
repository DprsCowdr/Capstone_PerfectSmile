<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Controllers\BaseController;

class Auth extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Display login form
     */
    public function index()
    {
        // If user is already logged in, redirect based on user type
        if (session()->get('isLoggedIn')) {
            $userType = session()->get('user_type');
            switch ($userType) {
                case 'admin':
                    return redirect()->to('/admin/dashboard');
                case 'doctor':
                    return redirect()->to('/doctor/dashboard');
                case 'patient':
                    return redirect()->to('/patient/dashboard');
                case 'staff':
                    return redirect()->to('/staff/dashboard');
                default:
                    return redirect()->to('/admin/dashboard'); // Default to admin
            }
        }

        return view('auth/login');
    }

    /**
     * Handle login form submission
     */
    public function login()
    {
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // Validate input
        if (empty($email) || empty($password)) {
            session()->setFlashdata('error', 'Email and password are required');
            return redirect()->back()->withInput();
        }

        // Attempt authentication
        $user = $this->userModel->authenticate($email, $password);

        if ($user) {
            // Set session data
            session()->set([
                'isLoggedIn' => true,
                'user_id' => $user['id'],
                'user_name' => $user['name'],
                'user_email' => $user['email'],
                'user_type' => $user['user_type']
            ]);

            // Redirect based on user type
            switch ($user['user_type']) {
                case 'admin':
                    return redirect()->to('/admin/dashboard');
                case 'doctor':
                    return redirect()->to('/doctor/dashboard');
                case 'dentist':
                    return redirect()->to('/dentist/dashboard');
                case 'patient':
                    return redirect()->to('/patient/dashboard');
                case 'staff':
                    return redirect()->to('/staff/dashboard');
                default:
                    return redirect()->to('/admin/dashboard'); // Default to admin for unknown types
            }
        } else {
            session()->setFlashdata('error', 'Invalid email or password');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }

    /**
     * Display registration form
     */
    public function register()
    {
        return view('auth/register');
    }

    /**
     * Handle registration form submission
     */
    public function registerUser()
    {
        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'user_type' => 'patient', // Default to patient for registration
            'phone' => $this->request->getPost('phone'),
            'address' => $this->request->getPost('address'),
            'date_of_birth' => $this->request->getPost('date_of_birth'),
            'gender' => $this->request->getPost('gender')
        ];

        // Validate data
        if (!$this->userModel->validate($data)) {
            session()->setFlashdata('errors', $this->userModel->errors());
            return redirect()->back()->withInput();
        }

        // Check if email already exists
        if ($this->userModel->getUserByEmail($data['email'])) {
            session()->setFlashdata('error', 'Email already registered');
            return redirect()->back()->withInput();
        }

        // Save user
        if ($this->userModel->save($data)) {
            session()->setFlashdata('success', 'Registration successful! Please login.');
            return redirect()->to('/login');
        } else {
            session()->setFlashdata('error', 'Registration failed. Please try again.');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated()
    {
        return session()->get('isLoggedIn') === true;
    }

    /**
     * Get current user data
     */
    public static function getCurrentUser()
    {
        // Allow tests and some code paths to set a session-backed user array directly to avoid DB lookups
        if (!empty($_SESSION['user']) && is_array($_SESSION['user'])) {
            return $_SESSION['user'];
        }

        if (self::isAuthenticated()) {
            $userModel = new UserModel();
            return $userModel->find(session()->get('user_id'));
        }
        return null;
    }
} 