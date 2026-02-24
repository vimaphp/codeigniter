<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/vimaphp>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Vima\CodeIgniter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Vima\Core\Services\SyncService;
use Vima\Core\Config\VimaConfig;

class VimaSync extends BaseCommand
{
    protected $group = 'Vima';
    protected $name = 'vima:sync';
    protected $description = 'Sync permissions and roles from config to storage';
    protected $usage = 'vima:sync [options]';
    protected $options = [
        'refresh' => 'Clear existing permissions and roles before syncing',
    ];

    public function run(array $params)
    {
        $refresh = !!CLI::getOption('refresh');
        $config = config('Vima', false); // ensure to always get the latest config updates

        // Convert CI4 Config to VimaConfig
        $vimaConfig = new VimaConfig(
            tables: $config->tables,
            columns: $config->columns,
            setup: $config->setup
        );

        if ($refresh) {
            if (CLI::prompt('Are you sure you want to refresh? This will delete all existing permissions, roles and any related user data.', ['n', 'y']) !== 'y') {
                CLI::write('Sync cancelled.', 'yellow');
                return;
            }

            CLI::write('Refreshing storage before sync...', 'yellow');
        }

        CLI::write('Syncing permissions and roles...', 'yellow');

        try {
            /** @var SyncService $syncService */
            $syncService = new SyncService(
                roles: service('vima_roles'),
                permissions: service('vima_permissions'),
                rolePermissions: service('vima_role_permissions')
            );

            if ($refresh) {
                $syncService->refresh();
            }

            $response = $syncService->sync($vimaConfig);

            if($response->warn) {
                CLI::write(strtoupper('warning:'), 'black', 'yellow');
                CLI::write('Some roles and permissions have been skipped');

                $roles = $response->skipped->roles;
                $permissions = $response->skipped->permssions;
                $bodyMapper = fn ($name,$reason) => [$name, $reason];

                if (!empty($roles)) {
                    $body = array_map($bodyMapper, $roles);

                    CLI::newLine();
                    CLI::table($body, ['Role', 'Reason']);
                }
                
                if (!empty($permissions)) {
                    $body = array_map($bodyMapper, $permissions);

                    CLI::newLine();
                    CLI::table($body, ['Permission', 'Reason']);
                }
            }

            CLI::write('Sync completed successfully!', 'green');

            // Automatically generate maps
            $this->call('vima:generate-maps');
        } catch (\Throwable $e) {
            CLI::error('Sync failed: ' . $e->getMessage());
        }
    }
}
