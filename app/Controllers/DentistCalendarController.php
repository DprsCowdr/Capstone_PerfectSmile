<?php
namespace App\Controllers;

use App\Controllers\Appointments;

class DentistCalendarController extends BaseController
{
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

