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
use Vima\CodeIgniter\Config\Vima;
use Vima\Core\Exceptions\AccessDeniedException;
use Vima\Core\Services\UserResolver;

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
        $page = $arguments[1] ?? null;

        if (!$permission) {
            return;
        }
        /**
         * @var Vima $config
         */
        $config = config('Vima');

        // Use the can() helper which handles user resolution and vima_context
        $resource = vima_context();
        $user = $config->currentUser ? ($config->currentUser)() : null;
        $userResolver = new UserResolver();

        if (!can($permission, $resource)) {
            if ($page) {
                return redirect()->to($page);
            }

            throw AccessDeniedException::forPermission($permission, $user, $userResolver);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
