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

use Vima\Core\Contracts\RoleRepositoryInterface;
use Vima\Core\Entities\Role;
use Vima\CodeIgniter\Models\RoleModel;
use Vima\Core\Contracts\RolePermissionRepositoryInterface;

class RoleRepository implements RoleRepositoryInterface
{
    protected RoleModel $model;

    public function __construct()
    {
        $this->model = new RoleModel();
    }

    public function findById(int|string $id, bool $resolve = false): ?Role
    {
        $cols = service('vima_config')->columns->roles;
        $data = $this->model->asArray()->find($id);
        if (!$data) {
            return null;
        }

        $role = new Role(
            name: $data[$cols->name],
            id: $data[$cols->id],
            description: $data[$cols->description] ?? null
        );

        if ($resolve) {
            /** @var RolePermissionRepositoryInterface $rpRepo */
            $rpRepo = service('vima_role_permissions');
            $role->permissions = $rpRepo->getRolePermissions($role);
        }

        return $role;
    }

    public function findByName(string $name): ?Role
    {
        $cols = service('vima_config')->columns->roles;
        $data = $this->model->asArray()->where($cols->name, $name)->first();
        if (!$data) {
            return null;
        }

        return new Role(
            name: $data[$cols->name],
            id: $data[$cols->id],
            description: $data[$cols->description] ?? null
        );
    }

    public function all(): array
    {
        $cols = service('vima_config')->columns->roles;
        $all = $this->model->asArray()->findAll();
        return array_map(fn($data) => new Role(
            name: $data[$cols->name],
            id: $data[$cols->id],
            description: $data[$cols->description] ?? null
        ), $all);
    }

    public function save(Role $role): Role
    {
        $cols = service('vima_config')->columns->roles;
        $data = [
            $cols->name => $role->name,
            $cols->description => $role->description,
        ];

        if ($role->id) {
            $this->model->update($role->id, $data);
        } else {
            $id = $this->model->insert($data);
            $role->id = $id;
        }

        // Save permissions if any
        if (!empty($role->permissions)) {
            /** @var RolePermissionRepositoryInterface $rpRepo */
            $rpRepo = service('vima_role_permissions');

            foreach ($role->permissions as $permission) {
                if (!$permission->id) {
                    /** @var \Vima\Core\Contracts\PermissionRepositoryInterface $pRepo */
                    $pRepo = service('vima_permissions');
                    $savedP = $pRepo->findByName($permission->name);
                    if ($savedP) {
                        $permission->id = $savedP->id;
                    } else {
                        $pRepo->save($permission);
                    }
                }

                $rpRepo->assign(\Vima\Core\Entities\RolePermission::define(
                    role_id: $role->id,
                    permission_id: $permission->id
                ));
            }
        }

        return $role;
    }

    public function delete(Role $role): void
    {
        $cols = service('vima_config')->columns->roles;
        if ($role->id) {
            $this->model->delete($role->id);
        } else {
            $this->model->where($cols->name, $role->name)->delete();
        }
    }

    public function deleteAll(): void
    {
        $this->model->truncate();
    }
}
