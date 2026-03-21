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
use Vima\Core\Entities\RoleParent;
use Vima\CodeIgniter\Models\RoleModel;
use Vima\Core\Contracts\RolePermissionRepositoryInterface;
use Vima\Core\Contracts\RoleParentRepositoryInterface;
use Vima\Core\Contracts\EventDispatcherInterface;
use Vima\Core\Events\Repository\RepositoryAction;
use CodeIgniter\CLI\CLI;
use Vima\Core\Entities\RolePermission;

class RoleRepository implements RoleRepositoryInterface
{
    protected RoleModel $model;

    public function __construct(protected ?EventDispatcherInterface $dispatcher = null)
    {
        $this->model = new RoleModel();
    }

    private static array $loading = [];

    public function findById(int|string $id, bool $resolve = false): ?Role
    {
        if (isset(self::$loading[$id])) {
            return null; // Prevent deep/circular recursion
        }

        self::$loading[$id] = true;

        try {
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

                /** @var RoleParentRepositoryInterface $rhRepo */
                $rhRepo = service('vima_role_parents');
                $role->parents = $rhRepo->getParents($role);
                $role->children = $rhRepo->getChildren($role);
            }

            return $role;
        } finally {
            unset(self::$loading[$id]);
        }
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

    public function all(?string $namespace = null, bool $onlyGlobal = false, bool $resolve = false): array
    {
        $cols = service('vima_config')->columns->roles;
        $query = $this->model->asArray();

        if ($namespace !== null) {
            $query->where($cols->namespace, $namespace);
        } else {
            if ($onlyGlobal) {
                $query->where($cols->namespace, null);
            }
        }

        $all = $query->findAll();

        return array_map(fn($data) => $this->findById($data[$cols->id], $resolve), $all);
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
            $this->dispatcher?->dispatch(new RepositoryAction(RepositoryAction::ACTION_UPDATED, Role::class, $role));
        } else {
            // Check for existing by name/namespace if no ID
            $existing = $this->findByName($role->name, $role->namespace);
            if ($existing) {
                $role->id = $existing->id;
                $this->model->update($role->id, $data);
                $this->dispatcher?->dispatch(new RepositoryAction(RepositoryAction::ACTION_UPDATED, Role::class, $role));
            } else {
                $id = $this->model->insert($data);
                $role->id = $id;
                $this->dispatcher?->dispatch(new RepositoryAction(RepositoryAction::ACTION_CREATED, Role::class, $role));
            }
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

                $rpRepo->assign(RolePermission::define(
                    role_id: $role->id,
                    permission_id: $permission->id
                ));
            }
        }

        // Save parents if any
        if (!empty($role->parents)) {
            /** @var \Vima\Core\Contracts\RoleParentRepositoryInterface $rhRepo */
            $rhRepo = service('vima_role_parents');
            $rhRepo->clearParents($role);

            foreach ($role->parents as $parent) {
                if (!$parent->id) {
                    $savedParent = $this->findByName($parent->name, $parent->namespace);
                    if ($savedParent) {
                        $parent->id = $savedParent->id;
                    } else {
                        $this->save($parent);
                    }
                }

                $rhRepo->assign(RoleParent::define(
                    role_id: $role->id,
                    parent_id: $parent->id
                ));
            }
        }

        if (!empty($role->children)) {
            /** @var \Vima\Core\Contracts\RoleParentRepositoryInterface $rhRepo */
            $rhRepo = service('vima_role_parents');
            $rhRepo->clearChildren($role);

            foreach ($role->children as $child) {
                if (!$child->id) {
                    $savedChild = $this->findByName($child->name, $child->namespace);
                    if ($savedChild) {
                        $child->id = $savedChild->id;
                    } else {
                        $this->save($child);
                    }
                }

                $rhRepo->assign(RoleParent::define(
                    role_id: $child->id,
                    parent_id: $role->id
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
            $this->dispatcher?->dispatch(new RepositoryAction(RepositoryAction::ACTION_DELETED, Role::class, $role));
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
            $this->dispatcher?->dispatch(new RepositoryAction(RepositoryAction::ACTION_DELETED, Role::class, $role));
        }
    }

    public function deleteAll(): void
    {
        $this->model->truncate();
        $this->dispatcher?->dispatch(new RepositoryAction(RepositoryAction::ACTION_DELETED_ALL, Role::class));
    }

    public function getParents(Role $role): array
    {
        /** @var \Vima\Core\Contracts\RoleParentRepositoryInterface $rhRepo */
        $rhRepo = service('vima_role_parents');
        return $rhRepo->getParents($role);
    }

    public function getChildren(Role $role): array
    {
        /** @var \Vima\Core\Contracts\RoleParentRepositoryInterface $rhRepo */
        $rhRepo = service('vima_role_parents');
        return $rhRepo->getChildren($role);
    }
}