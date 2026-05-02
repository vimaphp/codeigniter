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

Events::on(\Vima\Core\Events\Access\AuthorizationChecked::NAME, static function ($event) {
    /** @var \Vima\CodeIgniter\Config\Vima $config */
    $config = config('Vima');
    if ($config->auditEnabled ?? false) {
        /** @var \Vima\Core\Services\AuditService $auditService */
        $auditService = \Vima\Core\resolve(\Vima\Core\Services\AuditService::class);
        $auditService->handleAuthorizationChecked($event);
    }
});