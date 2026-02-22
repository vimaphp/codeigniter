# Vima PHP CI4 Setup Guide

This guide covers the detailed setup process for integrating Vima PHP into your CodeIgniter 4 application.

## 1. Installation

First, pull in the package via composer:

```bash
composer require vima/codeigniter
```

## 2. Setting Up Environment

Vima uses several tables to manage roles and permissions. You can publish the migrations using the provided command:

```bash
php spark vima:setup
```

This will:
- Copy the migration files to your `app/Database/Migrations` directory.
- Create a default `Config/Vima.php` file in your application if it doesn't exist.
- Register the necessary services.

Run the migrations:

```bash
php spark migrate
```

## 3. Configuration

The configuration file `app/Config/Vima.php` allows you to customize table names, column mappings, and your user resolver.

### User Resolver

Vima needs to know how to find the current logged-in user and how to extract its Primary Key. You can define closures in your configuration:

```php
public $currentUser = 'current_user';
public $userResolver = 'user_id_resolver'; // Informs vima on how to resolve a user's primary key

// In a helper or library
function current_user() {
    return auth()->user(); // Example if using Shield
}

function user_id_resolver($user) {
    return $user->id;
}
```

### Route Segment Resolver

If your application uses hashed IDs (e.g., Hashids) in your URLs, you can define a global `routeSegmentResolver` to automatically decode them before Vima attempts to find the resource.

```php
public $routeSegmentResolver = 'decode_hash_id';

// In a helper
function decode_hash_id($id) {
    return (new Hashids())->decode($id)[0] ?? null;
}
```

### Declarative Setup

You can define roles and permissions directly in the `setup` property of the config. Use `Role::define()` and `Permission::define()`:

```php
use Vima\Core\Entities\Role;
use Vima\Core\Entities\Permission;

$this->setup = new Setup(
    roles: [
        Role::define(
            name: 'admin',
            description: 'Full access',
            permissions: ['posts.*']
        ),
    ],
    permissions: [
        Permission::define(name: 'posts.create', description: 'Create posts'),
    ]
);
```
As from v0.0.1 you are provided with a SetupLibrary in Libraries/Vima/Setup.php that you can define all you roles and permmissions in and is automatically linked to setup config.
This makes the config cleaner and with less clutter to work with.

## 4. Registering Policies

Policies are class-based rules for ABAC. Register them in the `$policies` array:

```php
use App\Policies\PostPolicy;

public array $policies = [
    PostPolicy::class,
];
```

For more details on how to use Vima in your code, check the [Usage Guide](usage.md).
