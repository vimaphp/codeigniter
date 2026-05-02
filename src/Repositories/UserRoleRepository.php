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

use Vima\Core\Contracts\UserRoleRepositoryInterface;
use Vima\Core\Entities\Bare\BareUserRole;
use Vima\CodeIgniter\Models\UserRoleModel;

class UserRoleRepository implements UserRoleRepositoryInterface
{
    protected UserRoleModel $model;

    public function __construct()
    {
        $this->model = new UserRoleModel();
    }

    public function getRolesForUser(int|string $user_id): array
    {
        $cols = service('vima_config')->columns->userRoles;
        $data = $this->model->asArray()->where($cols->userId, $user_id)->findAll();

        return array_map(fn($row) => new BareUserRole(
            id: $row[$cols->id] ?? null,
            user_id: $row[$cols->userId],
            role_id: $row[$cols->roleId]
        ), $data);
    }

    public function assign(BareUserRole $userRole): void
    {
        $cols = service('vima_config')->columns->userRoles;

        $existing = $this->model->asArray()->where([
            $cols->userId => $userRole->user_id,
            $cols->roleId => $userRole->role_id
        ])->first();

        if ($existing) {
            return;
        }

        $id = $this->model->insert([
            $cols->userId => $userRole->user_id,
            $cols->roleId => $userRole->role_id
        ]);

        $userRole->id = $id;
    }

    public function revoke(BareUserRole $userRole): void
    {
        $cols = service('vima_config')->columns->userRoles;

        $this->model->where([
            $cols->userId => $userRole->user_id,
            $cols->roleId => $userRole->role_id
        ])->delete();
    }
}
