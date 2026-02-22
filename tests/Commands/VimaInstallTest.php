<?php

namespace Vima\CodeIgniter\Tests\Commands;

use Vima\CodeIgniter\Tests\VimaTestCase;
use CodeIgniter\Test\StreamFilterTrait;

class VimaInstallTest extends VimaTestCase
{
    use StreamFilterTrait;

    protected $composerPath;
    protected $configPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->composerPath = ROOTPATH . 'composer.json';
        $this->configPath = APPPATH . 'Config/Vima.php';

        // Backup existing composer.json
        if (file_exists($this->composerPath)) {
            copy($this->composerPath, $this->composerPath . '.bak');
        } else {
            file_put_contents($this->composerPath, json_encode(['require' => []]));
        }

        // Remove config if exists
        if (file_exists($this->configPath)) {
            rename($this->configPath, $this->configPath . '.bak');
        }
    }

    protected function tearDown(): void
    {
        // Restore backups
        if (file_exists($this->composerPath . '.bak')) {
            rename($this->composerPath . '.bak', $this->composerPath);
        }
        if (file_exists($this->configPath . '.bak')) {
            rename($this->configPath . '.bak', $this->configPath);
        }
        parent::tearDown();
    }

    public function testInstallPublishesConfig()
    {
        command('vima:setup --overwrite');

        $this->assertFileExists($this->configPath);
        $content = file_get_contents($this->configPath);
        $this->assertStringContainsString('namespace Config;', $content);
        $this->assertStringContainsString('class Vima extends BaseVima', $content);
    }
}
