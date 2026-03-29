# Contributing to Laravel CA TSA

Thank you for considering contributing to Laravel CA TSA! This document provides guidelines and instructions for contributing.

## Prerequisites

- **PHP** 8.4 or higher
- **Composer** 2.x
- **Git**
- A working knowledge of [RFC 3161 (TSA)](https://www.rfc-editor.org/rfc/rfc3161) is helpful but not required.

## Setup

1. Fork the repository on GitHub.

2. Clone your fork locally:

    ```bash
    git clone git@github.com:your-username/laravel-ca-tsa.git
    cd laravel-ca-tsa
    ```

3. Install dependencies:

    ```bash
    composer install
    ```

4. Verify the test suite passes:

    ```bash
    ./vendor/bin/pest
    ```

## Branching Strategy

| Branch | Purpose |
|--------|---------|
| `main` | Stable, release-ready code |
| `develop` | Integration branch for work in progress |
| `feat/*` | New features |
| `fix/*` | Bug fixes |
| `docs/*` | Documentation changes only |

Always create your branch from `develop`:

```bash
git checkout develop
git checkout -b feat/my-new-feature
```

## Coding Standards

### Formatting

This project uses [Laravel Pint](https://laravel.com/docs/pint) with the `@laravel` ruleset:

```bash
./vendor/bin/pint
```

### Static Analysis

All code must pass [PHPStan](https://phpstan.org/) at level 9:

```bash
./vendor/bin/phpstan analyse
```

### PHP 8.4 Specifics

- Use `readonly` classes and properties for DTOs and Value Objects.
- Use property hooks and asymmetric visibility where they improve clarity.
- Use backed enums instead of class constants for finite sets of values.
- Always type properties, parameters, and return values explicitly.

## Tests

This project uses [Pest 3](https://pestphp.com/) for testing:

```bash
# Run the full test suite
./vendor/bin/pest

# Run with coverage (minimum 80% required)
./vendor/bin/pest --coverage --min=80
```

- Every new feature must include tests.
- Every bug fix must include a regression test.
- Place feature tests in `tests/Feature/` and unit tests in `tests/Unit/`.

## Commit Messages

Follow the [Conventional Commits](https://www.conventionalcommits.org/) specification:

| Prefix | Usage |
|--------|-------|
| `feat:` | A new feature |
| `fix:` | A bug fix |
| `docs:` | Documentation only changes |
| `chore:` | Maintenance tasks (CI, dependencies) |
| `refactor:` | Code change that neither fixes a bug nor adds a feature |
| `test:` | Adding or updating tests |

Examples:

```
feat: add nonce validation to TSA request parser
fix: correct accuracy encoding in TSTInfo
docs: update configuration table in README
```

## Pull Request Process

1. Create your feature branch from `develop`.
2. Ensure all tests pass, code is formatted, and PHPStan reports no errors.
3. Update `CHANGELOG.md` under the `[Unreleased]` section.
4. Update `README.md` if your change affects public API, configuration, or usage.
5. Push your branch and open a Pull Request against `develop`.
6. Fill in the PR template completely.
7. Wait for review -- a maintainer will review and provide feedback.

## Code of Conduct

Please read and follow our [Code of Conduct](CODE_OF_CONDUCT.md).
