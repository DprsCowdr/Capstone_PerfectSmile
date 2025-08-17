<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Log request details
        log_message('info', "AuthFilter: Checking auth for " . $request->getMethod() . " " . $request->getUri());
        log_message('info', "AuthFilter: Session isLoggedIn = " . (session()->get('isLoggedIn') ? 'YES' : 'NO'));
        log_message('info', "AuthFilter: Session data = " . json_encode(session()->get()));
        
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            log_message('warning', "AuthFilter: User not logged in, redirecting to login");
            return redirect()->to('/login');
        }

        log_message('info', "AuthFilter: User is authenticated");

        // Optional: Check user type for specific routes
        if (!empty($arguments)) {
            $userType = session()->get('user_type');
            if (!in_array($userType, $arguments)) {
                log_message('warning', "AuthFilter: User type {$userType} not allowed for this route");
                return redirect()->to('/dashboard');
            }
        }
        
        log_message('info', "AuthFilter: All checks passed");
    }

    /**
     * We don't have anything to do here.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
} 