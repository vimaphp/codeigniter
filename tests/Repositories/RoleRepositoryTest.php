<?php

namespace Vima\CodeIgniter\Tests\Repositories;

use Vima\CodeIgniter\Repositories\RoleRepository;
use Vima\Core\Entities\Role;
use Vima\CodeIgniter\Tests\VimaTestCase;

class RoleRepositoryTest extends VimaTestCase
{
    protected RoleRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new RoleRepository();
    }

    public function testSaveAndFindRole()
    {
        $role = new Role(name: 'admin', description: 'Administrator');
        $savedRole = $this->repository->save($role);

        $this->assertNotNull($savedRole->id);
        $this->assertEquals('admin', $savedRole->name);

        $foundRole = $this->repository->findById($savedRole->id);
        $this->assertNotNull($foundRole);
        $this->assertEquals('admin', $foundRole->name);
    }

    public function testFindByName()
    {
        $role = new Role(name: 'editor');
        $this->repository->save($role);

        $foundRole = $this->repository->findByName('editor');
        $this->assertNotNull($foundRole);
        $this->assertEquals('editor', $foundRole->name);
    }

    public function testAllRoles()
    {
        $this->repository->save(new Role(name: 'role1'));
        $this->repository->save(new Role(name: 'role2'));

        $roles = $this->repository->all();
        $this->assertCount(2, $roles);
    }

    public function testDeleteRole()
    {
        $role = new Role(name: 'temporary');
        $this->repository->save($role);

        $this->repository->delete($role);
        $this->assertNull($this->repository->findByName('temporary'));
    }
}
