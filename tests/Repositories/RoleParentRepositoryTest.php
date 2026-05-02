<?php

namespace Vima\CodeIgniter\Tests\Repositories;

use Vima\CodeIgniter\Repositories\RoleParentRepository;
use Vima\CodeIgniter\Repositories\RoleRepository;
use Vima\Core\Entities\Bare\BareRole;
use Vima\Core\Entities\Bare\BareRoleParent;
use Vima\CodeIgniter\Tests\VimaTestCase;

class RoleParentRepositoryTest extends VimaTestCase
{
    protected RoleParentRepository $repository;
    protected RoleRepository $roleRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new RoleParentRepository();
        $this->roleRepository = new RoleRepository();
    }

    public function testAssignAndGetParents()
    {
        $child = $this->roleRepository->save(new BareRole(name: 'editor'));
        $parent = $this->roleRepository->save(new BareRole(name: 'viewer'));

        $this->repository->assign(new BareRoleParent(role_id: $child->id, parent_id: $parent->id));

        $parents = $this->repository->getParents($child);
        $rParent = $this->roleRepository->findById($parents[0]->parent_id);

        $this->assertCount(1, $parents);
        $this->assertEquals($parent->id, $rParent->id);
    }

    public function testGetChildren()
    {
        $child = $this->roleRepository->save(new BareRole(name: 'editor'));
        $parent = $this->roleRepository->save(new BareRole(name: 'viewer'));

        $this->repository->assign(new BareRoleParent(role_id: $child->id, parent_id: $parent->id));

        $children = $this->repository->getChildren($parent);
        $this->assertCount(1, $children);
        $this->assertEquals($child->id, $children[0]->id);
    }

    public function testRevokeParent()
    {
        $child = $this->roleRepository->save(new BareRole(name: 'editor'));
        $parent = $this->roleRepository->save(new BareRole(name: 'viewer'));
        $roleParent = new BareRoleParent(role_id: $child->id, parent_id: $parent->id);

        $this->repository->assign($roleParent);
        $this->assertCount(1, $this->repository->getParents($child));

        $this->repository->remove($roleParent);
        $this->assertCount(0, $this->repository->getParents($child));
    }
}
