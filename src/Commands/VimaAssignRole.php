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

class VimaAssignRole extends BaseCommand
{
    protected $group = 'Vima';
    protected $name = 'vima:assign-role';
    protected $description = 'Assign a role to a user';
    protected $usage = 'vima:assign-role [user_id] [role_name]';

    public function run(array $params)
    {
        $userId = $params[0] ?? CLI::prompt('User ID');
        $roleName = $params[1] ?? CLI::prompt('Role Name');

        try {
            $manager = service('vima');

            // Temporary user object for resolution
            $user = new class ($userId) {
                public function __construct(public $id)
                {}
                public function vimaGetId()
                {
                    return $this->id; }
            };

            $manager->assignRole($user, $roleName);
            CLI::write("Role [{$roleName}] assigned to user [{$userId}] successfully.", 'green');
        } catch (\Throwable $e) {
            CLI::error("Failed to assign role: " . $e->getMessage());
        }
    }
}
