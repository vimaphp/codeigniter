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
use Vima\Core\Entities\Bare\BareUserPermission;
use Vima\CodeIgniter\Models\UserPermissionModel;
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
        return array_map(fn($row) => new BareUserPermission(
            id: $row[$cols->id] ?? null,
            user_id: $row[$cols->userId],
            permission_id: $row[$cols->permissionId],
            constraints: isset($row[$cols->constraints]) ? json_decode($row[$cols->constraints], true) : null
        ), $data);
    }

    public function add(BareUserPermission $userPermission): void
    {
        $cols = service('vima_config')->columns->userPermissions;
        $existing = $this->model->asArray()->where([
            $cols->userId => $userPermission->user_id,
            $cols->permissionId => $userPermission->permission_id
        ])->first();

        if ($existing) {
            $this->model->update($existing['id'], [
                $cols->constraints => $userPermission->constraints ? json_encode($userPermission->constraints) : null
            ]);
            return;
        }

        $id = $this->model->insert([
            $cols->userId => $userPermission->user_id,
            $cols->permissionId => $userPermission->permission_id,
            $cols->constraints => $userPermission->constraints ? json_encode($userPermission->constraints) : null
        ]);

        $userPermission->id = $id;

        $this->dispatcher?->dispatch(new RepositoryAction(RepositoryAction::ACTION_CREATED, BareUserPermission::class, $userPermission));
    }

    public function remove(BareUserPermission $userPermission): void
    {
        $cols = service('vima_config')->columns->userPermissions;
        $this->model->where([
            $cols->userId => $userPermission->user_id,
            $cols->permissionId => $userPermission->permission_id
        ])->delete();

        $this->dispatcher?->dispatch(new RepositoryAction(RepositoryAction::ACTION_DELETED, BareUserPermission::class, $userPermission));
    }
}
