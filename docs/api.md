# Vima CI4 Implementation API Reference

The CI4 package provides framework-specific integration, helpers, and traits.

## Global Helpers

- `vima(): AccessManagerInterface`
  Returns the Vima service instance.
- `can(string $permission, ...$arguments): bool`
  Check authorization for the current user. Supports resource-based policies.
- `vima_resolve(): AccessResolver`
  Returns the Access Resolver for type-safe role/permission verification.
- `vima_context(?object $context = null): mixed`
  Get or set request-scoped context (shared resource).
- `vima_policy(string $action, callable $callback): void`
  Manually register an ABAC policy callback.

## Traits

### VimaTrait
Used in any Controller to gain authorization powers.

- `authorize(string $permission, ...$arguments): void`
  Enforces a permission check and throws an `AccessDeniedException` if it fails.
- `can(string $permission, ...$arguments): bool`
  Alias for the global `can()` helper.

## Route Filter Support

### Filter Helper
Use `Vima\CodeIgniter\Support\Filter` to generate clean route filter strings.

- `Filter::can(string $permission): string`
  Simple RBAC check on a route.
- `Filter::resource(string $model, int $segment, ?string $resolver = null): string`
  Loads a resource from the database into `vima_context()` for future checks.
- `Filter::policy(string $action, string $callback): string`
  Registers a policy class method for a specific route.
- `Filter::setup(): string`
  Ensures Vima is initialized for the request.

## Derived Constants (Mappers)
When maps are generated (`php spark vima:generate-maps`), you can access these in the `App\Mappers\Vima` namespace.

- `Roles::*`: Stable slugs for roles defined in your setup.
- `Permissions::*`: Stable slugs for permissions defined in your setup.
