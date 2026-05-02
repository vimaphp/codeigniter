<?php

namespace Vima\CodeIgniter\Tests\Repositories;

use Vima\CodeIgniter\Repositories\UserRoleRepository;
use Vima\CodeIgniter\Repositories\RoleRepository;
use Vima\Core\Entities\Bare\BareRole;
use Vima\Core\Entities\Bare\BareUserRole;
use Vima\CodeIgniter\Tests\VimaTestCase;

class UserRoleRepositoryTest extends VimaTestCase
{
    protected UserRoleRepository $repository;
    protected RoleRepository $roleRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserRoleRepository();
        $this->roleRepository = new RoleRepository();
    }

    public function testAssignAndGetRolesForUser()
    {
        $role1 = $this->roleRepository->save(new BareRole(name: 'admin'));
        $role2 = $this->roleRepository->save(new BareRole(name: 'editor'));

        $this->repository->assign(new BareUserRole(user_id: 1, role_id: $role1->id));
        $this->repository->assign(new BareUserRole(user_id: 1, role_id: $role2->id));

        $roles = $this->repository->getRolesForUser(1);
        $this->assertCount(2, $roles);

        $roleIds = array_map(fn($r) => $r->role_id, $roles);
        $this->assertContains($role1->id, $roleIds);
        $this->assertContains($role2->id, $roleIds);
    }

    public function testRevokeRole()
    {
        $role = $this->roleRepository->save(new BareRole(name: 'admin'));
        $userRole = new BareUserRole(user_id: 1, role_id: $role->id);

        $this->repository->assign($userRole);
        $this->assertCount(1, $this->repository->getRolesForUser(1));

        $this->repository->revoke($userRole);
        $this->assertCount(0, $this->repository->getRolesForUser(1));
    }

    public function testAssignDuplicateDoesNothing()
    {
        $role = $this->roleRepository->save(new BareRole(name: 'admin'));
        $userRole = new BareUserRole(user_id: 1, role_id: $role->id);

        $this->repository->assign($userRole);
        $this->repository->assign($userRole);

        $this->assertCount(1, $this->repository->getRolesForUser(1));
    }
}
