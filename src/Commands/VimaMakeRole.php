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
use Vima\Core\Entities\Permission;
use Vima\Core\Entities\Role;
use Vima\Core\Services\AccessManager;

class VimaMakeRole extends BaseCommand
{
    protected $group = 'Vima';
    protected $name = 'vima:make-role';
    protected $description = 'Create a new role';
    protected $usage = 'vima:make-role [name] [options]';
    protected $options = [
        'namespace' => 'Namespace for the role',
        'description' => 'Description for the role',
        'parents' => 'Parents for the role (comma separated)',
        'children' => 'Children for the role (comma separated)',
        'permissions' => 'Permissions for the role (comma separated)',
        'context' => 'Context for the role (pass as JSON)'
    ];

    public function run(array $params)
    {
        $name = $params[0] ?? CLI::prompt('Role name');
        $namespace = CLI::getOption('namespace');
        $description = CLI::getOption('description') ?? $params[1] ?? '';
        
        $parents = array_map('trim', explode(',', CLI::getOption('parents') ?? ''));
        $children = array_map('trim', explode(',', CLI::getOption('children') ?? ''));
        $permissions = array_map('trim', explode(',', CLI::getOption('permissions') ?? ''));
        $context = [];

        try {
            $context = CLI::getOption('context') ? json_decode(CLI::getOption('context'), true) : [];
        } catch (\Throwable $e) {
            CLI::error("Failed to parse context: " . $e->getMessage());
            return;
        }

        try {
            /** @var AccessManager $manager */
            $manager = service('vima');
            $role = Role::define(
                name: $name,
                description: $description,
                namespace: $namespace,
                parents: array_map(fn($p) => Role::define($p), $parents),
                children: array_map(fn($c) => Role::define($c), $children),
                permissions: array_map(fn($p) => Permission::define($p), $permissions),
                context: $context
            );

            $role = $manager->ensureRole($role);

            $msg = "Role [{$role->name}]";
            if ($role->namespace) {
                $msg .= " in namespace [{$role->namespace}]";
            }
            $msg .= " created successfully.";

            CLI::write($msg, 'green');
        } catch (\Throwable $e) {
            CLI::error("Failed to create role: " . $e->getMessage());
        }
    }
}
