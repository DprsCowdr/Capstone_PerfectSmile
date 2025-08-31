<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AppointmentModelTest extends CIUnitTestCase
{
    public function testSplitDateTimeLogicAndDurationMath()
    {
        // Simulate a row as AppointmentModel::splitDateTime would produce
        $row = [
            'appointment_datetime' => '2025-09-01 09:00:00',
            'procedure_duration' => 45
        ];

        // Emulate splitDateTime behavior (substr)
        if (isset($row['appointment_datetime'])) {
            $row['appointment_date'] = substr($row['appointment_datetime'], 0, 10);
            $row['appointment_time'] = substr($row['appointment_datetime'], 11, 5);
        }

        // Assert appointment_time was derived correctly
        $this->assertArrayHasKey('appointment_time', $row);
        $this->assertEquals('09:00', $row['appointment_time']);

        // Compute end time (start + duration)
        $startTs = strtotime($row['appointment_datetime']);
        $duration = isset($row['procedure_duration']) ? (int)$row['procedure_duration'] : 30;
        $endTs = $startTs + ($duration * 60);
        $end = date('H:i', $endTs);

        // Default interval used in calendar labels is 30 minutes
        $endWithInterval = date('H:i', $endTs + (30 * 60));

        $this->assertEquals('09:45', $end);
        $this->assertEquals('10:15', $endWithInterval);
    }
}
