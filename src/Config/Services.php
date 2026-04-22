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

use CodeIgniter\Config\BaseService;
use Vima\CodeIgniter\Repositories\RoleRepository;
use Vima\CodeIgniter\Repositories\PermissionRepository;
use Vima\CodeIgniter\Repositories\RolePermissionRepository;
use Vima\CodeIgniter\Repositories\UserRoleRepository;
use Vima\CodeIgniter\Repositories\UserPermissionRepository;
use Vima\CodeIgniter\Repositories\UserDenyRepository;
use Vima\Core\Config\VimaConfig;
use Vima\Core\Contracts\AccessManagerInterface;
use Vima\Core\Contracts\RoleRepositoryInterface;
use Vima\Core\Contracts\PermissionRepositoryInterface;
use Vima\Core\Contracts\RolePermissionRepositoryInterface;
use Vima\Core\Contracts\UserRoleRepositoryInterface;
use Vima\Core\Contracts\UserPermissionRepositoryInterface;
use Vima\Core\Contracts\UserDenyRepositoryInterface;
use Vima\Core\Contracts\RoleParentRepositoryInterface;
use Vima\Core\Services\AccessManager;
use Vima\Core\Services\PolicyRegistry;
use Vima\Core\Services\UserResolver;
use Vima\Core\DependencyContainer;
use Vima\Core\Services\AccessResolver;
use Vima\CodeIgniter\Config\ContextStorage;
use Vima\Core\Config\Tables;
use Vima\Core\Config\Columns;
use Vima\Core\Config\RoleColumns;
use Vima\Core\Config\PermissionColumns;
use Vima\Core\Config\UserRoleColumns;
use Vima\Core\Config\RolePermissionColumns;
use Vima\Core\Config\UserPermissionColumns;
use Vima\Core\Config\UserDenyColumns;
use Vima\Core\Config\RoleParentColumns;
use Vima\Core\Config\Setup;
use Vima\Core\Contracts\EventDispatcherInterface;
use Vima\CodeIgniter\Support\CodeIgniterEventDispatcher;
use Vima\Core\Contracts\PolicyRegistryInterface;
use Vima\Core\Services\SyncService;
use Vima\Core\Services\MapGenerator;
use Vima\Core\Services\MappingService;
use Vima\Core\Contracts\CacheInterface;
use Vima\CodeIgniter\Services\CacheAdapter;
use Vima\Core\Services\PermissionManager;
use Vima\Core\Services\RoleManager;
use function Vima\Core\resolve;

if (!class_exists(Services::class, false)) {
    class Services extends BaseService
    {
        public static function vima_config(bool $getShared = true): VimaConfig
        {
            if ($getShared) {
                return static::getSharedInstance('vima_config');
            }

            $ciConfig = config('Vima');
            $setup = $ciConfig->setup ?? new Setup();

            return new VimaConfig(
                tables: $ciConfig->tables ?? new Tables(),
                columns: $ciConfig->columns ?? new Columns(
                    roles: new RoleColumns(),
                    permissions: new PermissionColumns(),
                    userRoles: new UserRoleColumns(),
                    rolePermissions: new RolePermissionColumns(),
                    userPermissions: new UserPermissionColumns(),
                    roleParents: new RoleParentColumns(),
                    userDenies: new UserDenyColumns()
                ),
                setup: $setup,
                userResolver: $ciConfig->userResolver ?? null,
                cacheEnabled: $ciConfig->cacheEnabled ?? false,
                cacheTTL: $ciConfig->cacheTTL ?? 3600,
                cachePrefix: $ciConfig->cachePrefix ?? 'vima:'
            );
        }

        public static function vima_cache(bool $getShared = true): CacheInterface
        {
            if ($getShared) {
                return static::getSharedInstance('vima_cache');
            }

            return new CacheAdapter(cache());
        }

        /**
         * Main Vima Access Manager
         */
        public static function vima(bool $getShared = true): AccessManagerInterface
        {
            if ($getShared) {
                return static::getSharedInstance('vima');
            }

            // reset to ensure new instance
            DependencyContainer::reset();

            // Initialize Vima Core Container with CI4 implementations
            $container = DependencyContainer::getInstance();

            $container->register(RoleRepositoryInterface::class, fn() => service('vima_roles'));
            $container->register(PermissionRepositoryInterface::class, fn() => service('vima_permissions'));
            $container->register(RolePermissionRepositoryInterface::class, fn() => service('vima_role_permissions'));
            $container->register(UserRoleRepositoryInterface::class, fn() => service('vima_user_roles'));
            $container->register(UserPermissionRepositoryInterface::class, fn() => service('vima_user_permissions'));
            $container->register(RoleParentRepositoryInterface::class, fn() => service('vima_role_parents'));
            $container->register(UserDenyRepositoryInterface::class, fn() => service('vima_user_denies'));

            $container->register(VimaConfig::class, fn() => service('vima_config'));
            $container->register(CacheInterface::class, fn() => service('vima_cache'));

            $container->register(PolicyRegistryInterface::class, PolicyRegistry::instance());
            $container->register(UserResolver::class, fn() => new UserResolver(service('vima_config')));

            $container->register(EventDispatcherInterface::class, fn() => service('vima_events'));

            // Register Manager services for auto-wiring
            $container->register(RoleManager::class);
            $container->register(PermissionManager::class);

            $container->register(AccessManagerInterface::class, AccessManager::class);

            // AccessManager itself
            return resolve(AccessManagerInterface::class);
        }

        public static function vima_events(bool $getShared = true): EventDispatcherInterface
        {
            if ($getShared) {
                return static::getSharedInstance('vima_events');
            }
            return new CodeIgniterEventDispatcher();
        }

        public static function vima_roles(bool $getShared = true): RoleRepositoryInterface
        {
            if ($getShared) {
                return static::getSharedInstance('vima_roles');
            }
            return new RoleRepository();
        }

        public static function vima_permissions(bool $getShared = true): PermissionRepositoryInterface
        {
            if ($getShared) {
                return static::getSharedInstance('vima_permissions');
            }
            return new PermissionRepository();
        }

        public static function vima_role_permissions(bool $getShared = true): RolePermissionRepositoryInterface
        {
            if ($getShared) {
                return static::getSharedInstance('vima_role_permissions');
            }
            return new RolePermissionRepository();
        }

        public static function vima_user_roles(bool $getShared = true): UserRoleRepositoryInterface
        {
            if ($getShared) {
                return static::getSharedInstance('vima_user_roles');
            }
            return new UserRoleRepository();
        }

        public static function vima_user_permissions(bool $getShared = true): UserPermissionRepositoryInterface
        {
            if ($getShared) {
                return static::getSharedInstance('vima_user_permissions');
            }
            return new UserPermissionRepository();
        }

        public static function vima_user_denies(bool $getShared = true): UserDenyRepositoryInterface
        {
            if ($getShared) {
                return static::getSharedInstance('vima_user_denies');
            }
            return new UserDenyRepository();
        }

        public static function vima_role_parents(bool $getShared = true): RoleParentRepositoryInterface
        {
            if ($getShared) {
                return static::getSharedInstance('vima_role_parents');
            }
            return new \Vima\CodeIgniter\Repositories\RoleParentRepository();
        }

        /**
         * Request-scoped context storage
         */
        public static function vima_context(bool $getShared = true)
        {
            if ($getShared) {
                return static::getSharedInstance('vima_context');
            }

            return new ContextStorage();
        }

        /**
         * Access Resolver for verifying roles/permissions against Setup
         */
        public static function vima_resolver(bool $getShared = true): AccessResolver
        {
            if ($getShared) {
                return static::getSharedInstance('vima_resolver');
            }

            return new AccessResolver(
                service('vima_config')->setup,
                service('vima_roles'),
                service('vima_permissions')
            );
        }

        public static function vima_sync(bool $getShared = true): SyncService
        {
            if ($getShared) {
                return static::getSharedInstance('vima_sync');
            }

            return new SyncService(
                service('vima_roles'),
                service('vima_permissions'),
                service('vima_role_permissions'),
                service('vima_events'),
                service('vima_cache')
            );
        }

        public static function vima_map_generator(bool $getShared = true): MapGenerator
        {
            if ($getShared) {
                return static::getSharedInstance('vima_map_generator');
            }

            $rootPath = defined('ROOTPATH') ? ROOTPATH : getcwd() . DIRECTORY_SEPARATOR;
            $mappingFile = $rootPath . '.vima' . DIRECTORY_SEPARATOR . 'mapping.json';

            return new MapGenerator(
                new MappingService($mappingFile),
                service('vima_events')
            );
        }
    }
}
