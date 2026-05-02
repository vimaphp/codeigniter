<?php

namespace Vima\CodeIgniter\Tests;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Vima\CodeIgniter\Database\Migrations\CreateVimaTables;

abstract class VimaTestCase extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $namespace = 'Vima\CodeIgniter';

    protected function setUp(): void
    {
        parent::setUp();

        // Clear cache
        if (function_exists('vima')) {
            vima()->clearCache();
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public static function setupBeforeClass(): void
    {
        helper('vima');
        vima();
    }
}
