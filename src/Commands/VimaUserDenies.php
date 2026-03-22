<?php

namespace Vima\CodeIgniter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Vima\CodeIgniter\Support\Utils;

class VimaUserDenies extends BaseCommand
{
    protected $group = 'Vima';
    protected $name = 'vima:user-denies';
    protected $description = 'List all explicit permission denials for a user';
    protected $usage = 'vima:user-denies [user_id]';
    protected $options = [
        'limit' => 'Limit the number of characters in list columns (default: 30)',
    ];

    public function run(array $params)
    {
        $user_id = $params[0] ?? CLI::prompt('User ID', null, 'required');
        $limit = (int) (isset($params['limit']) ? $params['limit'] : (CLI::getOption('limit') ?? 30));

        if (empty($user_id)) {
            CLI::error('User ID is required.');
            return;
        }

        $user = Utils::creatVimaUser($user_id);

        CLI::write("Permission Denials for User: " . $user->id, 'yellow');

        try {
            /** @var \Vima\Core\Contracts\AccessManagerInterface $vima */
            $vima = service('vima');
            $denies = $vima->getDeniedPermissions($user);

            if (empty($denies)) {
                CLI::write('No explicit denials found for this user.', 'green');
                return;
            }

            $body = [];
            foreach ($denies as $deny) {
                $perm = $deny->permission;
                $body[] = [
                    $deny->id,
                    $perm ? ($perm->namespace ?? '[--GLOBAL--]') : 'N/A',
                    $perm ? $perm->name : $deny->permission_id,
                    $perm ? Utils::truncate($perm->description, $limit) : 'N/A',
                    Utils::truncate($deny->reason, $limit),
                ];
            }

            CLI::table($body, ['ID', 'Namespace', 'Name', 'Description', 'Reason']);
        } catch (\Exception $e) {
            CLI::error($e->getMessage());
        }
    }
}
