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
    protected $options = [];

    public function run(array $params)
    {
        $role = $params[0] ?? CLI::prompt('Role name', null, 'required');
        $permission = $params[1] ?? CLI::prompt('Permission name', null, 'required');

        if (empty($role) || empty($permission)) {
            CLI::write('Role and permission are required.', 'red');
            return;
        }

        try {
            $role = service('vima')->getRole($role);
            $permission = service('vima')->getPermission($permission);

            if (empty($role) || empty($permission)) {
                CLI::write('Role or permission not found.', 'red');
                return;
            }

            $role->permit($permission);
            $role->save();
            CLI::write('Role permitted to perform permission.', 'green');
        } catch (\Exception $e) {
            CLI::write($e->getMessage(), 'red');
        }
    }
}