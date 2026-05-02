<?php

namespace Vima\CodeIgniter\Tests\Commands;

use Vima\CodeIgniter\Models\UserRoleModel;
use Vima\CodeIgniter\Repositories\RoleRepository;
use Vima\CodeIgniter\Tests\VimaTestCase;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\Mock\MockInputOutput;
use Vima\Core\Entities\Bare\BareRole;

class VimaAssignRoleTest extends VimaTestCase
{
    public function testAssignRoleToUser()
    {
        /**
         * @var RoleRepository
         */
        $roleRepo = service('vima_roles');
        $roleRepo->save(new BareRole(name: 'admin'));

        $io = new MockInputOutput();
        CLI::setInputOutput($io);

        command('vima:assign-role 1 admin');

        $userRoleRepo = service('vima_user_roles');
        $roles = $userRoleRepo->getRolesForUser(1);
        $role = $roleRepo->findById($roles[0]->role_id);

        $this->assertCount(1, $roles);
        $this->assertEquals('admin', $role->name);

        CLI::resetInputOutput();
    }
}
