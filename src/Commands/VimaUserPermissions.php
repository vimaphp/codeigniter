<?php

namespace Vima\CodeIgniter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Vima\CodeIgniter\Support\Utils;

class VimaUserPermissions extends BaseCommand
{
    protected $group = 'Vima';
    protected $name = 'vima:user-permissions';
    protected $description = 'List all user permissions';
    protected $usage = 'vima:user-permissions [user_id] [options]';
    protected $options = [
        'limit' => 'Limit the number of characters in list columns (default: 30)',
    ];

    public function run(array $params)
    {
        $user_id = $params[0] ?? CLI::prompt('User ID', null, 'required');
        $limit = (int) (isset($params['limit']) ? $params['limit'] : (CLI::getOption('limit') ?? 30));

        if (empty($user_id)) {
            CLI::write('User ID is required.', 'red');
            return;
        }

        $user = Utils::creatVimaUser($user_id);

        CLI::write('Shwoing permissions for user: ' . $user->id, 'green');

        try {
            $permissions = service('vima')->getUserPermissions($user);

            $body = [];
            foreach ($permissions as $perm) {
                $body[] = [
                    $perm->id,
                    $perm->namespace ?? '[--GLOBAL--]',
                    $perm->name,
                    Utils::truncate($perm->description, $limit),
                ];
            }

            CLI::table($body, ['ID', 'Namespace', 'Name', 'Description']);
        } catch (\Exception $e) {
            CLI::write($e->getMessage(), 'red');
        }
    }
}