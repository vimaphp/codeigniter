<?php

namespace Vima\CodeIgniter\Tests\Helpers;

use Vima\CodeIgniter\Tests\VimaTestCase;
use Config\Services;
use Vima\CodeIgniter\Tests\Fixtures\User;
use Vima\Core\Entities\Permission;
use Vima\Core\Entities\Role;

class NamespaceHelperTest extends VimaTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('vima');
        
        // Setup mock user for 'can' helper
        $mockUser = new User(1);
        $config = config('Vima');
        $config->currentUser = function () use ($mockUser) {
            return $mockUser;
        };
    }

    public function testCanResolvesNamespaceFromPermissionString()
    {
        $user = call_user_func(config('Vima')->currentUser);
        $manager = vima();
        
        // Define namespaced permission
        $p = Permission::define("edit", namespace: "blog");
        $manager->ensurePermission($p);
        
        $role = Role::define(name: "editor", permissions: [$p], namespace: "blog");
        $manager->ensureRole($role);
        $manager->assignRole($user, $role);
        
        // Test with blog:edit
        $this->assertTrue(can('blog:edit'));
        
        // Test with other:edit (should fail)
        $this->assertFalse(can('other:edit'));
    }

    public function testCanUsesNamespaceAsSecondArgument()
    {
        $user = call_user_func(config('Vima')->currentUser);
        $manager = vima();
        
        $p = Permission::define("edit", namespace: "admin");
        $manager->ensurePermission($p);
        
        $role = Role::define(name: "super", permissions: [$p], namespace: "admin");
        $manager->ensureRole($role);
        $manager->assignRole($user, $role);
        
        // Test passing namespace explicitly
        $this->assertTrue(can('edit', 'admin'));
        
        // Test passing wrong namespace
        $this->assertFalse(can('edit', 'standard'));
    }
}
