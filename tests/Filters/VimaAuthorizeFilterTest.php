<?php

namespace Vima\CodeIgniter\Tests\Filters;

use Vima\CodeIgniter\Tests\VimaTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Vima\CodeIgniter\Filters\VimaAuthorizeFilter;
use Vima\Core\Exceptions\AccessDeniedException;

class VimaAuthorizeFilterTest extends VimaTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        
        $config = config('Vima');
        $config->currentUser = fn() => new \Vima\CodeIgniter\Tests\Fixtures\User(1);
    }

    public function testAuthorizeFilterAllowsWhenPermitted()
    {
        vima_policy('test.perm', function($user) {
            return true;
        });

        $routes = service('routes');
        $routes->get('test-auth-ok', function () {
            return 'OK';
        }, ['filter' => 'vima_authorize:test.perm']);

        $result = $this->get('test-auth-ok');
        $result->assertStatus(200);
        $result->assertSee('OK');
    }

    public function testAuthorizeFilterThrowsExceptionWhenDenied()
    {
        vima_policy('test.deny', function($user) {
            return false;
        });

        $routes = service('routes');
        $routes->get('test-auth-fail', function () {
            return 'OK';
        }, ['filter' => 'vima_authorize:test.deny']);

        $this->expectException(AccessDeniedException::class);
        $this->get('test-auth-fail');
    }

    public function testAuthorizeFilterRedirectsWhenPageProvided()
    {
        vima_policy('test.deny', function($user) {
            return false;
        });

        $routes = service('routes');
        $routes->get('test-auth-redirect', function () {
            return 'OK';
        }, ['filter' => 'vima_authorize:test.deny,login']);
        $routes->get('login', function() { return 'Login Page'; });

        $result = $this->get('test-auth-redirect');
        $result->assertRedirectTo('login');
    }
}
