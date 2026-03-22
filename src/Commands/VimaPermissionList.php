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

use Vima\Core\Contracts\PermissionRepositoryInterface;

class VimaPermissionList extends BaseCommand
{
    protected $group = 'Vima';
    protected $name = 'vima:role-list';
    protected $description = 'List all roles';
    protected $usage = 'vima:role-list [options]';
    protected $options = [
        'limit' => 'Limit the number of characters in list columns (default: 50)',
    ];

    public function run(array $params)
    {
        $limit = (int) (isset($params['limit']) ? $params['limit'] : (CLI::getOption('limit') ?? 30));

        /** @var PermissionRepositoryInterface $permRepo */
        $permRepo = service('vima_permissions');
        $permissions = $permRepo->all();

        if (empty($permissions)) {
            CLI::write('No permissions found.', 'yellow');
            return;
        }

        $body = [];
        foreach ($permissions as $perm) {
            $body[] = [
                $perm->id,
                $perm->namespace ?? '[--GLOBAL--]',
                $perm->name,
                $this->truncate($perm->description, $limit),
            ];
        }

        CLI::table($body, ['ID', 'Namespace', 'Name', 'Description']);
    }

    private function truncate(string $text, int $limit): string
    {
        if (strlen($text) <= $limit) {
            return $text;
        }

        return substr($text, 0, $limit - 3) . '...';
    }
}
