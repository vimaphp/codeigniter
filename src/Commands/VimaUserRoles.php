<?php

namespace Vima\CodeIgniter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Vima\CodeIgniter\Support\Utils;

class VimaUserRoles extends BaseCommand
{
    protected $group = 'Vima';
    protected $name = 'vima:user-roles';
    protected $description = 'List all roles for a user';
    protected $usage = 'vima:user-roles [user_id] [options]';
    protected $options = [
        'limit' => 'Limit the number of characters in list columns (default: 30)',
    ];

    public function run(array $params)
    {
        $user_id = $params[0] ?? CLI::prompt('User ID', null, 'required');
        $resolve = (bool) CLI::getOption('resolve');
        $limit = (int) (isset($params['limit']) ? $params['limit'] : (CLI::getOption('limit') ?? 30));

        if (empty($user_id)) {
            CLI::write('User ID is required.', 'red');
            return;
        }

        $user = Utils::creatVimaUser($user_id);

        try {
            $roles = service('vima')->getUserRoles($user, resolve: $resolve);

            CLI::write('Roles for user ' . $user_id . ':', 'green');

            $body = [];
            foreach ($roles as $role) {
                $parents = implode(', ', array_map(fn($r) => $r->namespace ? "{$r->namespace}:{$r->name}" : $r->name, $role->parents)) ?: '[--NONE--]';
                $children = implode(', ', array_map(fn($r) => $r->namespace ? "{$r->namespace}:{$r->name}" : $r->name, $role->children)) ?: '[--NONE--]';
                $permissions = implode(', ', array_map(fn($p) => $p->namespace ? "{$p->namespace}:{$p->name}" : $p->name, $role->permissions)) ?: '[--NONE--]';

                $body[] = [
                    $this->truncate($role->id, $limit),
                    $this->truncate($role->namespace ?? '[--GLOBAL--]', $limit),
                    $this->truncate($role->name, $limit),
                    $this->truncate($role->description, $limit),
                    $this->truncate(json_encode($role->context ?? []), $limit),
                    $this->truncate($permissions, $limit),
                ];

                if ($resolve) {
                    $body[] = [
                        $this->truncate($parents, $limit),
                        $this->truncate($children, $limit),
                    ];
                }
            }

            $thead = ['ID', 'Namespace', 'Name', 'Context'];

            if ($resolve) {
                $thead = array_merge($thead, ['Parents', 'Children', 'Permissions']);
            }

            CLI::table($body, $thead);
        } catch (\Exception $e) {
            CLI::write($e->getMessage(), 'red');
        }
    }

    private function truncate(string $text, int $limit): string
    {
        if (strlen($text) <= $limit) {
            return $text;
        }

        return substr($text, 0, $limit - 3) . '...';
    }
}