<?php

declare(strict_types=1);

namespace Vima\CodeIgniter\Tests\Commands;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\Mock\MockInputOutput;
use Config\Services;
use Vima\CodeIgniter\Commands\VimaSync;
use Vima\CodeIgniter\Tests\Fixtures\Setup;
use Vima\CodeIgniter\Tests\VimaTestCase;
use Vima\Core\Config\VimaConfig;
use Vima\Core\Entities\Bare\BareRole;

final class VimaSyncTest extends VimaTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $setup = new Setup();

        $config = new VimaConfig();

        $config->setup->roles = $setup->get()['roles'];
        $config->setup->permissions = $setup->get()['permissions'];

        Services::injectMock('vima_config', $config);

        $io = new MockInputOutput();

        CLI::setInputOutput($io);

        command('vima:sync');

        CLI::resetInputOutput();
    }

    public function test_sync_service(): void
    {
        $this->assertTrue(true);

        $roles = vima()->getRoles(resolve: true);

        $this->assertCount(1, $roles);
        $this->assertEquals('admin', $roles[0]->name);
        $this->assertEquals('can access everything', $roles[0]->description);
        $this->assertCount(1, $roles[0]->permissions);
        $this->assertEquals('test.view', $roles[0]->permissions[0]->name);
        $this->assertEquals('This is a permssion', $roles[0]->permissions[0]->description);
    }

    public function test_sync_service_with_refresh(): void
    {
        // Add a dummy role to ensure it gets wiped out by refresh
        $roleRepo = service('vima_roles');
        $roleRepo->save(new BareRole(name: 'dummy_role'));

        $io = new MockInputOutput();
        CLI::setInputOutput($io);

        $command = new VimaSync(service('logger'), service('commands'));
        $command->run(['refresh' => true]);

        CLI::resetInputOutput();

        $roles = vima()->getRoles();

        // Ensure the dummy role is gone, leaving only the 'admin' from config
        $this->assertCount(1, $roles);
        $this->assertEquals('admin', $roles[0]->name);
    }
}
