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
use Vima\Core\Entities\Bare\BareRole;
use Vima\Core\Entities\Bare\BareRoleParent;
use Vima\CodeIgniter\Models\RoleParentModel;

class RoleParentRepository implements RoleParentRepositoryInterface
{
    protected RoleParentModel $model;

    public function __construct()
    {
        $this->model = new RoleParentModel();
    }

    public function assign(BareRoleParent $relationship): void
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

    public function remove(BareRoleParent $relationship): void
    {
        $cols = service('vima_config')->columns->roleParents;
        $this->model
            ->where($cols->roleId, $relationship->role_id)
            ->where($cols->parentId, $relationship->parent_id)
            ->delete();
    }

    public function clearParents(BareRole $role): void
    {
        $cols = service('vima_config')->columns->roleParents;
        $this->model->where($cols->roleId, $role->id)->delete();
    }

    public function getParents(BareRole $role): array
    {
        $cols = service('vima_config')->columns->roleParents;
        $relationships = $this->model->where($cols->roleId, $role->id)->findAll();

        return array_map(fn($data) => new BareRoleParent(
            id: $data[$cols->id] ?? null,
            role_id: $data[$cols->roleId],
            parent_id: $data[$cols->parentId]
        ), $relationships);
    }

    public function getChildren(BareRole $role): array
    {
        $cols = service('vima_config')->columns->roleParents;
        $relationships = $this->model->where($cols->parentId, $role->id)->findAll();

        return array_map(fn($data) => new BareRoleParent(
            id: $data[$cols->id] ?? null,
            role_id: $data[$cols->roleId],
            parent_id: $data[$cols->parentId]
        ), $relationships);
    }
}
