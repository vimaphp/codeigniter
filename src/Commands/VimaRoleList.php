<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/vimaphp>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Vima\CodeIgniter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

use Vima\CodeIgniter\Support\Utils;
use Vima\Core\Contracts\RoleRepositoryInterface;

class VimaRoleList extends BaseCommand
{
    protected $group = 'Vima';
    protected $name = 'vima:role-list';
    protected $description = 'List all roles';
    protected $usage = 'vima:role-list [options]';
    protected $options = [
        'limit' => 'Limit the number of characters in list columns (default: 30)',
        'resolve' => 'Resolve role parents and children',
    ];

    public function run(array $params)
    {
        $limit = (int) (isset($params['limit']) ? $params['limit'] : (CLI::getOption('limit') ?? 30));
        $resolve = (bool) (isset($params['resolve']) ? $params['resolve'] : (CLI::getOption('resolve') ?? false));

        /** @var RoleRepositoryInterface $roleRepo */
        $roleRepo = service('vima_roles');
        $roles = $roleRepo->all(resolve: $resolve);


        if (empty($roles)) {
            CLI::write('No roles found.', 'yellow');
            return;
        }

        $body = [];
        foreach ($roles as $role) {
            $parents = implode(', ', array_map(fn($r) => $r->namespace ? "{$r->namespace}:{$r->name}" : $r->name, $role->parents)) ?: '[--NONE--]';
            $children = implode(', ', array_map(fn($r) => $r->namespace ? "{$r->namespace}:{$r->name}" : $r->name, $role->children)) ?: '[--NONE--]';
            $permissions = implode(', ', array_map(fn($p) => $p->namespace ? "{$p->namespace}:{$p->name}" : $p->name, $role->permissions)) ?: '[--NONE--]';

            $row = [
                $role->id,
                $role->namespace ?? '[--GLOBAL--]',
                $role->name,
                Utils::truncate($role->description, $limit),
                Utils::truncate(json_encode($role->context), $limit),
                Utils::truncate($permissions, $limit)
            ];

            if ($resolve) {
                $row = [
                    ...$row,
                    ...[
                        Utils::truncate($parents, $limit),
                        Utils::truncate($children, $limit)
                    ]
                ];
            }

            $body[] = $row;
        }

        $thead = ['ID', 'Namespace', 'Name', 'Description', 'Context', 'Permissions'];

        if ($resolve) {
            $thead[] = 'Parents';
            $thead[] = 'Children';
        }

        CLI::table($body, $thead);
    }
}
