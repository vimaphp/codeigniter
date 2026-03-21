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

namespace Vima\CodeIgniter\Services;

use CodeIgniter\Cache\CacheInterface as CICacheInterface;
use Vima\Core\Contracts\CacheInterface;

/**
 * Class CacheAdapter
 *
 * Adapts CodeIgniter's Cache service to Vima's CacheInterface.
 *
 * @package Vima\CodeIgniter\Services
 */
class CacheAdapter implements CacheInterface
{
    /**
     * @param CICacheInterface $cache
     */
    public function __construct(
        protected CICacheInterface $cache
    ) {
    }

    /**
     * @inheritDoc
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->cache->get($key);
        return $value === null ? $default : $value;
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        return $this->cache->save($key, $value, $ttl ?? 3600);
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key): bool
    {
        return $this->cache->delete($key);
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        return $this->cache->clean();
    }
}
