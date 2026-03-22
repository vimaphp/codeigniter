<?php

use CodeIgniter\Events\Events;

Events::on('pre_system', static function () {
    // initialzes the dependecy container
    vima();
});

Events::on('pre_command', static function () {
    // initialzes the dependecy container
    vima();
});