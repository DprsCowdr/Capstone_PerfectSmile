<?php
namespace App\Controllers;

use App\Controllers\Appointments;

class AdminCalendarController extends BaseController
{
	// Forward to Appointments controller for a single source of truth
	public function dayAppointments()
	{
		$a = new Appointments();
		return $a->dayAppointments();
	}

	public function availableSlots()
	{
		$a = new Appointments();
		return $a->availableSlots();
	}

	public function checkConflicts()
	{
		$a = new Appointments();
		return $a->checkConflicts();
	}
}

