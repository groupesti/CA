# Contributing

Thank you for considering contributing to Laravel CA SCEP! This document provides guidelines and instructions for contributing.

## Prerequisites

- **PHP** 8.4 or higher
- **Composer** 2.x
- **Git**
- A working understanding of SCEP (RFC 8894) and PKCS#7/CMS structures

## Setup

1. Fork the repository on GitHub.

2. Clone your fork locally:

```bash
git clone git@github.com:your-username/laravel-ca-scep.git
cd laravel-ca-scep
```

3. Install dependencies:

```bash
composer install
```

4. Verify the setup by running the test suite:

```bash
./vendor/bin/pest
```

## Branching Strategy

- **`main`** — Stable, production-ready code. All releases are tagged from this branch.
- **`develop`** — Work in progress. All feature branches merge here first.

Branch naming conventions:

| Prefix | Purpose | Example |
|--------|---------|---------|
| `feat/` | New features | `feat/renewal-support` |
| `fix/` | Bug fixes | `fix/challenge-validation` |
| `docs/` | Documentation only | `docs/update-readme` |
| `refactor/` | Code refactoring | `refactor/message-parser` |
| `test/` | Test additions or fixes | `test/scep-server-edge-cases` |
| `chore/` | Maintenance tasks | `chore/update-dependencies` |

## Coding Standards

### Laravel Pint

All code must conform to the Laravel coding style enforced by Pint:

```bash
./vendor/bin/pint
```

To check without modifying files:

```bash
./vendor/bin/pint --test
```

### PHPStan

Static analysis must pass at level 9:

```bash
./vendor/bin/phpstan analyse
```

### PHP 8.4 Specifics

This package targets PHP 8.4 exclusively. Use modern PHP features where appropriate:

- **Readonly classes and properties** for DTOs and value objects (e.g., `ScepMessage`).
- **Named arguments** for clarity in method calls with many parameters.
- **Backed enums** (`string` or `int`) instead of class constants for fixed value sets.
- **Union types, intersection types, and `never`** for strict type declarations.
- **Property hooks and asymmetric visibility** where they improve API design.
- **`#[\Override]`** attribute on methods that override parent implementations.

### General Rules

1. Always type properties, parameters, and return values. Avoid `mixed` without justification.
2. Use `final` on classes that are not designed for extension.
3. Use `readonly` on properties that should not change after construction.
4. Inject dependencies via the constructor; do not use facade aliases in tests.
5. Follow PSR-12 as enforced by Laravel Pint.

## Tests

This package uses [Pest](https://pestphp.com/) v3 for testing.

### Running Tests

```bash
./vendor/bin/pest
```

### Running with Coverage

```bash
./vendor/bin/pest --coverage --min=80
```

A minimum coverage threshold of **80%** is enforced in CI.

### Writing Tests

- Place feature tests in `tests/Feature/`.
- Place unit tests in `tests/Unit/`.
- Test file names should end in `Test.php` (e.g., `ScepServerTest.php`).
- Use descriptive test names that explain the expected behavior.

## Commit Messages

Follow the [Conventional Commits](https://www.conventionalcommits.org/) specification:

```
<type>(<scope>): <description>

[optional body]

[optional footer(s)]
```

### Types

| Type | Description |
|------|-------------|
| `feat` | A new feature |
| `fix` | A bug fix |
| `docs` | Documentation changes only |
| `chore` | Maintenance tasks (deps, CI, etc.) |
| `refactor` | Code change that neither fixes a bug nor adds a feature |
| `test` | Adding or updating tests |
| `perf` | Performance improvement |

### Examples

```
feat(server): add support for GetNextCACert operation
fix(parser): handle missing senderNonce attribute gracefully
docs: update configuration table in README
test(challenge): add tests for expired challenge password validation
```

## Pull Request Process

1. **Fork** the repository and create a branch from `develop`.
2. **Implement** your changes following the coding standards above.
3. **Write tests** for any new functionality or bug fixes.
4. **Update documentation** — every code change must be reflected in the relevant `.md` files (see `CLAUDE.md` for the responsibility matrix).
5. **Run the full check suite** before submitting:

```bash
./vendor/bin/pint
./vendor/bin/phpstan analyse
./vendor/bin/pest --coverage --min=80
```

6. **Submit a PR** targeting the `develop` branch.
7. Fill out the PR template completely, including the checklist.

### PR Checklist

Before submitting, verify:

- [ ] Tests added or updated (`./vendor/bin/pest`)
- [ ] Code formatted (`./vendor/bin/pint`)
- [ ] PHPStan passes (`./vendor/bin/phpstan analyse`)
- [ ] `CHANGELOG.md` updated (entry in `[Unreleased]`)
- [ ] `README.md` updated if public API changed
- [ ] `ARCHITECTURE.md` updated if `src/` structure changed

## Code of Conduct

This project follows the [Contributor Covenant Code of Conduct](CODE_OF_CONDUCT.md). By participating, you are expected to uphold this code.

## Questions?

If you have questions about contributing, please open a [GitHub Discussion](https://github.com/groupesti/laravel-ca-scep/discussions) rather than an issue.
