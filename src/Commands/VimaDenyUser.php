<?php

namespace Vima\CodeIgniter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Vima\CodeIgniter\Support\Utils;

class VimaDenyUser extends BaseCommand
{
    protected $group = 'Vima';
    protected $name = 'vima:deny-user';
    protected $description = 'Explicitly deny a permission to a user';
    protected $usage = 'vima:deny-user [user_id] [permission_name] [reason]';

    public function run(array $params)
    {
        $user_id = $params[0] ?? CLI::prompt('User ID', null, 'required');
        $permission = $params[1] ?? CLI::prompt('Permission', null, 'required');
        $reason = $params[2] ?? CLI::prompt('Reason (Optional)', '');

        if (empty($user_id) || empty($permission)) {
            CLI::error('User ID and Permission are required.');
            return;
        }

        $user = Utils::creatVimaUser($user_id);

        try {
            service('vima')->deny($user, $permission, $reason);
            CLI::write("Permission [{$permission}] explicitly denied to user [{$user_id}] with reason: {$reason}", 'green');
        } catch (\Exception $e) {
            CLI::error($e->getMessage());
        }
    }
}
