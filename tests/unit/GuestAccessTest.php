<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class GuestAccessTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testLoginIsAvailable(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    public function testLoginCannotBeStoredInHttpCache(): void
    {
        $response = $this->get('/login')->response();
        $directives = array_map('trim', explode(',', $response->getHeaderLine('Cache-Control')));
        $this->assertContains('no-store', $directives);
        $this->assertContains('private', $directives);
    }

    public function testGuestCannotAccessAdminUsers(): void
    {
        $this->get('/usuarios')->assertRedirect();
    }

    public function testGuestCannotAccessFinancialSettings(): void
    {
        $this->get('/assinatura')->assertRedirect();
    }
    public function testGuestCannotAccessVisitas(): void
    {
        $this->get('/visitas')->assertRedirect();
    }

    public function testGuestCannotAccessOperationalDashboard(): void
    {
        $this->get('/')->assertRedirect();
    }

    public function testGuestCannotAccessOperationalList(): void
    {
        $this->get('/operacional/lista')->assertRedirect();
    }

    public function testGuestCannotAccessGeradores(): void
    {
        $this->get('/operacional/geradores')->assertRedirect();
    }

    public function testGuestCannotAccessTaxaCozinha(): void
    {
        $this->get('/operacional/taxa-cozinha')->assertRedirect();
    }

    public function testGuestCannotAccessProfile(): void
    {
        $this->get('/perfil')->assertRedirect();
    }

    public function testGuestCannotAccessLogs(): void
    {
        $this->get('/logs')->assertRedirect();
    }
}
