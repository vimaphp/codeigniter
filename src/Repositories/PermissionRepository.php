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

use Vima\Core\Contracts\PermissionRepositoryInterface;
use Vima\Core\Entities\Permission;
use Vima\CodeIgniter\Models\PermissionModel;

class PermissionRepository implements PermissionRepositoryInterface
{
    protected PermissionModel $model;

    public function __construct()
    {
        $this->model = new PermissionModel();
    }

    public function findById(int|string $id): ?Permission
    {
        $cols = service('vima_config')->columns->permissions;
        $data = $this->model->asArray()->find($id);
        if (!$data) {
            return null;
        }

        return new Permission(
            name: $data[$cols->name],
            id: $data[$cols->id],
            description: $data[$cols->description] ?? null
        );
    }

    public function findByName(string $name): ?Permission
    {
        $cols = service('vima_config')->columns->permissions;
        $data = $this->model->asArray()->where($cols->name, $name)->first();
        if (!$data) {
            return null;
        }

        return new Permission(
            name: $data[$cols->name],
            id: $data[$cols->id],
            description: $data[$cols->description] ?? null
        );
    }

    public function all(): array
    {
        $cols = service('vima_config')->columns->permissions;
        $all = $this->model->asArray()->findAll();
        return array_map(fn($data) => new Permission(
            name: $data[$cols->name],
            id: $data[$cols->id],
            description: $data[$cols->description] ?? null
        ), $all);
    }

    public function save(Permission $permission): Permission
    {
        $cols = service('vima_config')->columns->permissions;
        $data = [
            $cols->name => $permission->name,
            $cols->description => $permission->description,
        ];

        if ($permission->id) {
            $this->model->update($permission->id, $data);
        } else {
            $id = $this->model->insert($data);
            $permission->id = $id;
        }

        return $permission;
    }

    public function delete(Permission $permission): void
    {
        $cols = service('vima_config')->columns->permissions;
        if ($permission->id) {
            $this->model->delete($permission->id);
        } else {
            $this->model->where($cols->name, $permission->name)->delete();
        }
    }

    public function deleteAll(): void
    {
        $this->model->truncate();
    }
}
