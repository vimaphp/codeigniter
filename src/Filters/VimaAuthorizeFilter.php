<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/vimaphp>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Vima\CodeIgniter\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Vima\Core\Exceptions\AccessDeniedException;

/**
 * Filter to enforce Vima policies.
 */
class VimaAuthorizeFilter implements FilterInterface
{
    /**
     * @param array|null $arguments [permission]
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (empty($arguments)) {
            return;
        }

        $permission = $arguments[0] ?? null;
        if (!$permission) {
            return;
        }

        // Use the can() helper which handles user resolution and vima_context
        $resource = vima_context();

        if (!can($permission, $resource)) {
            throw AccessDeniedException::forPermission($permission);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
