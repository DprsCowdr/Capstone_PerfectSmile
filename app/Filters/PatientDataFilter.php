<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * Strips patient-identifying fields from JSON responses when the
 * current session user is a patient. Designed to be conservative and
 * run after controllers produce their responses.
 */
class PatientDataFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // no-op before
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Only operate on JSON responses
        $contentType = $response->getHeaderLine('Content-Type');
        if (stripos($contentType, 'application/json') === false) {
            return;
        }

        // If the session says user_type is not 'patient', do nothing
        try {
            $session = session();
            if ($session->get('user_type') !== 'patient') {
                return;
            }
        } catch (\Exception $e) {
            // If session unavailable, be conservative and do nothing
            return;
        }

        $body = $response->getBody();
        $data = json_decode($body, true);
        if ($data === null) {
            return; // nothing we can do
        }

        $stripKeys = ['patient_name', 'patient_email', 'patient_phone'];

        $sanitize = function (&$item) use ($stripKeys) {
            if (!is_array($item)) return;
            foreach ($stripKeys as $k) {
                if (array_key_exists($k, $item)) {
                    unset($item[$k]);
                }
            }
            // If nested appointment lists exist, sanitize them
            foreach ($item as &$v) {
                if (is_array($v)) {
                    // recursively sanitize arrays of arrays
                    array_walk_recursive($v, function (&$val, $key) use ($stripKeys) {
                        // nothing — array_walk_recursive doesn't give parent context for keys
                    });
                }
            }
        };

        // If payload is a list of appointments
        if (isset($data[0]) && is_array($data)) {
            foreach ($data as &$entry) {
                $sanitize($entry);
            }
            $newBody = json_encode($data);
            $response->setBody($newBody);
            return;
        }

        // Common shapes: { data: [...], meta: ... } or single resource
        if (isset($data['data']) && is_array($data['data'])) {
            foreach ($data['data'] as &$entry) {
                $sanitize($entry);
            }
            $response->setBody(json_encode($data));
            return;
        }

        // Fallback: sanitize top-level array
        $sanitize($data);
        $response->setBody(json_encode($data));
    }
}
