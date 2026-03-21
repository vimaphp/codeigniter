<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/vimaphp>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Vima\CodeIgniter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Vima\Core\Contracts\CacheInterface;

class VimaCacheClear extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Vima';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'vima:cache-clear';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Clears the Vima authorization cache';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'vima:cache-clear';

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        CLI::write('Clearing Vima cache...', 'yellow');

        try {
            /** @var CacheInterface $cache */
            $cache = service('vima_cache');
            $cache->clear();

            CLI::write('Vima cache cleared successfully.', 'green');
        } catch (\Throwable $e) {
            CLI::error('Failed to clear Vima cache: ' . $e->getMessage());
        }
    }
}
