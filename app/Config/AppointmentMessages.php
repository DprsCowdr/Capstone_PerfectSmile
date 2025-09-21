<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class AppointmentMessages extends BaseConfig
{
    // Default templates. Admins can override these by editing writable/appointment_messages.json
    public $templates = [
    'patient' => "You're all set! on {when} — we allow a {grace}-minute grace period. {appointment_length} If you can't make it, please contact us to reschedule.",
        'staff' => "A new appointment has been created. Appointment time: {when}. The patient has a grace period of {grace} minutes for late arrival. Please monitor attendance and update the status if needed.",
        'admin' => "A new appointment has been logged in the system. Appointment time: {when}. Grace period rules are active ({grace} minutes). Staff will handle status updates if the patient is late or absent.",
        'duplicate_note' => "(Note: an identical appointment was already on file; no duplicate was created.)",
        'adjusted_note' => "(Note: requested time was adjusted to {adjusted_time} to avoid a conflict.)"
    ];

    /**
     * Load overrides from writable/appointment_messages.json if present.
     * This allows non-developers (admins) to edit the JSON file via the admin UI we provide.
     */
    public function __construct()
    {
        $path = WRITEPATH . 'appointment_messages.json';
        if (is_file($path)) {
            try {
                $json = file_get_contents($path);
                $data = json_decode($json, true);
                if (is_array($data)) {
                    $this->templates = array_merge($this->templates, $data);
                }
            } catch (\Exception $e) {
                // ignore and use defaults
            }
        }
    }

    public function getTemplate($key)
    {
        return $this->templates[$key] ?? null;
    }
}
