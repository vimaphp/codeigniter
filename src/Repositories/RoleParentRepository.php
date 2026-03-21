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

use Vima\Core\Contracts\RoleParentRepositoryInterface;
use Vima\Core\Contracts\RoleRepositoryInterface;
use Vima\Core\Entities\Role;
use Vima\Core\Entities\RoleParent;
use Vima\CodeIgniter\Models\RoleParentModel;

class RoleParentRepository implements RoleParentRepositoryInterface
{
    protected RoleParentModel $model;

    public function __construct()
    {
        $this->model = new RoleParentModel();
    }

    public function assign(RoleParent $relationship): void
    {
        $cols = service('vima_config')->columns->roleParents;
        $data = [
            $cols->roleId => $relationship->role_id,
            $cols->parentId => $relationship->parent_id,
        ];

        // Check for existing relationship to avoid duplicates
        $exists = $this->model->where($data)->first();

        if (!$exists) {
            $this->model->insert($data);
        }
    }

    public function remove(RoleParent $relationship): void
    {
        $cols = service('vima_config')->columns->roleParents;
        $this->model
            ->where($cols->roleId, $relationship->role_id)
            ->where($cols->parentId, $relationship->parent_id)
            ->delete();
    }

    public function clearParents(Role $role): void
    {
        $cols = service('vima_config')->columns->roleParents;
        $this->model->where($cols->roleId, $role->id)->delete();
    }

    public function getParents(Role $role): array
    {
        $cols = service('vima_config')->columns->roleParents;
        $relationships = $this->model->where($cols->roleId, $role->id)->findAll();

        $roles = [];
        /** @var RoleRepositoryInterface $roleRepo */
        $roleRepo = service('vima_roles');

        foreach ($relationships as $data) {
            $parent = $roleRepo->findById($data[$cols->parentId], true);
            if ($parent) {
                $roles[] = $parent;
            }
        }

        return $roles;
    }

    public function getChildren(Role $role): array
    {
        $cols = service('vima_config')->columns->roleParents;
        $relationships = $this->model->where($cols->parentId, $role->id)->findAll();

        $roles = [];
        /** @var RoleRepositoryInterface $roleRepo */
        $roleRepo = service('vima_roles');

        foreach ($relationships as $data) {
            $child = $roleRepo->findById($data[$cols->roleId], true);
            if ($child) {
                $roles[] = $child;
            }
        }

        return $roles;
    }
}
