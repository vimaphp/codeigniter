<?php

namespace Vima\CodeIgniter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Vima\CodeIgniter\Support\Utils;

class VimaUndenyUser extends BaseCommand
{
    protected $group = 'Vima';
    protected $name = 'vima:undeny';
    protected $description = 'Remove an explicit denial from a user';
    protected $usage = 'vima:undeny [user_id] [permission_or_role] [options]';
    protected $options = [
        '--role' => 'Remove a role denial instead of a permission denial',
    ];

    public function run(array $params)
    {
        $user_id = $params[0] ?? null;
        if (empty($user_id)) {
            $user_id = CLI::prompt('User ID', null, 'required');
        }

        $target = $params[1] ?? null;
        if (empty($target)) {
            $target = CLI::prompt('Permission/Role Name', null, 'required');
        }

        $isRole = isset($params['role']) || CLI::getOption('role') !== null;

        if (empty($user_id) || empty($target)) {
            CLI::error('User ID and target name are required.');
            return;
        }

        $user = Utils::creatVimaUser($user_id);

        try {
            $vima = service('vima');
            if ($isRole) {
                $vima->undenyRole($user, $target);
                CLI::write("Role [{$target}] denial removed for user [{$user_id}].", 'green');
            } else {
                $vima->undeny($user, $target);
                CLI::write("Permission [{$target}] denial removed for user [{$user_id}].", 'green');
            }
        } catch (\Exception $e) {
            CLI::error($e->getMessage());
        }
    }
}
