# Vima PHP CI4 Usage Guide

This guide explains how to use Vima PHP for authorization within your CodeIgniter 4 application.

## 1. Using the `can()` Helper

The simplest way to check permissions is using the global `can()` helper.

```php
if (can('posts.edit', $post)) {
    // Authorized...
}
```

- If only an ability name is provided (`can('posts.create')`), it performs a standard RBAC check.
- If a resource is provided as the second argument, it automatically triggers policy evaluation (ABAC).

## 2. Controller Authorization

You can use the `authorize()` method in your controllers by using the `VimaTrait`.

```php
use Vima\CodeIgniter\Traits\VimaTrait;

class PostController extends BaseController {
    use VimaTrait;

    public function edit($id) {
        $post = model(PostModel::class)->find($id);
        
        // Throws AccessDeniedException if not authorized
        $this->authorize('posts.edit', $post);
        
        // ...
    }
}
```

## 3. Route Filters

Vima provides several filters to secure your routes. You can use the `Vima\CodeIgniter\Support\Filter` helper to generate filter strings cleanly.

### `VimaAuthorizeFilter`
Requires a specific permission to access a route.

```php
use Vima\CodeIgniter\Support\Filter as Vima;

// app/Config/Routes.php
$routes->get('admin', 'Admin::index', ['filter' => Vima::can('admin.access')]);
```

### `VimaResourceFilter`
Automatically loads a resource from the database based on a URI segment and stores it in the Vima context for subsequent ABAC checks.

```php
use Vima\CodeIgniter\Support\Filter as Vima;

// Loads Post model using segment 3 as ID
$routes->get('posts/edit/(:num)', 'Posts::edit/$1', ['filter' => Vima::resource('PostModel', 3)]);

// Using a specific resolver for hashed IDs
$routes->get('posts/edit/(:num)', 'Posts::edit/$1', [
    'filter' => Vima::resource('PostModel', 3, 'decode_id_func')
]);

// Or using a specific method on the model
$routes->get('posts/edit/(:num)', 'Posts::edit/$1', [
    'filter' => Vima::resource('PostModel', 3, 'findWithHash')
]);
```

### `VimaPolicyFilter`
Registers a policy dynamically. This is useful when combined with other filters.

```php
use Vima\CodeIgniter\Support\Filter as Vima;

$routes->get('posts/edit/(:num)', 'Posts::edit/$1', [
    'filter' => [
        Vima::resource('PostModel', 3),
        Vima::policy('posts.edit', 'App\Policies\PostPolicy::canEdit'),
        Vima::can('posts.edit')
    ]
]);
```

## 4. Writing Policies

Policies are plain PHP classes that implement `Vima\Core\Contracts\PolicyInterface`.

```php
namespace App\Policies;

use Vima\Core\Contracts\PolicyInterface;
use App\Entities\User;
use App\Entities\Post;

class PostPolicy implements PolicyInterface {
    public static function getResource() {
        return Post::class;
    }

    public function canEdit(User $user, Post $post): bool {
        return $user->id === $post->user_id || $user->is_admin;
    }
}
```

Register your policy in `Config/Vima.php` to make it available.

## 5. Type-Safe Mappers & Consistency

Vima generates mapper classes that provide IDE autocompletion and ensure your access control logic remains stable, even if you rename roles or permissions in the database.

### Generating Maps
When you run `php spark vima:sync`, Vima synchronizes your configuration and automatically generates mapping classes in `app/Mappers/Vima/`. You can also trigger this manually:

```bash
php spark vima:generate-maps
```

### Using Constants
Instead of using hardcoded strings, use the generated constants. This provides type safety and lets you use IDE features like "Find Usages".

```php
use App\Mappers\Vima\Permissions;
use App\Mappers\Vima\Roles;

// In a view or controller
if (can(Permissions::POSTS_EDIT, $post)) { ... }

// Using the resolver in a policy to verify existence against Setup
if (vima()->hasRole($user, vima_resolve()->role(Roles::ADMIN))) { ... }
```

### Renaming Roles/Permissions
If you need to rename a role (e.g., from `admin` to `admins`) without breaking your code:

1.  **Update `.vima/mapping.json`**: Find the slug and change its value to the new name.
    ```json
    "roles": {
        "ADMIN": "admins"
    }
    ```
2.  **Update `Setup.php`**: Change the name in your definition to match.
    ```php
    Role::define(name: 'admins', ...)
    ```
3.  **Run Sync**: `php spark vima:sync`.

Your code continues to use `Roles::ADMIN`, but Vima now maps it to the new `admins` string. This maintains a stable application logic even as your system requirements evolve.

---

(c) Vima PHP <https://github.com/vimaphp>
