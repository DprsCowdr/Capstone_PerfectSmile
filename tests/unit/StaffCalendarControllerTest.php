<?php
namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Controllers\StaffCalendarController;

class StaffCalendarControllerTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
    }

    public function testEnsureStaffFalseWhenNoUser()
    {
        $ctrl = new StaffCalendarController();
        $ref = new \ReflectionClass($ctrl);
        $method = $ref->getMethod('ensureStaff');
        $method->setAccessible(true);
        $this->assertFalse($method->invoke($ctrl));
    }

    public function testEnsureStaffTrueForStaff()
    {
        $_SESSION['user'] = ['id' => 2, 'user_type' => 'staff'];
        $ctrl = new StaffCalendarController();
        $ref = new \ReflectionClass($ctrl);
        $method = $ref->getMethod('ensureStaff');
        $method->setAccessible(true);
        $this->assertTrue($method->invoke($ctrl));
    }
}
