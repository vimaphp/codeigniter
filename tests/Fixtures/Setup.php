<?php

namespace Vima\CodeIgniter\Tests\Fixtures;

use Vima\Core\Contracts\SetupProviderInterface;
use Vima\Core\Entities\Permission;
use Vima\Core\Entities\Role;

final class Setup implements SetupProviderInterface
{
    public function get(): array
    {
        return [
            'roles' => [
                Role::define('admin', ['*'], 'can access everything')
            ],
            'permissions' => [
                Permission::define('test.view', 'This is a permssion')
            ]
        ];
    }
}