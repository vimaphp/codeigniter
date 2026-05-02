<?php

namespace Vima\CodeIgniter\Tests\Commands;

use Vima\CodeIgniter\Repositories\PermissionRepository;
use Vima\CodeIgniter\Repositories\UserPermissionRepository;
use Vima\CodeIgniter\Tests\VimaTestCase;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\Mock\MockInputOutput;
use Vima\Core\Entities\Bare\BarePermission;

class VimaPermitUserTest extends VimaTestCase
{
    public function testPermitUser()
    {
        /**
         * @var PermissionRepository $permRepo
         */
        $permRepo = service('vima_permissions');
        $permRepo->save(new BarePermission(name: 'extra.perm'));

        $io = new MockInputOutput();
        CLI::setInputOutput($io);

        command('vima:permit-user 1 extra.perm');

        /**
         * @var UserPermissionRepository 
         */
        $userPermRepo = service('vima_user_permissions');
        $userPerms = $userPermRepo->findByUserId(1);
        $perm = $permRepo->findById($userPerms[0]->permission_id);

        $this->assertCount(1, $userPerms);
        $this->assertEquals('extra.perm', $perm->name);

        CLI::resetInputOutput();
    }
}
