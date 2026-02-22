<@php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/vimaphp>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace {namespace};

use Vima\Core\Contracts\PolicyInterface;
use {resourceFullClass};

class {class} implements PolicyInterface
{
    /**
     * Return the fully qualified class name of the resource this policy handles.
     *
     * @return string
     */
    public static function getResource(): string
    {
        return {resourceClass}::class;
    }

    /**
     * Check if user can view the resource.
     *
     * @param object $user
     * @param {resourceClass} ${resourceVar}
     * @return bool
     */
    public function canView(object $user, {resourceClass} ${resourceVar}): bool
    {
        return true;
    }

    /**
     * Check if user can edit the resource.
     *
     * @param object $user
     * @param {resourceClass} ${resourceVar}
     * @return bool
     */
    public function canEdit(object $user, {resourceClass} ${resourceVar}): bool
    {
        return true;
    }
}