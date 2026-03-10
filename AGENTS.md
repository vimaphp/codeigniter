# Agent Integration Guide: Vima CodeIgniter

This guide is for AI agents working on a CodeIgniter 4 application using the `vima/codeigniter` adapter.

## Core Concepts
This package bridges `vima/core` with CI4, adding helper functions, traits, and route filters.

## Common Tasks

### 1. Checking Permissions
Use the `can()` helper. It handles RBAC, ABAC, and Namespacing automatically.

```php
// Simple RBAC
can('admin.access');

// Namespaced RBAC
can('blog:posts.edit'); 
can('posts.edit', 'blog'); // Alternative

// Policy Check (ABAC)
can('posts.edit', $post); 

// Hybrid (Namespaced + Policy)
can('blog:posts.edit', $post);
```

### 2. Guarding Controllers
Use the `VimaTrait` in your controllers.

```php
use Vima\CodeIgniter\Traits\VimaTrait;

class Blog extends BaseController {
    use VimaTrait;

    public function delete($id) {
        $post = model(PostModel::class)->find($id);
        $this->authorize('posts.delete', $post); // Throws AccessDeniedException
    }
}
```

### 3. Route Filters
Apply filters in `app/Config/Routes.php` using the `Filter` helper.

```php
use Vima\CodeIgniter\Support\Filter as Vima;

$routes->get('admin', 'Admin::index', ['filter' => Vima::can('admin.access')]);
```

### 4. Direct Service Access
If you need the full `AccessManager`, use the `vima()` service helper.

```php
vima()->assignRole($user, 'editor');
```

## Database & Models
- `RoleModel`, `PermissionModel`, and `UserRoleModel` use the `namespace` and `context` (JSON) fields.
- Always ensure `namespace` is included when searching for roles/permissions if the app is multi-tenant.
