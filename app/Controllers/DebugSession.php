<?php
namespace App\Controllers;

use App\Controllers\BaseController;

class DebugSession extends BaseController
{
    public function index()
    {
        $sess = [];
        try { $sess = session()->get(); } catch (\Throwable $e) { $sess = ['error' => $e->getMessage()]; }
        $user = null;
        try { $user = \App\Controllers\Auth::getCurrentUser(); } catch (\Throwable $e) { $user = ['error' => $e->getMessage()]; }
        return $this->response->setJSON(['session' => $sess, 'current_user' => $user]);
    }
}