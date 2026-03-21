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
use Vima\Core\Services\AccessManager;

class VimaMakePermission extends BaseCommand
{
    protected $group = 'Vima';
    protected $name = 'vima:make-permission';
    protected $description = 'Create a new permission';
    protected $usage = 'vima:make-permission [name] [options]';
    protected $options = [
        'namespace'   => 'Namespace for the permission',
        'description' => 'Description for the permission',
    ];

    public function run(array $params)
    {
        $name = $params[0] ?? CLI::prompt('Permission name');
        $namespace = CLI::getOption('namespace');
        $description = CLI::getOption('description') ?? $params[1] ?? '';

        try {
            /** @var AccessManager $manager */
            $manager = service('vima');
            $permission = $manager->ensurePermission($name, $description, $namespace);
            
            $msg = "Permission [{$permission->name}]";
            if ($permission->namespace) {
                $msg .= " in namespace [{$permission->namespace}]";
            }
            $msg .= " created successfully.";

            CLI::write($msg, 'green');
        } catch (\Throwable $e) {
            CLI::error("Failed to create permission: " . $e->getMessage());
        }
    }
}
