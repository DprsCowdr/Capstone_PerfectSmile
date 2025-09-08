<?php

namespace App\Models;

use CodeIgniter\Model;

class AppointmentServiceModel extends Model
{
    protected $table = 'appointment_service';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'appointment_id',
        'service_id',
        'tooth_number',
        'surface',
        'notes'
    ];

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules = [
        'appointment_id' => 'required|numeric',
        'service_id' => 'required|numeric'
    ];

    protected $validationMessages = [
        'appointment_id' => [
            'required' => 'Appointment ID is required',
            'numeric' => 'Invalid appointment ID'
        ],
        'service_id' => [
            'required' => 'Service ID is required',
            'numeric' => 'Invalid service ID'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Get services for an appointment
     */
    public function getServicesForAppointment($appointmentId)
    {
        return $this->select('appointment_service.*, services.name as service_name, services.price')
                    ->join('services', 'services.id = appointment_service.service_id')
                    ->where('appointment_id', $appointmentId)
                    ->findAll();
    }

    /**
     * Get appointments for a service
     */
    public function getAppointmentsForService($serviceId)
    {
        return $this->where('service_id', $serviceId)->findAll();
    }

    /**
     * Get services for an appointment with detailed information
     */
    public function getAppointmentServices($appointmentId)
    {
        return $this->select('appointment_service.id as appointment_service_id, appointment_service.*, services.name as service_name, services.description, services.price')
                    ->join('services', 'services.id = appointment_service.service_id')
                    ->where('appointment_id', $appointmentId)
                    ->findAll();
    }

    /**
     * Get services for an appointment filtered by tooth number
     */
    public function getAppointmentServicesByTooth($appointmentId, $toothNumber)
    {
        return $this->select('appointment_service.id as appointment_service_id, appointment_service.*, services.name as service_name, services.description, services.price')
                    ->join('services', 'services.id = appointment_service.service_id')
                    ->where('appointment_id', $appointmentId)
                    ->where('tooth_number', $toothNumber)
                    ->findAll();
    }

    /**
     * Get total cost for an appointment's services
     */
    public function getAppointmentTotal($appointmentId)
    {
        $result = $this->select('SUM(services.price) as total')
                      ->join('services', 'services.id = appointment_service.service_id')
                      ->where('appointment_id', $appointmentId)
                      ->first();
        
        return $result ? (float)$result['total'] : 0.0;
    }

    /**
     * Get count of services for an appointment
     */
    public function getAppointmentServiceCount($appointmentId)
    {
        return $this->where('appointment_id', $appointmentId)->countAllResults();
    }
} 