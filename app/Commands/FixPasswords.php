<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UserModel;

class FixPasswords extends BaseCommand
{
    protected $group       = 'Auth';
    protected $name        = 'auth:fix-passwords';
    protected $description = 'Fix user passwords by hashing them properly';

    public function run(array $params)
    {
        $userModel = new UserModel();
        
        // Get all users
        $users = $userModel->findAll();
        
        foreach ($users as $user) {
            // Check if password is already hashed
            if (!password_verify('admin123', $user['password']) && 
                !password_verify('doctor123', $user['password']) && 
                !password_verify('patient123', $user['password']) && 
                !password_verify('staff123', $user['password'])) {
                
                // Password is already hashed, skip
                CLI::write("User {$user['email']} password already hashed", 'green');
                continue;
            }
            
            // Hash the password based on user type
            $plainPassword = '';
            switch ($user['user_type']) {
                case 'admin':
                    $plainPassword = 'admin123';
                    break;
                case 'doctor':
                    $plainPassword = 'doctor123';
                    break;
                case 'patient':
                    $plainPassword = 'patient123';
                    break;
                case 'staff':
                    $plainPassword = 'staff123';
                    break;
            }
            
            if ($plainPassword) {
                $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
                $userModel->update($user['id'], ['password' => $hashedPassword]);
                CLI::write("Updated password for {$user['email']}", 'yellow');
            }
        }
        
        CLI::write('All passwords have been updated!', 'green');
    }
} 