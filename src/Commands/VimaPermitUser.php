<?php

namespace Vima\CodeIgniter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Vima\CodeIgniter\Support\Utils;
use Vima\Core\Entities\UserPermission;

class VimaPermitUser extends BaseCommand
{
    protected $group = 'Vima';
    protected $name = 'vima:permit-user';
    protected $description = 'Permit a user to perform a permission';
    protected $usage = 'vima:permit-user [user_id] [permission_name] [options] (You can pass namespaces to the role or permssion using \':\' to prefix the namespace)';
    protected $options = [];

    public function run(array $params)
    {
        $user_id = $params[0] ?? CLI::prompt('User ID', null, 'required');
        $permission = $params[1] ?? CLI::prompt('Permission', null, 'required');

        if (empty($user_id) || empty($permission)) {
            CLI::write('User and permission are required.', 'red');
            return;
        }

        $user = Utils::creatVimaUser($user_id);

        try {
            $permission = service('vima_permissions')->find($permission);

            if (empty($permission)) {
                CLI::write('Permission not found.', 'red');
                return;
            }

            $userPermission = UserPermission::define(
                $user->id,
                $permission->id
            );

            service('vima_user_permissions')->save($userPermission);

            CLI::write('User permitted to perform permission.', 'green');
        } catch (\Exception $e) {
            CLI::write($e->getMessage(), 'red');
        }
    }
}