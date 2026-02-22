<?php

namespace Vima\CodeIgniter\Tests\Commands;

use Vima\CodeIgniter\Tests\VimaTestCase;
use CodeIgniter\Test\StreamFilterTrait;

class VimaPolicyGeneratorTest extends VimaTestCase
{
    use StreamFilterTrait;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure Policies directory exists in app
        if (!is_dir(APPPATH . 'Policies')) {
            mkdir(APPPATH . 'Policies', 0777, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up generated files
        if (file_exists(APPPATH . 'Policies/BlogPolicy.php')) {
            unlink(APPPATH . 'Policies/BlogPolicy.php');
        }
        parent::tearDown();
    }

    public function testGeneratorCreatesFileWithCorrectContent()
    {
        command('vima:make:policy BlogPolicy --resource "App\\\\Entities\\\\Blog"');

        $this->assertFileExists(APPPATH . 'Policies/BlogPolicy.php');

        $content = file_get_contents(APPPATH . 'Policies/BlogPolicy.php');
        $this->assertStringContainsString('class BlogPolicy implements PolicyInterface', $content);
        $this->assertStringContainsString('use App\Entities\Blog;', $content);
        $this->assertStringContainsString('return Blog::class;', $content);
        $this->assertStringContainsString('public function canView(object $user, Blog $blog): bool', $content);
    }

    public function testGeneratorOverwritesWithForce()
    {
        file_put_contents(APPPATH . 'Policies/BlogPolicy.php', 'Original Content');

        command('vima:make:policy BlogPolicy --resource "App\\\\Entities\\\\Blog" --force');

        $content = file_get_contents(APPPATH . 'Policies/BlogPolicy.php');
        $this->assertStringNotContainsString('Original Content', $content);
        $this->assertStringContainsString('class BlogPolicy', $content);
    }
}
