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

class VimaSetup extends BaseCommand
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
    protected $name = 'vima:setup';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Sets up Vima in the current project';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'vima:setup {options}';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $options = [
        'overwrite' => 'Overwrite existing files',
    ];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $overwrite = !!CLI::getOption('overwrite');

        $this->publishSetup($overwrite);
        $this->publishConfig($overwrite);
        $this->registerHelper();

        CLI::write('Vima setup completed!', 'green');
    }

    /**
     * Publishes the default Vima config file to the application.
     */
    protected function publishConfig(bool $overwrite = false)
    {
        $source = __DIR__ . '/../Config/Vima.php';
        $dest = APPPATH . 'Config/Vima.php';

        if (file_exists($dest)) {
            if (!$overwrite) {
                CLI::write('Config file already exists. Use --overwrite to overwrite it.', 'yellow');
                return;
            }

            // inform of file overwrite
            if (CLI::prompt('Config file already exists. Overwrite?', ['n', 'y']) !== 'y') {
                return;
            }

            unlink($dest);
        }

        if (copy($source, $dest)) {
            // Adjust namespace in the copied file
            $content = file_get_contents($dest);

            $search[] = "namespace Vima\CodeIgniter\Config;";
            $search[] = "use CodeIgniter\Config\BaseConfig;";
            $search[] = "class Vima extends BaseConfig";
            $search[] = "use Vima\CodeIgniter\Libraries\Setup as SetupLibrary;";
            $replace[] = "namespace Config;";
            $replace[] = "use Vima\CodeIgniter\Config\Vima as BaseVima;";
            $replace[] = "class Vima extends BaseVima";
            $replace[] = "use App\Libraries\Vima\Setup as SetupLibrary;";

            $content = str_replace($search, $replace, $content);

            file_put_contents($dest, $content);
        } else {
            CLI::error('Failed to publish config file.');
        }
    }

    /**
     * Publishes the default Vima config file to the application.
     */
    protected function publishSetup(bool $overwrite = false)
    {
        $source = __DIR__ . '/../Libraries/Setup.php';
        $dest = APPPATH . 'Libraries/Vima/Setup.php';

        $desDir = dirname($dest);

        if (!is_dir($desDir)) {
            mkdir($desDir, 0777, true);
        }

        if (file_exists($dest)) {
            if (!$overwrite) {
                CLI::write('Setup file already exists. Use --overwrite to overwrite it.', 'yellow');
                return;
            }

            if (CLI::prompt('Setup file already exists. Overwrite?', ['n', 'y']) !== 'y') {
                return;
            }

            unlink($dest);
        }

        if (copy($source, $dest)) {
            // Adjust namespace in the copied file
            $content = file_get_contents($dest);

            $search[] = "namespace Vima\CodeIgniter\Libraries;";
            $replace[] = "namespace App\Libraries\Vima;";

            $content = str_replace($search, $replace, $content);

            file_put_contents($dest, $content);
        } else {
            CLI::error('Failed to publish setup file.');
        }
    }

    /**
     * Helps with registering the helper in Autoload.php
     */
    protected function registerHelper()
    {
        $path = APPPATH . 'Config/Autoload.php';
        if (!file_exists($path))
            return;

        $content = file_get_contents($path);
        $helperName = 'Vima\CodeIgniter\Helpers\vima';

        if (strpos($content, $helperName) !== false) {
            return;
        }

        // Search for $helpers array
        $pattern = '/(public \$helpers = \[)(.*?)(\];)/s';
        if (preg_match($pattern, $content, $matches)) {
            $currentHelpers = trim($matches[2]);
            if (empty($currentHelpers)) {
                $newHelpers = "\n        '{$helperName}'\n    ";
            } else {
                $newHelpers = $currentHelpers . ",\n        '{$helperName}'";
            }
            $content = str_replace($matches[0], $matches[1] . $newHelpers . $matches[3], $content);
            file_put_contents($path, $content);
        } else {
            CLI::error('Failed to register helper.');
        }
    }
}
