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
            namespace: $data[$cols->namespace] ?? null,
            id: $data[$cols->id],
            description: $data[$cols->description] ?? null,
            context: isset($data[$cols->context]) ? json_decode($data[$cols->context], true) : [],
        );

        if ($resolve) {
            /** @var RolePermissionRepositoryInterface $rpRepo */
            $rpRepo = service('vima_role_permissions');
            $role->permissions = $rpRepo->getRolePermissions($role);
        }

        return $role;
    }

    public function findByName(string $name, ?string $namespace = null): ?Role
    {
        $cols = service('vima_config')->columns->roles;
        $query = $this->model->asArray()->where($cols->name, $name);

        if ($namespace) {
            $query->where($cols->namespace, $namespace);
        } else {
            $query->groupStart()
                ->where($cols->namespace, null)
                ->orWhere($cols->namespace, '')
                ->groupEnd();
        }

        $data = $query->first();
        if (!$data) {
            return null;
        }

        return new Role(
            name: $data[$cols->name],
            namespace: $data[$cols->namespace] ?? null,
            id: $data[$cols->id],
            description: $data[$cols->description] ?? null,
            context: isset($data[$cols->context]) ? json_decode($data[$cols->context], true) : [],
        );
    }

    public function all(?string $namespace = null): array
    {
        $cols = service('vima_config')->columns->roles;
        $query = $this->model->asArray();

        if ($namespace !== null) {
            $query->where($cols->namespace, $namespace);
        }

        $all = $query->findAll();

        return array_map(fn($data) => new Role(
            name: $data[$cols->name],
            namespace: $data[$cols->namespace] ?? null,
            id: $data[$cols->id],
            description: $data[$cols->description] ?? null,
            context: isset($data[$cols->context]) ? json_decode($data[$cols->context], true) : [],
        ), $all);
    }

    public function save(Role $role): Role
    {
        $cols = service('vima_config')->columns->roles;
        $data = [
            $cols->name => $role->name,
            $cols->namespace => $role->namespace,
            $cols->description => $role->description,
            $cols->context => empty($role->context) ? null : json_encode($role->context),
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
                    $savedP = $pRepo->findByName($permission->name, $permission->namespace);
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
            $query = $this->model->where($cols->name, $role->name);
            if ($role->namespace) {
                $query->where($cols->namespace, $role->namespace);
            } else {
                $query->groupStart()
                    ->where($cols->namespace, null)
                    ->orWhere($cols->namespace, '')
                    ->groupEnd();
            }
            $query->delete();
        }
    }

    public function deleteAll(): void
    {
        $this->model->truncate();
    }

    public function getParents(Role $role): array
    {
        // TODO: Implement database structure for role inheritance
        return [];
    }

    public function getChildren(Role $role): array
    {
        // TODO: Implement database structure for role inheritance
        return [];
    }
}
