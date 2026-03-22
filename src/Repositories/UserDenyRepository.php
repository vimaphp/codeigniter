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

    public function add(string|int $user_id, string|int $permission_id, ?string $reason = null): void
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
                $cols->reason => $reason,
                $cols->createdAt => date('Y-m-d H:i:s'),
            ]);
        } else {
            // Update reason if it changed
            $this->model->update($exists['id'], [
                $cols->reason => $reason,
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
            ->select($tables->permissions . '.*, ' . $tables->userDenies . '.*')
            ->join($tables->permissions, $tables->permissions . '.' . $permCols->id . ' = ' . $tables->userDenies . '.' . $cols->permissionId)
            ->where($tables->userDenies . '.' . $cols->userId, $user_id)
            ->get()
            ->getResult();

        $denies = [];
        foreach ($results as $row) {
            $permission = new Permission(
                $row->{$permCols->name},
                $row->{$permCols->description} ?? null,
                $row->{$permCols->namespace} ?? null,
                $row->{$permCols->id}
            );

            $denies[] = new \Vima\Core\Entities\UserDeny(
                user_id: $row->{$cols->userId},
                permission_id: $row->{$cols->permissionId},
                id: (int) $row->id,
                reason: $row->{$cols->reason} ?? null,
                permission: $permission
            );
        }

        return $denies;
    }
}
