<?php

namespace Vima\CodeIgniter\Support;

use CodeIgniter\Config\Services;
use Vima\Core\Contracts\PolicyInterface;
use Vima\Core\Contracts\PolicyRegistryInterface;
use Vima\Core\Services\PolicyRegistry;
use function Vima\Core\resolve;

/**
 * Class Discovery
 * 
 * Handles auto-discovery of Vima components in a CodeIgniter 4 application.
 */
class Discovery
{
    /**
     * Discovers and registers policies in the given namespace/directory.
     *
     * @param string $directory The directory name to scan (e.g., 'Policies')
     * @return void
     */
    public static function discoverPolicies(string $directory = 'Policies'): void
    {
        $locator = Services::locator();
        $files = $locator->listFiles($directory);
        
        /** @var PolicyRegistryInterface $registry */
        $registry = resolve(PolicyRegistryInterface::class);

        foreach ($files as $file) {
            $className = $locator->getClassname($file);
            
            if (!$className || !class_exists($className) || (new \ReflectionClass($className))->isAbstract()) {
                continue;
            }

            if (is_subclass_of($className, PolicyInterface::class)) {
                $registry->registerClass($className::getResource(), $className);
            }
        }
    }
}
