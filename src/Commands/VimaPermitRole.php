<?php

namespace Vima\CodeIgniter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class VimaPermitRole extends BaseCommand
{
    protected $group = 'Vima';
    protected $name = 'vima:permit-role';
    protected $description = 'Permit a role to perform a permission';
    protected $usage = 'vima:permit-role [role_name] [permission_name] [options] (You can pass namespaces to the role or permssion using \':\' to prefix the namespace)';
    protected $options = [
        'role' => 'The role to permit',
        'permission' => 'The permission to permit',
    ];

    public function run(array $params)
    {
        $role = $params[0] ?? CLI::getOption('role');
        $permission = $params[1] ?? CLI::getOption('permission');

        if (empty($role) || empty($permission)) {
            CLI::write('Role and permission are required.', 'red');
            return;
        }

        try {
            $role = service('vima_roles')->find($role);
            $permission = service('vima_permissions')->find($permission);

            if (empty($role) || empty($permission)) {
                CLI::write('Role or permission not found.', 'red');
                return;
            }

            $role->addPermission($permission);
            CLI::write('Role permitted to perform permission.', 'green');
        } catch (\Exception $e) {
            CLI::write($e->getMessage(), 'red');
        }
    }
}