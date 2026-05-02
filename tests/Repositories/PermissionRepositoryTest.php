<?php

namespace Vima\CodeIgniter\Tests\Repositories;

use Vima\CodeIgniter\Repositories\PermissionRepository;
use Vima\Core\Entities\Bare\BarePermission;
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
        $permission = new BarePermission(name: 'users.create', description: 'Can create users');
        $savedPermission = $this->repository->save($permission);

        $this->assertNotNull($savedPermission->id);
        $this->assertEquals('users.create', $savedPermission->name);

        $foundPermission = $this->repository->findById($savedPermission->id);
        $this->assertNotNull($foundPermission);
        $this->assertEquals('users.create', $foundPermission->name);
    }

    public function testFindByName()
    {
        $permission = new BarePermission(name: 'users.edit');
        $this->repository->save($permission);

        $foundPermission = $this->repository->findByName('users.edit');
        $this->assertNotNull($foundPermission);
        $this->assertEquals('users.edit', $foundPermission->name);
    }

    public function testDeletePermission()
    {
        $permission = new BarePermission(name: 'temporary');
        $this->repository->save($permission);

        $this->repository->delete($permission);
        $this->assertNull($this->repository->findByName('temporary'));
    }

    public function testSaveDuplicateNameUpdatesExisting()
    {
        $p1 = new BarePermission(name: 'posts.create', namespace: 'blog', description: 'Old desc');
        $this->repository->save($p1);

        $p2 = new BarePermission(name: 'posts.create', namespace: 'blog', description: 'New desc');
        $saved = $this->repository->save($p2);

        $this->assertEquals($p1->id, $saved->id);
        $this->assertEquals('New desc', $saved->description);
        
        $tableName = service('vima_config')->tables->permissions;
        $count = $this->db->table($tableName)->where('name', 'posts.create')->where('namespace', 'blog')->countAllResults();
        $this->assertEquals(1, $count);
    }
}
