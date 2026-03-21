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

use Vima\Core\Contracts\UserPermissionRepositoryInterface;
use Vima\Core\Entities\UserPermission;
use Vima\CodeIgniter\Models\UserPermissionModel;
use Vima\Core\Contracts\PermissionRepositoryInterface;
use Vima\Core\Contracts\EventDispatcherInterface;
use Vima\Core\Events\Repository\RepositoryAction;

class UserPermissionRepository implements UserPermissionRepositoryInterface
{
    protected UserPermissionModel $model;

    public function __construct(protected ?EventDispatcherInterface $dispatcher = null)
    {
        $this->model = new UserPermissionModel();
    }

    public function findByUserId(int|string $userId): array
    {
        $cols = service('vima_config')->columns->userPermissions;
        $data = $this->model->where($cols->userId, $userId)->findAll();
        return array_map(fn($row) => UserPermission::define(
            user_id: $row[$cols->userId],
            permission_id: $row[$cols->permissionId],
        ), $data);
    }

    public function getPermissionsForUser(int|string $user_id): array
    {
        $cols = service('vima_config')->columns->userPermissions;
        $data = $this->model->asArray()->where($cols->userId, $user_id)->findAll();

        /** @var PermissionRepositoryInterface $permRepo */
        $permRepo = service('vima_permissions');

        $permissions = [];
        foreach ($data as $row) {
            $permissions[] = $permRepo->findById($row[$cols->permissionId]);
        }

        return array_filter($permissions);
    }

    public function add(UserPermission $userPermission): void
    {
        $cols = service('vima_config')->columns->userPermissions;
        $existing = $this->model->asArray()->where([
            $cols->userId => $userPermission->user_id,
            $cols->permissionId => $userPermission->permission_id
        ])->first();

        if ($existing) {
            return;
        }

        $id = $this->model->insert([
            $cols->userId => $userPermission->user_id,
            $cols->permissionId => $userPermission->permission_id
        ]);

        $userPermission->id = $id;

        $this->dispatcher?->dispatch(new RepositoryAction(RepositoryAction::ACTION_CREATED, UserPermission::class, $userPermission));
    }

    public function remove(UserPermission $userPermission): void
    {
        $cols = service('vima_config')->columns->userPermissions;
        $this->model->where([
            $cols->userId => $userPermission->user_id,
            $cols->permissionId => $userPermission->permission_id
        ])->delete();

        $this->dispatcher?->dispatch(new RepositoryAction(RepositoryAction::ACTION_DELETED, UserPermission::class, $userPermission));
    }
}
