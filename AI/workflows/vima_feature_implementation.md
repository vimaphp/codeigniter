---
description: Workflow for implementing a new authorized feature in Vima CI4
---

# Workflow: Implementing Authorized Features with Vima

Follow these steps when adding new functionality that requires permission checks.

## 1. Requirement Analysis
Identify the **Action** (e.g., `audit`) and the **Resource** (e.g., `Account`).
- Is it simple RBAC? (e.g., "Can view admin panel")
- Is it ABAC? (e.g., "Can only view their own account")

## 2. Configuration (`Config/Vima.php`)
Add the new permission to the `$setup` array. Use a logical namespace if applicable.

```php
'permissions' => [
    'accounts.audit' => 'Ability to audit account histories',
],
```

## 3. Synchronization
// turbo
Run the sync command to push the new definition to the database.

```bash
php spark vima:sync
```

## 4. Mapping
// turbo
Generate the PHP constants to avoid using strings in code.

```bash
php spark vima:generate-maps
```

## 5. Policy Implementation (If ABAC)
If the check depends on the resource state:
// turbo
```bash
php spark vima:make:policy AccountPolicy --resource App\Entities\Account
```
Implement the logic in the generated class in `app/Policies/`.

## 6. Route Protection
Apply the `vima_authorize` filter in `app/Config/Routes.php`.

```php
use App\Mappers\Vima\Permissions;
// ...
$routes->get('audit', 'Audit::index', ['filter' => "vima_authorize:".Permissions::ACCOUNTS_AUDIT]);
```

## 7. Controller Enforcement
Use the `VimaTrait` and `authorize()` for granular checks.

```php
use Vima\CodeIgniter\Traits\VimaTrait;
use App\Mappers\Vima\Permissions;

class Audit extends BaseController {
    use VimaTrait;

    public function show($id) {
        $account = model(AccountModel::class)->find($id);
        $this->authorize(Permissions::ACCOUNTS_AUDIT, $account);
        // ...
    }
}
```

## 8. View Visibility
Use the `can()` helper to hide/show UI elements.

```html
<?php if (can(Permissions::ACCOUNTS_AUDIT)): ?>
    <button>Audit Now</button>
<?php endif; ?>
```
