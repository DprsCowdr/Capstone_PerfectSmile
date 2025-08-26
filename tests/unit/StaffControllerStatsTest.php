<?php

use CodeIgniter\Test\CIUnitTestCase;

final class StaffControllerStatsTest extends CIUnitTestCase
{
    public function testStatsReturnsSevenDayArrays()
    {
        // Prepare a fake authenticated staff session
        $_SESSION['isLoggedIn'] = true;
        $_SESSION['user_id'] = 2; // assume this exists in test DB
        $_SESSION['user_type'] = 'staff';

        // Call the staff stats route through CodeIgniter's router
        $result = $this->call('get', 'staff/stats');

        $this->assertEquals(200, $result->getStatusCode());

        $body = $result->getBody();
        $json = json_decode($body, true);

        $this->assertIsArray($json);
        $this->assertArrayHasKey('labels', $json);
        $this->assertArrayHasKey('counts', $json);
        $this->assertArrayHasKey('patientCounts', $json);
        $this->assertArrayHasKey('treatmentCounts', $json);

        // Should be 7 days
        $this->assertCount(7, $json['labels']);
        $this->assertCount(7, $json['counts']);
        $this->assertCount(7, $json['patientCounts']);
        $this->assertCount(7, $json['treatmentCounts']);

        // All values should be integers
        foreach (['counts','patientCounts','treatmentCounts'] as $k) {
            foreach ($json[$k] as $v) {
                $this->assertIsInt($v);
                $this->assertGreaterThanOrEqual(0, $v);
            }
        }
    }
}
