<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Controllers\Auth;
use App\Models\UserModel;

class DebugAuth extends BaseCommand
{
    protected $group = 'debug';
    protected $name = 'debug:auth';
    protected $description = 'Debug authentication and session state';

    public function run(array $params)
    {
        CLI::write('=== SESSION DEBUG ===', 'yellow');
        
        $session = session();
        
        CLI::write('Session ID: ' . session_id());
        CLI::write('Is Logged In: ' . ($session->get('isLoggedIn') ? 'true' : 'false'));
        CLI::write('User ID: ' . ($session->get('user_id') ?? 'null'));
        CLI::write('User Type: ' . ($session->get('user_type') ?? 'null'));
        CLI::write('User Name: ' . ($session->get('user_name') ?? 'null'));
        CLI::write('User Email: ' . ($session->get('user_email') ?? 'null'));
        
        CLI::newLine();
        CLI::write('=== ALL SESSION DATA ===', 'yellow');
        $allSession = $session->get();
        if ($allSession) {
            foreach ($allSession as $key => $value) {
                CLI::write("$key: " . (is_array($value) ? json_encode($value) : $value));
            }
        } else {
            CLI::write('No session data found', 'red');
        }
        
        CLI::newLine();
        CLI::write('=== TESTING getCurrentUser() ===', 'yellow');
        
        // Set test session for debugging
        $session->set([
            'isLoggedIn' => true,
            'user_id' => 1,
            'user_type' => 'admin'
        ]);
        
        CLI::write('Test session set with user_id=1, user_type=admin');
        
        $user = Auth::getCurrentUser();
        if ($user) {
            CLI::write('User found in database:', 'green');
            CLI::write('ID: ' . ($user['id'] ?? 'null'));
            CLI::write('Name: ' . ($user['name'] ?? 'null'));
            CLI::write('Email: ' . ($user['email'] ?? 'null'));
            CLI::write('User Type: ' . ($user['user_type'] ?? 'null'));
        } else {
            CLI::write('getCurrentUser() returned null', 'red');
            
            // Manual lookup
            CLI::write('Attempting manual lookup...', 'yellow');
            $userModel = new UserModel();
            $dbUser = $userModel->find(1);
            if ($dbUser) {
                CLI::write('Manual lookup successful:', 'green');
                CLI::write('ID: ' . ($dbUser['id'] ?? 'null'));
                CLI::write('Name: ' . ($dbUser['name'] ?? 'null'));
                CLI::write('Email: ' . ($dbUser['email'] ?? 'null'));
                CLI::write('User Type: ' . ($dbUser['user_type'] ?? 'null'));
            } else {
                CLI::write('Manual lookup failed - user not found in database', 'red');
                
                // Check what users exist
                CLI::write('Checking all users in database...', 'yellow');
                $allUsers = $userModel->findAll();
                if ($allUsers) {
                    CLI::write('Found ' . count($allUsers) . ' users:');
                    foreach ($allUsers as $u) {
                        CLI::write("ID: {$u['id']}, Name: {$u['name']}, Type: {$u['user_type']}");
                    }
                } else {
                    CLI::write('No users found in database!', 'red');
                }
            }
        }
        
        CLI::newLine();
        CLI::write('Done.', 'green');
    }
}
