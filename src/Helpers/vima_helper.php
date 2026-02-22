<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/vimaphp>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


use Config\Services;
use Vima\Core\Contracts\AccessManagerInterface;
use Vima\Core\Services\AccessResolver;

if (!function_exists('vima')) {
    /**
     * Returns the Vima Access Manager service.
     */
    function vima(): AccessManagerInterface
    {
        return Services::vima();
    }
}

if (!function_exists('can')) {
    /**
     * Check if the current user has the given permission.
     * 
     * @param string $permission
     * @param mixed ...$arguments
     * @return bool
     */
    function can(string $permission, ...$arguments): bool
    {
        $user = null;
        $config = config('Vima');

        if ($config && isset($config->currentUser) && is_callable($config->currentUser)) {
            $user = call_user_func($config->currentUser);
        } else {
            try {
                if (function_exists('auth')) {
                    $user = auth()->user();
                } else {
                    $user = service('auth')->user();
                }
            } catch (\Throwable $e) {
                $user = null;
            }
        }

        if (!$user) {
            throw new \Exception("Vima could not resolve the current user. Please ensure a user is logged in or define 'currentUser' in your Vima configuration.");
        }

        return vima()->can($user, $permission, ...$arguments);
    }
}

if (!function_exists('vima_context')) {
    /**
     * Get or set request-scoped context object.
     */
    function vima_context(?object $context = null)
    {
        $storage = Services::vima_context();
        if ($context !== null) {
            $storage->set($context);
        }
        return $storage->get();
    }
}

if (!function_exists('vima_policy')) {
    /**
     * Define a new policy.
     */
    function vima_policy(string $action, callable $callback): void
    {
        vima()->govern($action, $callback);
    }
}

if (!function_exists('vima_resolve')) {
    /**
     * Resolve a role or permission against the Setup configuration.
     * 
     * @return AccessResolver
     */
    function vima_resolve(): AccessResolver
    {
        return Services::vima_resolver();
    }
}
