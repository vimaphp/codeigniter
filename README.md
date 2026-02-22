# Vima PHP - CodeIgniter 4 Bridge

Vima PHP is a powerful, flexible, and developer-friendly access control library for PHP. This package provides the official bridge for CodeIgniter 4, enabling seamless integration of Role-Based Access Control (RBAC) and Attribute-Based Access Control (ABAC) into your CI4 applications.

## Key Features

- **Seamless CI4 Integration**: Works out of the box with CodeIgniter 4 services and filters.
- **RBAC & ABAC support**: Combine static roles with dynamic, context-aware policies.
- **Declarative Setup**: Manage your roles and permissions via configuration.
- **Command Line Support**: Generate policies and manage access via `spark`.
- **Easy-to-use Helpers**: Check permissions using the `can()` helper.

## Installation

Install the bridge via Composer:

```bash
composer require vima/codeigniter
```

Then run the setup command to set up necessary configuration:

```bash
php spark vima:setup
```

## Documentation

For detailed information on how to setup and use Vima in your CI4 project, please refer to the following guides:

- [**Setup Guide**](docs/setup.md) - Detailed instructions on installation and configuration.
- [**Usage Guide**](docs/usage.md) - How to use filters, helpers, and policies in your application.

## Contribution

This package is part of the **Vima PHP** organization. We welcome contributions!

---

(c) Vima PHP <https://github.com/vimaphp>
