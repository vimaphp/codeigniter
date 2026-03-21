# CI4 Event Hooks

This package bridges Vima core events to the CodeIgniter 4 `Events` system. You can listen for any Vima event using `Events::on()`.

## Event Dispatching Logic

Vima events are triggered in CI4 with three different identifiers to provide maximum flexibility:
1.  **Unique Name**: The `NAME` constant defined in each event (e.g., `vima.sync.started`).
2.  **Class Name**: The fully qualified class name of the event (e.g., `Vima\Core\Events\Sync\SyncStarted`).
3.  **Global Event**: A generic `vima.event` name fired for every Vima dispatch.

## Example Usage

### Monitoring Sync Operations
You can perform cleanup or notify external systems after a synchronization.

```php
// app/Config/Events.php

use CodeIgniter\Events\Events;
use Vima\Core\Events\Sync\SyncFinished;

Events::on('vima.sync.finished', function (SyncFinished $event) {
    if ($event->get('response')->hasWarnings()) {
        // Log sync warnings...
    }
});
```

### Listening to Repository Actions
Track when roles or permissions are modified.

```php
// app/Config/Events.php

use CodeIgniter\Events\Events;
use Vima\Core\Events\Repository\RepositoryAction;
use Vima\Core\Entities\Role;

Events::on('vima.repository.action', function (RepositoryAction $event) {
    if ($event->get('action') === RepositoryAction::ACTION_CREATED && $event->get('entity') === Role::class) {
        $role = $event->get('payload');
        // Notify admin about new role...
    }
});
```

### Global Monitoring
A single listener for all Vima events.

```php
Events::on('vima.event', function ($event) {
    // Log any Vima event activity
    $name = method_exists($event, 'getName') ? $event->getName() : get_class($event);
    log_message('info', 'Vima Event: ' . $name);
});
```

## List of Hook Identifiers

| Vima Event | CI4 Name | Payload |
|---|---|---|
| `SyncStarted` | `vima.sync.started` | `VimaConfig`, `refresh` (bool) |
| `SyncFinished` | `vima.sync.finished`| `SyncResponse` |
| `RepositoryAction`| `vima.repository.action` | `action` (slug), `entity` (class), `payload` (entity) |
| `MapGenerated` | `vima.mapping.generated` | `target` (roles/perms), `map` (array) |
