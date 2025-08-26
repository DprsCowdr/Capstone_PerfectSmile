<?php

namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class Dashboard extends BaseConfig
{
    /**
     * Grace window in minutes for considering an appointment missed.
     * If an appointment is more than this many minutes in the past, the dashboard
     * will move to the next appointment.
     */
    public $nextAppointmentGraceMinutes = 15;
}
