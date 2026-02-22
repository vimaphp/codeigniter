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

        // Run migrations
        $migrate = new CreateVimaTables();
        $migrate->up();
    }

    protected function tearDown(): void
    {
        $migrate = new CreateVimaTables();
        $migrate->down();
        parent::tearDown();
    }
}
