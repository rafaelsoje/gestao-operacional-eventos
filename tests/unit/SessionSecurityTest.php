<?php

use App\Filters\AuthFilter;
use App\Filters\RoleFilter;
use App\Models\Users;
use CodeIgniter\Test\CIUnitTestCase;

final class SessionSecurityTest extends CIUnitTestCase
{
    private int $userId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('SQLite3', db_connect()->DBDriver);
        $forge = \Config\Database::forge();
        $forge->dropTable('users', true);
        $forge->addField(['id' => ['type' => 'INTEGER', 'auto_increment' => true]]);
        foreach (['name', 'email', 'password', 'role', 'admin', 'must_change_password'] as $field) {
            $forge->addField([$field => ['type' => 'TEXT', 'null' => true]]);
        }
        $forge->addKey('id', true)->createTable('users');
        $hash = password_hash('Test-password-123', PASSWORD_DEFAULT);
        $this->userId = (int)(new Users())->insert([
            'name' => 'Test Admin', 'email' => 'session-test@example.invalid',
            'password' => $hash, 'role' => 'admin', 'admin' => 1, 'must_change_password' => 0,
        ]);
        session()->set([
            'auth' => true, 'admin' => true,
            'user' => (object)['id' => $this->userId, 'role' => 'admin'],
            'password_fingerprint' => hash('sha256', $hash),
        ]);
    }

    protected function tearDown(): void
    {
        session()->remove(['auth', 'admin', 'user', 'password_fingerprint']);
        \Config\Database::forge()->dropTable('users', true);
        parent::tearDown();
    }

    public function testDeletedAccountLosesAccess(): void
    {
        (new Users())->delete($this->userId);
        $response = $this->check('/usuarios');
        $this->assertStringEndsWith('/login', $response->getHeaderLine('Location'));
        $this->assertFalse(session()->has('auth'));
    }

    public function testDemotedAdminIsDeniedByRoleFilter(): void
    {
        (new Users())->update($this->userId, ['role' => 'user', 'admin' => 0]);
        $this->assertNull($this->check('/usuarios'));
        $this->assertSame('user', session()->get('user')->role);
        $this->assertFalse(session()->has('admin'));
        $this->assertNotNull((new RoleFilter())->before(service('request'), ['admin']));
        $this->assertObjectNotHasProperty('password', session()->get('user'));
    }

    public function testPasswordResetRevokesExistingSession(): void
    {
        (new Users())->update($this->userId, ['password' => password_hash('Changed-password-123', PASSWORD_DEFAULT)]);
        $this->assertNotNull($this->check('/'));
        $this->assertFalse(session()->has('auth'));
    }

    public function testLegacySessionMustLogInAgain(): void
    {
        session()->remove('password_fingerprint');
        $this->assertNotNull($this->check('/'));
        $this->assertFalse(session()->has('auth'));
    }

    public function testForcedPasswordChangeBlocksDirectAccessAndPost(): void
    {
        (new Users())->update($this->userId, ['must_change_password' => 1]);
        foreach (['GET', 'POST'] as $method) {
            service('request')->setMethod($method);
            $response = $this->check('/cadastro/operacional');
            $this->assertStringEndsWith('/password/force', $response->getHeaderLine('Location'));
        }
        $this->assertNull($this->check('/password/force'));
    }

    public function testForcedPasswordFormWorksUnderSubdirectory(): void
    {
        (new Users())->update($this->userId, ['must_change_password' => 1]);
        $config = config('App');
        $original = $config->baseURL;
        try {
            $config->baseURL = 'http://example.com/audiovisual/';
            $this->assertNull($this->check('/audiovisual/password/force'));
            $this->assertNotNull($this->check('/audiovisual/usuarios'));
        } finally {
            $config->baseURL = $original;
        }
    }

    private function check(string $path): ?\CodeIgniter\HTTP\ResponseInterface
    {
        $request = service('request');
        $request->getUri()->setPath($path);
        return (new AuthFilter())->before($request);
    }
}
