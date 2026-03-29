# Contributing

Thank you for considering contributing to Laravel CA Certificate! This document provides guidelines and instructions for contributing.

## Prerequisites

- **PHP** 8.4+
- **Composer** 2.x
- **Git**
- **SQLite** (for running tests)

## Setup

1. Fork the repository on GitHub.

2. Clone your fork locally:

    ```bash
    git clone git@github.com:your-username/laravel-ca-crt.git
    cd laravel-ca-crt
    ```

3. Install dependencies:

    ```bash
    composer install
    ```

4. Verify everything works:

    ```bash
    ./vendor/bin/pest
    ./vendor/bin/pint --test
    ./vendor/bin/phpstan analyse
    ```

## Branching Strategy

| Branch | Purpose |
|--------|---------|
| `main` | Stable, release-ready code. |
| `develop` | Work in progress, integration branch. |
| `feat/description` | New features. |
| `fix/description` | Bug fixes. |
| `docs/description` | Documentation-only changes. |
| `refactor/description` | Code refactoring without behavior changes. |
| `test/description` | Test additions or improvements. |

Always branch from `develop` and submit PRs back to `develop`.

## Coding Standards

This project follows the Laravel coding style enforced by [Laravel Pint](https://laravel.com/docs/pint):

```bash
# Check for style issues
./vendor/bin/pint --test

# Auto-fix style issues
./vendor/bin/pint
```

Static analysis is performed with [PHPStan](https://phpstan.org/) at **level 9** via [Larastan](https://github.com/larastan/larastan):

```bash
./vendor/bin/phpstan analyse
```

## Tests

Tests are written with [Pest 3](https://pestphp.com/). All new features and bug fixes must include tests.

```bash
# Run all tests
./vendor/bin/pest

# Run tests with coverage (minimum 80% required)
./vendor/bin/pest --coverage --min=80

# Run a specific test file
./vendor/bin/pest tests/Feature/CertificateManagerTest.php
```

### Test organization

- `tests/Unit/` — Unit tests for individual classes (builders, services, models).
- `tests/Feature/` — Feature tests for integrated workflows (issuing, revoking, API endpoints).

## Commit Messages

Follow the [Conventional Commits](https://www.conventionalcommits.org/) specification:

| Prefix | Usage |
|--------|-------|
| `feat:` | New feature. |
| `fix:` | Bug fix. |
| `docs:` | Documentation-only changes. |
| `chore:` | Maintenance tasks (dependencies, CI, tooling). |
| `refactor:` | Code restructuring without behavior change. |
| `test:` | Adding or updating tests. |
| `perf:` | Performance improvements. |

Examples:

```
feat: add OCSP stapling support to CertificateValidator
fix: prevent chain builder infinite loop on self-referencing intermediates
docs: add AD CS template usage examples to README
```

## Pull Request Process

1. **Fork** the repository and create a branch from `develop`.
2. **Write code** following the coding standards above.
3. **Add or update tests** for your changes.
4. **Update documentation**: any code change must be accompanied by updates to the relevant `.md` files (see the documentation responsibility table in `CLAUDE.md`).
5. **Run the full check suite** before submitting:

    ```bash
    ./vendor/bin/pest
    ./vendor/bin/pint --test
    ./vendor/bin/phpstan analyse
    ```

6. **Submit a PR** to `develop` using the [pull request template](/.github/PULL_REQUEST_TEMPLATE.md).
7. **Respond to code review** feedback promptly.

### PR Checklist

Before submitting, ensure:

- [ ] Tests pass (`./vendor/bin/pest`)
- [ ] Code is formatted (`./vendor/bin/pint`)
- [ ] PHPStan passes (`./vendor/bin/phpstan analyse`)
- [ ] `CHANGELOG.md` updated (section `[Unreleased]`)
- [ ] `README.md` reflects any API changes
- [ ] `ARCHITECTURE.md` updated if `src/` structure changed

## PHP 8.4 Specifics

This package targets PHP 8.4+ and uses modern PHP features where appropriate:

- **Readonly properties and classes** for DTOs and value objects.
- **Backed enums** (`string` / `int`) instead of class constants.
- **Named arguments** in public API methods.
- **Union types and intersection types** for strict typing.
- **`#[\Override]` attribute** on interface implementations where applicable.
- **Property hooks and asymmetric visibility** when they improve the design.

## Reporting Bugs

Use the [bug report template](/.github/ISSUE_TEMPLATE/bug_report.md) on GitHub Issues. Include your PHP version (must be 8.4+), Laravel version (12.x or 13.x), and a minimal reproduction.

## Suggesting Features

Use the [feature request template](/.github/ISSUE_TEMPLATE/feature_request.md) on GitHub Issues.

## Code of Conduct

This project follows the [Contributor Covenant Code of Conduct](CODE_OF_CONDUCT.md). By participating, you are expected to uphold this code.
