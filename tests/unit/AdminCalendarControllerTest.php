<?php
namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Controllers\AdminCalendarController;

class AdminCalendarControllerTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Ensure no user in session by default
        $_SESSION = [];
    }

    public function testEnsureAdminFalseWhenNoUser()
    {
        $ctrl = new AdminCalendarController();
        $ref = new \ReflectionClass($ctrl);
        $method = $ref->getMethod('ensureAdmin');
        $method->setAccessible(true);
        $this->assertFalse($method->invoke($ctrl));
    }

    public function testEnsureAdminTrueWhenAdmin()
    {
        $_SESSION['user'] = ['id' => 1, 'user_type' => 'admin'];
        $ctrl = new AdminCalendarController();
        $ref = new \ReflectionClass($ctrl);
        $method = $ref->getMethod('ensureAdmin');
        $method->setAccessible(true);
        $this->assertTrue($method->invoke($ctrl));
    }
}
