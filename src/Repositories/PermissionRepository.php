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
use Vima\Core\Contracts\EventDispatcherInterface;
use Vima\Core\Events\Repository\RepositoryAction;

class PermissionRepository implements PermissionRepositoryInterface
{
    protected PermissionModel $model;

    public function __construct(protected ?EventDispatcherInterface $dispatcher = null)
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
            namespace: $data[$cols->namespace] ?? null,
            id: $data[$cols->id],
            description: $data[$cols->description] ?? null
        );
    }

    public function findByName(string $name, ?string $namespace = null): ?Permission
    {
        $cols = service('vima_config')->columns->permissions;
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

        return new Permission(
            name: $data[$cols->name],
            namespace: $data[$cols->namespace] ?? null,
            id: $data[$cols->id],
            description: $data[$cols->description] ?? null
        );
    }

    public function all(?string $namespace = null, bool $onlyGlobal = false): array
    {
        $cols = service('vima_config')->columns->permissions;
        $query = $this->model->asArray();

        if ($namespace !== null) {
            $query->where($cols->namespace, $namespace);
        } else {
            if ($onlyGlobal) {
                $query->where($cols->namespace, null);
            }
        }

        $all = $query->findAll();
        return array_map(fn($data) => new Permission(
            name: $data[$cols->name],
            namespace: $data[$cols->namespace] ?? null,
            id: $data[$cols->id],
            description: $data[$cols->description] ?? null
        ), $all);
    }

    public function save(Permission $permission): Permission
    {
        $cols = service('vima_config')->columns->permissions;
        $data = [
            $cols->name => $permission->name,
            $cols->namespace => $permission->namespace,
            $cols->description => $permission->description,
        ];

        if ($permission->id) {
            $this->model->update($permission->id, $data);
            $this->dispatcher?->dispatch(new RepositoryAction(RepositoryAction::ACTION_UPDATED, Permission::class, $permission));
        } else {
            // Check for existing by name/namespace if no ID
            $existing = $this->findByName($permission->name, $permission->namespace);
            if ($existing) {
                $permission->id = $existing->id;
                $this->model->update($permission->id, $data);
                $this->dispatcher?->dispatch(new RepositoryAction(RepositoryAction::ACTION_UPDATED, Permission::class, $permission));
            } else {
                $id = $this->model->insert($data);
                $permission->id = $id;
                $this->dispatcher?->dispatch(new RepositoryAction(RepositoryAction::ACTION_CREATED, Permission::class, $permission));
            }
        }

        return $permission;
    }

    public function delete(Permission $permission): void
    {
        $cols = service('vima_config')->columns->permissions;
        if ($permission->id) {
            $this->model->delete($permission->id);
            $this->dispatcher?->dispatch(new RepositoryAction(RepositoryAction::ACTION_DELETED, Permission::class, $permission));
        } else {
            $query = $this->model->where($cols->name, $permission->name);
            if ($permission->namespace) {
                $query->where($cols->namespace, $permission->namespace);
            } else {
                $query->groupStart()
                    ->where($cols->namespace, null)
                    ->orWhere($cols->namespace, '')
                    ->groupEnd();
            }
            $query->delete();
            $this->dispatcher?->dispatch(new RepositoryAction(RepositoryAction::ACTION_DELETED, Permission::class, $permission));
        }
    }

    public function deleteAll(): void
    {
        $this->model->truncate();
        $this->dispatcher?->dispatch(new RepositoryAction(RepositoryAction::ACTION_DELETED_ALL, Permission::class));
    }
}
