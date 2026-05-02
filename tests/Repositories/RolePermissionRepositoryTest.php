<?php

namespace Vima\CodeIgniter\Tests\Repositories;

use Vima\CodeIgniter\Repositories\RolePermissionRepository;
use Vima\CodeIgniter\Repositories\RoleRepository;
use Vima\CodeIgniter\Repositories\PermissionRepository;
use Vima\Core\Entities\Bare\BareRole;
use Vima\Core\Entities\Bare\BarePermission;
use Vima\Core\Entities\Bare\BareRolePermission;
use Vima\CodeIgniter\Tests\VimaTestCase;

class RolePermissionRepositoryTest extends VimaTestCase
{
    protected RolePermissionRepository $repository;
    protected RoleRepository $roleRepository;
    protected PermissionRepository $permissionRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new RolePermissionRepository();
        $this->roleRepository = new RoleRepository();
        $this->permissionRepository = new PermissionRepository();
    }

    public function testAssignAndGetRolePermissions()
    {
        $role = $this->roleRepository->save(new BareRole(name: 'editor'));
        $perm1 = $this->permissionRepository->save(new BarePermission(name: 'posts.create'));
        $perm2 = $this->permissionRepository->save(new BarePermission(name: 'posts.edit'));

        $this->repository->assign(new BareRolePermission(role_id: $role->id, permission_id: $perm1->id));
        $this->repository->assign(new BareRolePermission(role_id: $role->id, permission_id: $perm2->id));

        $rolePermissions = $this->repository->getRolePermissions($role);
        $this->assertCount(2, $rolePermissions);
    }

    public function testRevokePermission()
    {
        $role = $this->roleRepository->save(new BareRole(name: 'editor'));
        $perm = $this->permissionRepository->save(new BarePermission(name: 'posts.create'));
        $rolePerm = new BareRolePermission(role_id: $role->id, permission_id: $perm->id);

        $this->repository->assign($rolePerm);
        $this->assertCount(1, $this->repository->getRolePermissions($role));

        $this->repository->revoke($rolePerm);
        $this->assertCount(0, $this->repository->getRolePermissions($role));
    }

    public function testGetPermissionRoles()
    {
        $role1 = $this->roleRepository->save(new BareRole(name: 'admin'));
        $role2 = $this->roleRepository->save(new BareRole(name: 'editor'));
        $perm = $this->permissionRepository->save(new BarePermission(name: 'shared.perm'));

        $this->repository->assign(new BareRolePermission(role_id: $role1->id, permission_id: $perm->id));
        $this->repository->assign(new BareRolePermission(role_id: $role2->id, permission_id: $perm->id));

        $permissionRoles = $this->repository->getPermissionRoles($perm);
        $this->assertCount(2, $permissionRoles);
    }
}
