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

use Vima\Core\Contracts\RoleRepositoryInterface;

class VimaRoleList extends BaseCommand
{
    protected $group = 'Vima';
    protected $name = 'vima:role-list';
    protected $description = 'List all roles';
    protected $usage       = 'vima:role-list [options]';
    protected $options     = [
        'limit' => 'Limit the number of characters in list columns (default: 50)',
    ];

    public function run(array $params)
    {
        $limit = (int) (isset($params['limit']) ? $params['limit'] : (CLI::getOption('limit') ?? 50));

        /** @var RoleRepositoryInterface $roleRepo */
        $roleRepo = service('vima_roles');
        $roles = $roleRepo->all(resolve: true);

        if (empty($roles)) {
            CLI::write('No roles found.', 'yellow');
            return;
        }

        $body = [];
        foreach ($roles as $role) {
            $parents = implode(', ', array_map(fn($r) => $r->namespace ? "{$r->namespace}:{$r->name}" : $r->name, $role->parents)) ?: '[--NONE--]';
            $children = implode(', ', array_map(fn($r) => $r->namespace ? "{$r->namespace}:{$r->name}" : $r->name, $role->children)) ?: '[--NONE--]';
            $permissions = implode(', ', array_map(fn($p) => $p->namespace ? "{$p->namespace}:{$p->name}" : $p->name, $role->permissions)) ?: '[--NONE--]';

            $body[] = [
                $role->id,
                $role->namespace ?? '[--GLOBAL--]',
                $role->name,
                $role->description,
                json_encode($role->context),
                $this->truncate($parents, $limit),
                $this->truncate($children, $limit),
                $this->truncate($permissions, $limit)
            ];
        }

        CLI::table($body, ['ID', 'Namespace', 'Name', 'Description', 'Context', 'Parents', 'Children', 'Permissions']);
    }

    private function truncate(string $text, int $limit): string
    {
        if (strlen($text) <= $limit) {
            return $text;
        }

        return substr($text, 0, $limit - 3) . '...';
    }
}
