<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/vimaphp>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Vima\CodeIgniter\Repositories;

use CodeIgniter\Model;
use Vima\CodeIgniter\Models\UserDenyModel;
use Vima\Core\Config\Columns;
use Vima\Core\Contracts\UserDenyRepositoryInterface;
use Vima\Core\Entities\Permission;

/**
 * Class UserDenyRepository
 *
 * CI4 implementation of UserDenyRepositoryInterface.
 */
class UserDenyRepository implements UserDenyRepositoryInterface
{
    protected Model $model;
    protected Columns $columns;

    public function __construct(?Model $model = null, ?Columns $columns = null)
    {
        $this->model = $model ?? new UserDenyModel();
        $this->columns = $columns ?? new Columns(); // This usually comes from global config
    }

    public function add(string|int $user_id, string|int $permission_id): void
    {
        $cols = $this->columns->userDenies;
        
        $exists = $this->model->where([
            $cols->userId => $user_id,
            $cols->permissionId => $permission_id,
        ])->first();

        if (!$exists) {
            $this->model->insert([
                $cols->userId => $user_id,
                $cols->permissionId => $permission_id,
            ]);
        }
    }

    public function remove(string|int $user_id, string|int $permission_id): void
    {
        $cols = $this->columns->userDenies;
        
        $this->model->where([
            $cols->userId => $user_id,
            $cols->permissionId => $permission_id,
        ])->delete();
    }

    public function isDenied(string|int $user_id, string|int $permission_id): bool
    {
        $cols = $this->columns->userDenies;
        
        return $this->model->where([
            $cols->userId => $user_id,
            $cols->permissionId => $permission_id,
        ])->countAllResults() > 0;
    }

    public function getDeniedPermissions(string|int $user_id): array
    {
        $cols = $this->columns->userDenies;
        $permCols = $this->columns->permissions;
        $tables = \config('Vima')->tables;

        $results = $this->model->db->table($tables->userDenies)
            ->select($tables->permissions . '.*')
            ->join($tables->permissions, $tables->permissions . '.' . $permCols->id . ' = ' . $tables->userDenies . '.' . $cols->permissionId)
            ->where($tables->userDenies . '.' . $cols->userId, $user_id)
            ->get()
            ->getResult();

        $permissions = [];
        foreach ($results as $row) {
            $permissions[] = new Permission(
                $row->{$permCols->name},
                $row->{$permCols->description} ?? null,
                $row->{$permCols->namespace} ?? null,
                $row->{$permCols->id}
            );
        }

        return $permissions;
    }
}
