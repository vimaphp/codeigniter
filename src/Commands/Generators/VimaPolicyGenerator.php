<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/vimaphp>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Vima\CodeIgniter\Commands\Generators;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\GeneratorTrait;

class VimaPolicyGenerator extends BaseCommand
{
    use GeneratorTrait;

    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Generators';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'vima:make:policy';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Generates a new Vima policy file.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'vima:make:policy <name> [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'name' => 'The policy class name',
    ];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [
        '--resource' => 'The resource class this policy handles (e.g. App\Entities\Post)',
        '--namespace' => 'Set base namespace of the generated class. Default: App\Policies',
        '--force' => 'Force overwrite existing file',
    ];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $this->component = 'Policy';
        $this->directory = 'Policies';
        $this->template = 'policy.tpl.php';

        $this->execute($params);
    }

    /**
     * Prepare the code for the generated class.
     */
    protected function prepare(string $class): string
    {
        $resourceFullClass = $this->getOption('resource') ?? 'App\Entities\Resource';

        // Extract class name from full namespace
        $parts = explode('\\', $resourceFullClass);
        $resourceClass = end($parts);
        $resourceVar = lcfirst($resourceClass);

        return $this->parseTemplate(
            $class,
            ['{resourceFullClass}', '{resourceClass}', '{resourceVar}'],
            [$resourceFullClass, $resourceClass, $resourceVar]
        );
    }

    /**
     * Gets the full path to the template file.
     */
    protected function renderTemplate(array $params): string
    {
        return file_get_contents(realpath(__DIR__ . '/../../Views/Generators/policy.tpl.php'));
    }
}
