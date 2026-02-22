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
use Config\Services;

/**
 * Filter to handle global Vima setup from configuration.
 * Registers policies defined in the Vima config.
 */
class VimaSetupFilter implements FilterInterface
{
    private static bool $initialized = false;

    public static function reset(): void
    {
        self::$initialized = false;
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        if (self::$initialized) {
            return;
        }

        $config = config('Vima');

        if ($config && isset($config->policies)) {
            $vima = service('vima');
            foreach ($config->policies as $policyClass) {
                if (method_exists($policyClass, 'getResource')) {
                    $resourceClass = $policyClass::getResource();
                    $vima->registerPolicy($resourceClass, $policyClass);
                }
            }
        }

        self::$initialized = true;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
