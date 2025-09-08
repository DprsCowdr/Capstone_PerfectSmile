<?php

namespace App\Controllers;

use App\Models\ServiceModel;
use App\Models\AppointmentServiceModel;
use App\Models\AppointmentModel;
use App\Controllers\Auth;

class CheckupServices extends BaseController
{
    protected $serviceModel;
    protected $appointmentServiceModel;
    protected $appointmentModel;

    public function __construct()
    {
        $this->serviceModel = new ServiceModel();
        $this->appointmentServiceModel = new AppointmentServiceModel();
        $this->appointmentModel = new AppointmentModel();
    }

    /**
     * Get services for a specific appointment (AJAX)
     */
    public function getAppointmentServices($appointmentId)
    {
        $user = Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['dentist', 'admin'])) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(403);
        }

        try {
            // Check if filtering by tooth number
            $toothNumber = $this->request->getGet('tooth_number');
            
            if ($toothNumber) {
                $services = $this->appointmentServiceModel->getAppointmentServicesByTooth($appointmentId, $toothNumber);
            } else {
                $services = $this->appointmentServiceModel->getAppointmentServices($appointmentId);
            }
            
            $total = $this->appointmentServiceModel->getAppointmentTotal($appointmentId);

            return $this->response->setJSON([
                'success' => true,
                'services' => $services,
                'total' => number_format($total, 2),
                'count' => count($services)
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => 'Failed to fetch services: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Add service to appointment (AJAX)
     */
    public function addService($appointmentId)
    {
        $user = Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['dentist', 'admin'])) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(403);
        }

        // Get JSON input if available, fallback to POST
        $input = $this->request->getJSON();
        if ($input) {
            $serviceId = $input->service_id ?? null;
            $toothNumber = $input->tooth_number ?? null;
            $surface = $input->surface ?? null;
            $notes = $input->notes ?? null;
        } else {
            $serviceId = $this->request->getPost('service_id');
            $toothNumber = $this->request->getPost('tooth_number', FILTER_SANITIZE_STRING) ?: null;
            $surface = $this->request->getPost('surface', FILTER_SANITIZE_STRING) ?: null;
            $notes = $this->request->getPost('notes', FILTER_SANITIZE_STRING) ?: null;
        }

        if (!$serviceId) {
            return $this->response->setJSON(['error' => 'Service ID is required'])->setStatusCode(400);
        }

        // Verify appointment exists and belongs to dentist (if dentist user)
        $appointment = $this->appointmentModel->find($appointmentId);
        if (!$appointment) {
            return $this->response->setJSON(['error' => 'Appointment not found'])->setStatusCode(404);
        }

        if ($user['user_type'] === 'dentist' && $appointment['dentist_id'] != $user['id']) {
            return $this->response->setJSON(['error' => 'Access denied'])->setStatusCode(403);
        }

        try {
            // Get service details
            $service = $this->serviceModel->find($serviceId);
            if (!$service) {
                return $this->response->setJSON(['error' => 'Service not found'])->setStatusCode(404);
            }

            // Add service to appointment with tooth data
            $insertData = [
                'appointment_id' => $appointmentId,
                'service_id' => $serviceId,
                'tooth_number' => $toothNumber,
                'surface' => $surface,
                'notes' => $notes
            ];

            $appointmentServiceId = $this->appointmentServiceModel->insert($insertData);

            if (!$appointmentServiceId) {
                return $this->response->setJSON([
                    'error' => 'Failed to add service to appointment'
                ])->setStatusCode(500);
            }

            // Get updated totals
            $total = $this->appointmentServiceModel->getAppointmentTotal($appointmentId);
            $count = $this->appointmentServiceModel->getAppointmentServiceCount($appointmentId);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Service added successfully',
                'service' => array_merge($service, [
                    'appointment_service_id' => $appointmentServiceId,
                    'tooth_number' => $toothNumber,
                    'surface' => $surface,
                    'notes' => $notes
                ]),
                'total' => number_format($total, 2),
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => 'Failed to add service: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Remove service from appointment (AJAX)
     */
    public function removeService($appointmentId, $appointmentServiceId)
    {
        $user = Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['dentist', 'admin'])) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(403);
        }

        // Verify appointment belongs to dentist (if dentist user)
        $appointment = $this->appointmentModel->find($appointmentId);
        if (!$appointment) {
            return $this->response->setJSON(['error' => 'Appointment not found'])->setStatusCode(404);
        }

        if ($user['user_type'] === 'dentist' && $appointment['dentist_id'] != $user['id']) {
            return $this->response->setJSON(['error' => 'Access denied'])->setStatusCode(403);
        }

        try {
            $deleted = $this->appointmentServiceModel->delete($appointmentServiceId);

            if (!$deleted) {
                return $this->response->setJSON([
                    'error' => 'Failed to remove service'
                ])->setStatusCode(500);
            }

            // Get updated totals
            $total = $this->appointmentServiceModel->getAppointmentTotal($appointmentId);
            $count = $this->appointmentServiceModel->getAppointmentServiceCount($appointmentId);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Service removed successfully',
                'total' => number_format($total, 2),
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => 'Failed to remove service: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Search services (AJAX)
     */
    public function searchServices()
    {
        $user = Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['dentist', 'admin'])) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(403);
        }

        $searchTerm = $this->request->getGet('q', FILTER_SANITIZE_STRING) ?: '';
        $limit = $this->request->getGet('limit', FILTER_VALIDATE_INT) ?: 20;

        try {
            $services = $this->serviceModel->searchServices($searchTerm, $limit);
            
            return $this->response->setJSON([
                'success' => true,
                'services' => $services
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => 'Failed to search services: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Get all services for selection (AJAX)
     */
    public function getAllServices()
    {
        $user = Auth::getCurrentUser();
        if (!$user || !in_array($user['user_type'], ['dentist', 'admin'])) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(403);
        }

        try {
            $services = $this->serviceModel->getServicesForSelection();
            
            return $this->response->setJSON([
                'success' => true,
                'services' => $services
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => 'Failed to fetch services: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}
