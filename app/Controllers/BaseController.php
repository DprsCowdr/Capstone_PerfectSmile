<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = [];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = service('session');
    }

    /**
     * Resolve branch id from request or session.
     * Preference order:
     *  - POST/GET param 'branch_id'
     *  - JSON body 'branch_id'
     *  - session('selected_branch_id')
     * Returns null when no branch selected (meaning all branches).
     *
     * @return int|null
     */
    protected function resolveBranchId()
    {
        // Check POST/GET
        $bid = $this->request->getPost('branch_id');
        if (empty($bid)) $bid = $this->request->getGet('branch_id');

        // Check JSON payload
        if (empty($bid)) {
            try {
                $json = $this->request->getJSON(true);
                if (is_array($json) && isset($json['branch_id'])) $bid = $json['branch_id'];
            } catch (\Exception $e) {
                // ignore
            }
        }

        if (!empty($bid)) return is_numeric($bid) ? (int)$bid : $bid;

        // Fallback to session
        $session = session();
        $s = $session->get('selected_branch_id');
        return !empty($s) ? (is_numeric($s) ? (int)$s : $s) : null;
    }
}
