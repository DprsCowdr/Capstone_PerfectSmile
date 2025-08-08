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
        'appointment_id', 'service_id'
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
} 