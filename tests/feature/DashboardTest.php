<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class DashboardTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testAdminDashboardIsAvailable(): void
    {
        $result = $this->get('/admin');

        $result->assertStatus(200);
        $result->assertSee('Panel administrativo');
        $result->assertSee('Próximas funciones');
    }
}
