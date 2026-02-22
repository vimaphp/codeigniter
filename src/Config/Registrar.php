<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/vimaphp>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vima\CodeIgniter\Config;

use Vima\CodeIgniter\Filters\VimaResourceFilter;
use Vima\CodeIgniter\Filters\VimaSetupFilter;
use Vima\CodeIgniter\Filters\VimaPolicyFilter;
use Vima\CodeIgniter\Filters\VimaAuthorizeFilter;

class Registrar
{
    /**
     * Register filters.
     */
    public static function Filters(): array
    {
        return [
            'aliases' => [
                'vima_setup' => VimaSetupFilter::class,
                'vima_resource' => VimaResourceFilter::class,
                'vima_policy' => VimaPolicyFilter::class,
                'vima_authorize' => VimaAuthorizeFilter::class,
            ],
        ];
    }
}
