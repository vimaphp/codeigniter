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
use Vima\Core\Config\Setup;
use Vima\Core\Config\Tables;
use Vima\Core\Config\Columns;
use Vima\Core\Config\RoleColumns;
use Vima\Core\Config\PermissionColumns;
use Vima\Core\Config\RolePermissionColumns;
use Vima\Core\Config\UserRoleColumns;
use Vima\Core\Config\UserPermissionColumns;
use Vima\CodeIgniter\Libraries\Setup as SetupLibrary;

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

    public function __construct()
    {
        parent::__construct();

        $this->tables = new Tables();
        $this->columns = new Columns(
            roles: new RoleColumns(),
            permissions: new PermissionColumns(),
            rolePermissions: new RolePermissionColumns(),
            userRoles: new UserRoleColumns(),
            userPermissions: new UserPermissionColumns()
        );

        $this->setup = new Setup(...SetupLibrary::get());
    }
}
