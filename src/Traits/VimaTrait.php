<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/vimaphp>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vima\CodeIgniter\Traits;

use Vima\Core\Exceptions\AccessDeniedException;

/**
 * Trait VimaTrait
 * 
 * Provides convenient authorization methods for CodeIgniter 4 controllers.
 */
trait VimaTrait
{
    /**
     * Authorize the current user for the given permission.
     *
     * @param mixed $permission Or resource object
     * @param mixed ...$arguments
     * @throws AccessDeniedException
     */
    protected function authorize(string $permission, ...$arguments): void
    {
        if (!$this->can((string) $permission, ...$arguments)) {
            throw AccessDeniedException::forPermission((string) $permission);
        }
    }

    /**
     * Authorizes any of the permssions provided. Throws an exception if all are forbidden
     * @param array $permissions
     * @param array $arguments
     * @throws AccessDeniedException
     * @return void
     */
    protected function authorize_any(array $permissions, ...$arguments): void
    {
        if (!$this->can_any($permissions, ...$arguments)) {
            throw AccessDeniedException::forPermission(implode(' or ', $permissions));
        }
    }


    /**
     * Authorizes all of the permssions provided. Throws an exception if any is forbidden
     * @param array $permissions
     * @param array $arguments
     * @throws AccessDeniedException
     * @return void
     */
    protected function authorize_all(array $permissions, ...$arguments): void
    {
        if (!$this->can_all($permissions, ...$arguments)) {
            throw AccessDeniedException::forPermission(implode(' or ', $permissions));
        }
    }

    /**
     * Check if the current user has the given permission.
     *
     * @param string $permission
     * @param mixed ...$arguments
     * @return bool
     */
    protected function can(string $permission, ...$arguments): bool
    {
        return can($permission, ...$arguments);
    }

    /**
     * Performs a can check on all of the permissions provided and returns true on the first permissible action for the current user
     * @param array $permissions
     * @param array $arguments
     * @return bool
     */
    protected function can_any(array $permissions, ...$arguments): bool
    {
        return can_any($permissions, ...$arguments);
    }

    /**
     * Performs a can check on all of the permissions provided and returns true on the first impermissible action for the current user
     * @param array $permissions
     * @param array $arguments
     * @return bool
     */
    protected function can_all(array $permissions, ...$arguments): bool
    {
        return can_any($permissions, ...$arguments);
    }
}
