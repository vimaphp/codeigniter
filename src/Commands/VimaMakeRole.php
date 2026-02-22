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

class VimaMakeRole extends BaseCommand
{
    protected $group = 'Vima';
    protected $name = 'vima:make-role';
    protected $description = 'Create a new role';
    protected $usage = 'vima:make-role [name] [description]';

    public function run(array $params)
    {
        $name = $params[0] ?? CLI::prompt('Role name');
        $description = $params[1] ?? CLI::prompt('Description', '');

        try {
            /** @var AccessManager $manager */
            $manager = service('vima');
            $role = $manager->ensureRole($name, $description);
            CLI::write("Role [{$role->name}] created successfully.", 'green');
        } catch (\Throwable $e) {
            CLI::error("Failed to create role: " . $e->getMessage());
        }
    }
}
