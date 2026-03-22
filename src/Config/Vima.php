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

use CodeIgniter\Config\BaseConfig;
use RuntimeException;
use Vima\Core\Config\Setup;
use Vima\Core\Config\Tables;
use Vima\Core\Config\Columns;
use Vima\Core\Config\RoleColumns;
use Vima\Core\Config\PermissionColumns;
use Vima\Core\Config\RolePermissionColumns;
use Vima\Core\Config\UserRoleColumns;
use Vima\Core\Config\UserPermissionColumns;
use Vima\CodeIgniter\Libraries\Setup as SetupLibrary;
use Vima\Core\Config\RoleParentColumns;
use Vima\Core\Contracts\PolicyInterface;
use Vima\Core\Contracts\PolicyRegistryInterface;
use Vima\Core\Contracts\SetupProviderInterface;
use Vima\Core\Services\PolicyRegistry;
use function Vima\Core\resolve;

class Vima extends BaseConfig
{
    /**
     * Table names.
     */
    public Tables $tables;

    /**
     * Column names mapping.
     */
    public Columns $columns;

    /**
     * Declarative setup for roles and permissions.
     */
    public Setup $setup;

    /**
     * List of setup providers.
     * @var class-string<SetupProviderInterface>[]
     */
    public array $providers = [
        SetupLibrary::class,
    ];

    /**
     * List of policy classes that implement PolicyInterface.
     * @var string[]
     */
    public array $policies = [];

    /**
     * Callback or Closure to resolve the current user object.
     * signature: fn() => object|null
     */
    public $currentUser = null;

    /**
     * Callback or Closure to resolved the user's ID/Primary Key from a user object/array.
     * signature: fn($user) => string|int
     */
    public $userResolver = null;

    /**
     * Optional ID Resolver for hashed route segments. Used with the Vima::resource() filter
     * signature: fn($id) => mixed
     */
    public $routeSegmentResolver = null;

    /**
     * Whether to enable authorization results caching.
     */
    public bool $cacheEnabled = false;

    /**
     * Cache Time-To-Live in seconds.
     */
    public int $cacheTTL = 3600;

    /**
     * Prefix for cache keys.
     */
    public string $cachePrefix = 'vima:';

    public function __construct()
    {
        parent::__construct();

        $this->tables = new Tables();
        $this->columns = new Columns(
            roles: new RoleColumns(),
            permissions: new PermissionColumns(),
            rolePermissions: new RolePermissionColumns(),
            userRoles: new UserRoleColumns(),
            userPermissions: new UserPermissionColumns(),
            roleParents: new RoleParentColumns()
        );

        $this->setup = $this->resolveSetup();
        $this->registerPolicies();
    }

    protected function resolveSetup(): Setup
    {
        $roles = [];
        $permissions = [];

        foreach ($this->providers as $provider) {
            $data = new $provider()->get();

            if (isset($data['roles'])) {
                $roles = array_merge($roles, $data['roles']);
            }

            if (isset($data['permissions'])) {
                $permissions = array_merge($permissions, $data['permissions']);
            }
        }

        return new Setup($roles, $permissions);
    }
    private function registerPolicies()
    {
        /**
         * @var PolicyRegistry
         */
        $policyRegistry = resolve(PolicyRegistryInterface::class);

        foreach ($this->policies as $p) {
            if (!class_exists($p)) {
                throw new RuntimeException("[Vima] Class $p does not exist");
            }

            $instance = new $p();

            if (!($instance instanceof PolicyInterface)) {
                throw new RuntimeException("[Vima] Policy class $p is invalid. Policies must implement PolicyInterface::class");
            }

            $policyRegistry->registerClass($instance::getResource(), $p);
        }
    }
}
