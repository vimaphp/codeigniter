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

class VimaRoleList extends BaseCommand
{
    protected $group = 'Vima';
    protected $name = 'vima:role-list';
    protected $description = 'List all roles and their permissions';

    public function run(array $params)
    {
        $repository = service('vima_roles');
        $roles = $repository->all();

        if (empty($roles)) {
            CLI::write('No roles found.', 'yellow');
            return;
        }

        $thead = ['ID', 'Name', 'Description', 'Permissions'];
        $tbody = [];

        foreach ($roles as $role) {
            // Resolve role with permissions
            $fullRole = $repository->findById($role->id, true);
            $perms = array_map(fn($p) => $p->name, $fullRole->permissions);

            $tbody[] = [
                $role->id ?? 'N/A',
                $role->name,
                $role->description ?? '-',
                implode(', ', $perms)
            ];
        }

        CLI::table($tbody, $thead);
    }
}
