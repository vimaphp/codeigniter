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

class VimaPublishAI extends BaseCommand
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
    protected $name = 'vima:publish-ai';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Publishes AI agent guidance to the project root for various IDEs and agents.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'vima:publish-ai {options}';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $options = [
        'ide' => 'The target IDE/Agent (cursor, windsurf, vscode, cline, roocode, vima, jetbrains, all)',
        'overwrite' => 'Overwrite existing files',
    ];

    /**
     * Known AI agent rule locations relative to ROOTPATH.
     * 
     * @var array
     */
    protected $locations = [
        'cursor' => '.cursor/rules/vima.md',
        'windsurf' => '.windsurf/rules/vima.md',
        'vscode' => '.github/rules/vima.md',
        'cline' => '.cline/rules/vima.md',
        'roocode' => '.roo/rules/vima.md',
        'antigravity' => '.agent/skills/vima/SKILL.md',
        'jetbrains' => '.jetbrains/rules/vima.md',
    ];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $ide = CLI::getOption('ide');
        $overwrite = !!CLI::getOption('overwrite');

        if (empty($ide)) {
            $options = array_keys($this->locations);
            sort($options);
            $options[] = 'all';

            $ide = CLI::prompt('Choose target IDE/Agent to publish guidance for', $options);
        }

        if ($ide === 'all') {
            foreach ($this->locations as $key => $path) {
                $this->publish($key, $path, $overwrite);
            }
        } elseif (isset($this->locations[$ide])) {
            $this->publish($ide, $this->locations[$ide], $overwrite);
        } else {
            CLI::error("Unknown IDE/Agent: {$ide}. Supported: " . implode(', ', array_keys($this->locations)) . ', all');
        }

        CLI::write('AI Guidance publishing completed!', 'green');
    }

    /**
     * Publishes the guidance file to the specified location.
     */
    protected function publish(string $id, string $filename, bool $overwrite)
    {
        $source = __DIR__ . '/../../AI/SKILL.md';

        // In a package context, we need to find the AI folder. 
        if (!file_exists($source)) {
            // Fallback for when running from a different location if necessary
            $source = realpath(__DIR__ . '/../../AI/SKILL.md');
        }

        if (!$source || !file_exists($source)) {
            CLI::error("Source guidance file not found at: " . __DIR__ . '/../../AI/SKILL.md');
            return;
        }

        $dest = ROOTPATH . $filename;

        // Ensure directory exists
        $dir = dirname($dest);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                CLI::error("Failed to create directory: {$dir}");
                return;
            }
        }

        if (file_exists($dest)) {
            if (!$overwrite) {
                if (CLI::prompt("File '{$filename}' already exists for {$id}. Overwrite?", ['n', 'y']) !== 'y') {
                    CLI::write("Skipping {$id}...", 'yellow');
                    return;
                }
            }
        }

        if (copy($source, $dest)) {
            CLI::write("Published AI guidance for {$id} to {$filename}", 'green');

            // Also publish workflows if the target supports them (like Vima agent)
            if ($id === 'vima') {
                $this->publishWorkflows();
            }
        } else {
            CLI::error("Failed to publish guidance for {$id} to {$filename}");
        }
    }

    /**
     * Publishes the workflows directory.
     */
    protected function publishWorkflows()
    {
        $sourceDir = __DIR__ . '/../../AI/workflows';
        $destDir = ROOTPATH . '.agent/skills/vima/workflows';

        if (!is_dir($sourceDir)) {
            return;
        }

        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $files = scandir($sourceDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (copy($sourceDir . DIRECTORY_SEPARATOR . $file, $destDir . DIRECTORY_SEPARATOR . $file)) {
                CLI::write("Published workflow: {$file}", 'green');
            }
        }
    }
}
