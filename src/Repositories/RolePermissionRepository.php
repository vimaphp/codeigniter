<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/vimaphp>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Vima\CodeIgniter\Repositories;

use Vima\Core\Contracts\RolePermissionRepositoryInterface;
use Vima\Core\Entities\Bare\BareRole;
use Vima\Core\Entities\Bare\BarePermission;
use Vima\Core\Entities\Bare\BareRolePermission;
use Vima\CodeIgniter\Models\RolePermissionModel;

class RolePermissionRepository implements RolePermissionRepositoryInterface
{
    protected RolePermissionModel $model;

    public function __construct()
    {
        $this->model = new RolePermissionModel();
    }

    public function getRolePermissions(BareRole $role): array
    {
        $cols = service('vima_config')->columns->rolePermissions;
        $data = $this->model->asArray()->where($cols->roleId, $role->id)->findAll();

        return array_map(fn($row) => new BareRolePermission(
            id: $row['id'] ?? null,
            role_id: $row[$cols->roleId],
            permission_id: $row[$cols->permissionId],
            constraints: isset($row[$cols->constraints]) ? json_decode($row[$cols->constraints], true) : null
        ), $data);
    }

    public function getPermissionRoles(BarePermission $permission): array
    {
        $cols = service('vima_config')->columns->rolePermissions;
        $data = $this->model->asArray()->where($cols->permissionId, $permission->id)->findAll();

        return array_map(fn($row) => new BareRolePermission(
            id: $row['id'] ?? null,
            role_id: $row[$cols->roleId],
            permission_id: $row[$cols->permissionId],
            constraints: isset($row[$cols->constraints]) ? json_decode($row[$cols->constraints], true) : null
        ), $data);
    }

    public function all(): array
    {
        $cols = service('vima_config')->columns->rolePermissions;
        $data = $this->model->asArray()->findAll();
        return array_map(fn($row) => new BareRolePermission(
            id: $row['id'] ?? null,
            role_id: $row[$cols->roleId],
            permission_id: $row[$cols->permissionId],
            constraints: isset($row[$cols->constraints]) ? json_decode($row[$cols->constraints], true) : null
        ), $data);
    }

    public function assign(BareRolePermission $permission): void
    {
        $cols = service('vima_config')->columns->rolePermissions;
        $existing = $this->model->where([
            $cols->roleId => $permission->role_id,
            $cols->permissionId => $permission->permission_id
        ])->first();

        if ($existing) {
            $this->model->update($existing['id'], [
                $cols->constraints => $permission->constraints ? json_encode($permission->constraints) : null
            ]);
            return;
        }

        $this->model->insert([
            $cols->roleId => $permission->role_id,
            $cols->permissionId => $permission->permission_id,
            $cols->constraints => $permission->constraints ? json_encode($permission->constraints) : null
        ]);
    }

    public function revoke(BareRolePermission $permission): void
    {
        $cols = service('vima_config')->columns->rolePermissions;
        $this->model->where([
            $cols->roleId => $permission->role_id,
            $cols->permissionId => $permission->permission_id
        ])->delete();
    }

    public function deleteAll(): void
    {
        $this->model->where('1=1')->delete();
    }
}
