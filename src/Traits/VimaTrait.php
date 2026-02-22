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
use Vima\Core\Entities\Permission;

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
     * Supports both:
     * 1. $this->authorize('permission', $resource, ...$args)
     * 2. $this->authorize($resource, 'permission', ...$args)
     *
     * @param mixed $permission Or resource object
     * @param mixed ...$arguments
     * @throws AccessDeniedException
     */
    protected function authorize($permission, ...$arguments): void
    {
        // Detect reversed arguments: authorize($resource, $permission)
        if (is_object($permission) && !($permission instanceof Permission) && count($arguments) > 0) {
            $resource = $permission;
            $actualPermission = $arguments[0];
            $remainingArgs = array_slice($arguments, 1);

            $permission = $actualPermission;
            $arguments = array_merge([$resource], $remainingArgs);
        }

        if (!can((string) $permission, ...$arguments)) {
            throw AccessDeniedException::forPermission((string) $permission);
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
}
