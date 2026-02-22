<?php

namespace Vima\CodeIgniter\Tests\Repositories;

use Vima\CodeIgniter\Repositories\PermissionRepository;
use Vima\Core\Entities\Permission;
use Vima\CodeIgniter\Tests\VimaTestCase;

class PermissionRepositoryTest extends VimaTestCase
{
    protected PermissionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PermissionRepository();
    }

    public function testSaveAndFindPermission()
    {
        $permission = new Permission(name: 'users.create', description: 'Can create users');
        $savedPermission = $this->repository->save($permission);

        $this->assertNotNull($savedPermission->id);
        $this->assertEquals('users.create', $savedPermission->name);

        $foundPermission = $this->repository->findById($savedPermission->id);
        $this->assertNotNull($foundPermission);
        $this->assertEquals('users.create', $foundPermission->name);
    }

    public function testFindByName()
    {
        $permission = new Permission(name: 'users.edit');
        $this->repository->save($permission);

        $foundPermission = $this->repository->findByName('users.edit');
        $this->assertNotNull($foundPermission);
        $this->assertEquals('users.edit', $foundPermission->name);
    }

    public function testDeletePermission()
    {
        $permission = new Permission(name: 'temporary');
        $this->repository->save($permission);

        $this->repository->delete($permission);
        $this->assertNull($this->repository->findByName('temporary'));
    }
}
