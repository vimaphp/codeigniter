<?php

namespace Vima\CodeIgniter\Tests\Filters;

use CodeIgniter\Test\CIUnitTestCase;
use Vima\CodeIgniter\Filters\VimaSetupFilter;
use Vima\CodeIgniter\Tests\VimaTestCase;
use Config\Services;
use Vima\Core\Contracts\PolicyInterface;

class MockResource
{
}
class MockPolicy implements PolicyInterface
{
    public static function getResource(): string
    {
        return MockResource::class;
    }
    public function canEdit()
    {
        return true;
    }
}

class VimaSetupFilterTest extends VimaTestCase
{
    public function testBeforeRegistersPoliciesFromConfig()
    {
        \Vima\CodeIgniter\Filters\VimaSetupFilter::reset();
        $config = config('Vima');
        $config->policies = [MockPolicy::class];

        $filter = new VimaSetupFilter();
        $filter->before(Services::request());

        $vima = service('vima');
        $post = new MockResource();

        // Use valid User fixture
        $this->assertTrue($vima->can(new \Vima\CodeIgniter\Tests\Fixtures\User(1), 'edit', $post));
    }

    public function testBeforeIgnoresInvalidPolicies()
    {
        \Vima\CodeIgniter\Filters\VimaSetupFilter::reset();
        $config = config('Vima');
        $config->policies = [\stdClass::class]; // Doesn't implement PolicyInterface

        $filter = new VimaSetupFilter();
        $filter->before(Services::request());

        $vima = service('vima');

        // Should return false because stdClass has no policy registered
        $this->assertFalse($vima->can(new \Vima\CodeIgniter\Tests\Fixtures\User(1), 'edit', new \stdClass()));
    }
}
