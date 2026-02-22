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
use Vima\Core\Services\MappingService;
use Vima\Core\Services\MapGenerator;

class VimaGenerateMaps extends BaseCommand
{
    protected $group = 'Vima';
    protected $name = 'vima:generate-maps';
    protected $description = 'Generate PHP mapper classes for roles and permissions';
    protected $usage = 'vima:generate-maps';

    public function run(array $params)
    {
        $config = service('vima_config');
        $rootPath = defined('ROOTPATH') ? ROOTPATH : getcwd() . DIRECTORY_SEPARATOR;

        $vimaDir = $rootPath . '.vima' . DIRECTORY_SEPARATOR;
        $mappingFile = $vimaDir . 'mapping.json';

        $outputDir = APPPATH . 'Mappers' . DIRECTORY_SEPARATOR . 'Vima' . DIRECTORY_SEPARATOR;
        $namespace = 'App\Mappers\Vima';

        CLI::write('Initializing Vima Mapper...', 'yellow');

        try {
            $mappingService = new MappingService($mappingFile);
            $generator = new MapGenerator($mappingService);

            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            // Generate Roles
            CLI::write('Generating Roles mapper...', 'yellow');
            $rolesClass = $generator->generateRoles($config->setup, $namespace);
            file_put_contents($outputDir . 'Roles.php', $rolesClass);

            // Generate Permissions
            CLI::write('Generating Permissions mapper...', 'yellow');
            $permsClass = $generator->generatePermissions($config->setup, $namespace);
            file_put_contents($outputDir . 'Permissions.php', $permsClass);

            CLI::write('Mapper classes generated successfully in ' . $namespace, 'green');
            CLI::write('Mapping file updated: ' . $mappingFile, 'green');
        } catch (\Throwable $e) {
            CLI::error('Generation failed: ' . $e->getMessage());
            log_message('error', '[Vima] Map generation failed: ' . $e->getMessage());
        }
    }
}
