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
use Vima\Core\Contracts\AccessManagerInterface;

class VimaAssignRole extends BaseCommand
{
    protected $group = 'Vima';
    protected $name = 'vima:assign-role';
    protected $description = 'Assign a role to a user';
    protected $usage = 'vima:assign-role [user_id] [role_name] [options]';
    protected $options = [
        'namespace' => 'Namespace for the role',
    ];

    public function run(array $params)
    {
        $userId = $params[0] ?? CLI::prompt('User ID');
        $roleName = $params[1] ?? CLI::prompt('Role Name');
        $namespace = CLI::getOption('namespace');

        try {
            /**
             * @var AccessManagerInterface
             */
            $manager = service('vima');

            if (!str_contains($roleName, ':') && $namespace) {
                $roleName = "$namespace:$roleName";
            }

            // Temporary user object for resolution
            $user = Utils::creatVimaUser($userId);

            $manager->assignRole($user, $roleName);

            $msg = "Role [{$roleName}]";
            if ($namespace) {
                $msg .= " in namespace [{$namespace}]";
            }
            $msg .= " assigned to user [{$userId}] successfully.";

            CLI::write($msg, 'green');
        } catch (\Throwable $e) {
            CLI::error("Failed to assign role: " . $e->getMessage());
        }
    }
}
