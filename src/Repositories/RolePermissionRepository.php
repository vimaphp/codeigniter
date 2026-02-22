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
use Vima\Core\Entities\{Role, Permission, RolePermission};
use Vima\CodeIgniter\Models\RolePermissionModel;
use Vima\Core\Contracts\PermissionRepositoryInterface;

class RolePermissionRepository implements RolePermissionRepositoryInterface
{
    protected RolePermissionModel $model;

    public function __construct()
    {
        $this->model = new RolePermissionModel();
    }

    public function getRolePermissions(Role $role): array
    {
        $cols = service('vima_config')->columns->rolePermissions;
        $data = $this->model->asArray()->where($cols->roleId, $role->id)->findAll();

        /** @var PermissionRepositoryInterface $permRepo */
        $permRepo = service('vima_permissions');

        $permissions = [];
        foreach ($data as $row) {
            $permissions[] = $permRepo->findById($row[$cols->permissionId]);
        }

        return array_filter($permissions);
    }

    public function getPermissionRoles(Permission $permission): array
    {
        $cols = service('vima_config')->columns->rolePermissions;
        $data = $this->model->asArray()->where($cols->permissionId, $permission->id)->findAll();

        return array_map(fn($row) => RolePermission::define(
            role_id: $row[$cols->roleId],
            permission_id: $row[$cols->permissionId],
        ), $data);
    }

    public function all(): array
    {
        $cols = service('vima_config')->columns->rolePermissions;
        $data = $this->model->asArray()->findAll();
        return array_map(fn($row) => RolePermission::define(
            role_id: $row[$cols->roleId],
            permission_id: $row[$cols->permissionId],
        ), $data);
    }

    public function assign(RolePermission $permission): void
    {
        $cols = service('vima_config')->columns->rolePermissions;
        $existing = $this->model->where([
            $cols->roleId => $permission->role_id,
            $cols->permissionId => $permission->permission_id
        ])->first();

        if ($existing) {
            return;
        }

        $id = $this->model->insert([
            $cols->roleId => $permission->role_id,
            $cols->permissionId => $permission->permission_id
        ]);

        $permission->id = $id;
    }

    public function revoke(RolePermission $permission): void
    {
        $cols = service('vima_config')->columns->rolePermissions;
        $this->model->where([
            $cols->roleId => $permission->role_id,
            $cols->permissionId => $permission->permission_id
        ])->delete();
    }

    public function deleteAll(): void
    {
        $this->model->truncate();
    }
}
