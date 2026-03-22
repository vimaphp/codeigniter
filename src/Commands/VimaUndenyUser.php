<?php

namespace Vima\CodeIgniter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Vima\CodeIgniter\Support\Utils;

class VimaUndenyUser extends BaseCommand
{
    protected $group = 'Vima';
    protected $name = 'vima:undeny-user';
    protected $description = 'Remove an explicit denial from a user';
    protected $usage = 'vima:undeny-user [user_id] [permission_name]';

    public function run(array $params)
    {
        $user_id = $params[0] ?? CLI::prompt('User ID', null, 'required');
        $permission = $params[1] ?? CLI::prompt('Permission', null, 'required');

        if (empty($user_id) || empty($permission)) {
            CLI::error('User ID and Permission are required.');
            return;
        }

        $user = Utils::creatVimaUser($user_id);

        try {
            service('vima')->undeny($user, $permission);
            CLI::write("Permission [{$permission}] denial removed for user [{$user_id}].", 'green');
        } catch (\Exception $e) {
            CLI::error($e->getMessage());
        }
    }
}
