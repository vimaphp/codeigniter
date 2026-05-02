<?php

namespace Vima\CodeIgniter\Tests\Traits;

use Vima\CodeIgniter\Repositories\PermissionRepository;
use Vima\CodeIgniter\Repositories\RoleRepository;
use Vima\CodeIgniter\Repositories\UserRoleRepository;
use Vima\CodeIgniter\Tests\VimaTestCase;
use Vima\CodeIgniter\Traits\VimaTrait;
use Vima\CodeIgniter\Tests\Fixtures\User;
use Vima\Core\Entities\Bare\BarePermission;
use Vima\Core\Entities\Bare\BareRole;
use Vima\Core\Entities\Bare\BareRolePermission;
use Vima\Core\Entities\Bare\BareUserRole;
use Vima\Core\Exceptions\AccessDeniedException;

class VimaTraitTest extends VimaTestCase
{
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();
        helper('vima');
        vima()->clearCache();

        $this->controller = new class {
            use VimaTrait;

            // Expose protected methods for testing
            public function testCan(string $permission, ...$arguments): bool
            {
                return $this->can($permission, ...$arguments);
            }

            public function testCanAny(array $permissions, ...$arguments): bool
            {
                return $this->can_any($permissions, ...$arguments);
            }

            public function testCanAll(array $permissions, ...$arguments): bool
            {
                return $this->can_all($permissions, ...$arguments);
            }

            public function testAuthorize(string $permission, ...$arguments): void
            {
                $this->authorize($permission, ...$arguments);
            }

            public function testAuthorizeAny(array $permissions, ...$arguments): void
            {
                $this->authorize_any($permissions, ...$arguments);
            }

            public function testAuthorizeAll(array $permissions, ...$arguments): void
            {
                $this->authorize_all($permissions, ...$arguments);
            }

            public function testDenyUser(string $permission, ?string $reason = null): void
            {
                $this->denyUser($permission, $reason);
            }

            public function testIsDenied(string $permission): bool
            {
                return $this->isDenied($permission);
            }
        };

        // Setup mock user
        $mockUser = new User(1);
        $config = config('Vima');
        $config->cacheEnabled = false; // Disable cache for this test
        $config->currentUser = function () use ($mockUser) {
            return $mockUser;
        };

        // Populate database with permissions and roles
        /**
         * @var PermissionRepository
         */
        $permRepo = service('vima_permissions');
        /**
         * @var RoleRepository
         */
        $roleRepo = service('vima_roles');
        /**
         * @var UserRoleRepository
         */
        $userRoleRepo = service('vima_user_roles');
        $rolePermRepo = service('vima_role_permissions');

        $editPerm = $permRepo->save(new BarePermission(name: 'edit.post'));
        $viewPerm = $permRepo->save(new BarePermission(name: 'view.post'));

        $role = $roleRepo->save(new BareRole(name: 'editor'));

        $rolePermRepo->assign(new BareRolePermission(
            role_id: $role->id,
            permission_id: $editPerm->id
        ));
        $rolePermRepo->assign(new BareRolePermission(
            role_id: $role->id,
            permission_id: $viewPerm->id
        ));

        $userRoleRepo->assign(new BareUserRole(user_id: 1, role_id: $role->id));
    }

    public function testCan()
    {
        $this->assertTrue($this->controller->testCan('edit.post'));
        $this->assertFalse($this->controller->testCan('delete.post'));
    }

    public function testCanAny()
    {
        $this->assertTrue($this->controller->testCanAny(['edit.post', 'delete.post']));
        $this->assertTrue($this->controller->testCanAny(['delete.post', 'edit.post']));
        $this->assertFalse($this->controller->testCanAny(['delete.post', 'other.post']));
    }

    public function testCanAll()
    {
        $this->assertTrue($this->controller->testCanAll(['edit.post']));
        $this->assertFalse($this->controller->testCanAll(['delete.post']));
        $this->assertFalse($this->controller->testCanAll(['edit.post', 'delete.post']));
    }

    public function testAuthorize()
    {
        $this->controller->testAuthorize('edit.post'); // Should not throw exception

        $this->expectException(AccessDeniedException::class);
        $this->controller->testAuthorize('delete.post');
    }

    public function testAuthorizeAny()
    {
        $this->controller->testAuthorizeAny(['edit.post', 'delete.post']); // Should not throw

        $this->expectException(AccessDeniedException::class);
        $this->controller->testAuthorizeAny(['delete.post', 'other.post']);
    }

    public function testAuthorizeAll()
    {
        $this->controller->testAuthorizeAll(['edit.post', 'view.post']); // Should not throw

        $this->expectException(AccessDeniedException::class);
        $this->controller->testAuthorizeAll(['edit.post', 'delete.post']);
    }


    public function testDenyUserAndIsDenied()
    {
        $this->assertFalse($this->controller->testIsDenied('edit.post'));
        $this->controller->testDenyUser('edit.post', 'No edits for you');
        $this->assertTrue($this->controller->testIsDenied('edit.post'));

        // Check that the permission is now actually denied via can()
        $this->assertFalse($this->controller->testCan('edit.post'));
    }
}
