# Contributing to Laravel CA

Thank you for considering contributing to Laravel CA! This document provides guidelines and instructions for contributing.

## Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.4** or higher
- **Composer 2.x**
- **Git**
- **SQLite** (for running tests)

## Setup

1. **Fork the repository** on GitHub.

2. **Clone your fork:**

   ```bash
   git clone git@github.com:your-username/laravel-ca.git
   cd laravel-ca
   ```

3. **Install dependencies:**

   ```bash
   composer install
   ```

4. **Verify your setup:**

   ```bash
   ./vendor/bin/pest
   ./vendor/bin/pint --test
   ./vendor/bin/phpstan analyse
   ```

## Branching Strategy

| Branch | Purpose |
|--------|---------|
| `main` | Stable, release-ready code. Never push directly. |
| `develop` | Integration branch for work in progress. |
| `feat/description` | New features. |
| `fix/description` | Bug fixes. |
| `docs/description` | Documentation-only changes. |
| `refactor/description` | Code refactoring without behavior change. |
| `test/description` | Test additions or improvements. |

Always branch from `develop` and submit your PR back to `develop`.

## Coding Standards

### Laravel Pint

All code must pass Laravel Pint with the `@laravel` ruleset:

```bash
# Check for formatting issues
./vendor/bin/pint --test

# Auto-fix formatting
./vendor/bin/pint
```

### PHPStan Level 9

All code must pass PHPStan at the maximum level:

```bash
./vendor/bin/phpstan analyse
```

### PHP 8.4 Specifics

This package targets PHP 8.4 exclusively. Use modern PHP features where appropriate:

- **Readonly classes and properties** for DTOs and Value Objects.
- **Property hooks** where they improve readability.
- **Asymmetric visibility** when a property should be publicly readable but privately writable.
- **Typed properties, parameters, and return types** everywhere -- avoid `mixed` without justification.
- **Backed enums** (`string` / `int`) instead of class constants where applicable.
- **Named arguments** in method calls for clarity.
- **Union types and intersection types** where appropriate.
- **`#[\Override]`** attribute on overridden methods.
- **`match` expressions** instead of `switch` statements.

## Tests

This project uses **Pest 3** as its testing framework.

```bash
# Run the full test suite
./vendor/bin/pest

# Run with code coverage
./vendor/bin/pest --coverage

# Run a specific test file
./vendor/bin/pest tests/Unit/Services/CaManagerTest.php
```

### Requirements

- **Minimum coverage:** 80%
- Every new feature must include corresponding tests.
- Every bug fix must include a regression test.
- Place feature tests in `tests/Feature/` and unit tests in `tests/Unit/`.
- Do not use facade aliases in tests -- inject dependencies via the IoC container.

## Commit Messages

This project follows **Conventional Commits**:

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
| `docs` | Documentation only changes |
| `style` | Formatting, missing semicolons, etc. (no code change) |
| `refactor` | Code change that neither fixes a bug nor adds a feature |
| `test` | Adding or updating tests |
| `chore` | Maintenance tasks (CI, deps, tooling) |
| `perf` | Performance improvements |

### Examples

```
feat(ca-manager): add support for intermediate CA creation
fix(lookup): resolve cache invalidation on lookup deletion
docs(readme): update configuration section with OCSP options
test(audit-log): add coverage for polymorphic actor relationship
```

## Pull Request Process

1. **Fork** the repository and create a feature branch from `develop`.
2. **Write your code** following the coding standards above.
3. **Add or update tests** to cover your changes.
4. **Run the full validation suite:**

   ```bash
   ./vendor/bin/pest --coverage --min=80
   ./vendor/bin/pint --test
   ./vendor/bin/phpstan analyse
   ```

5. **Update documentation:**
   - Update `CHANGELOG.md` with your changes under `[Unreleased]`.
   - Update `README.md` if you changed the public API, configuration, or added commands.
   - Update `ARCHITECTURE.md` if you added or moved classes in `src/`.

6. **Submit a Pull Request** to the `develop` branch.
7. **Fill in the PR template** completely, ensuring all checklist items are addressed.
8. **Wait for review.** Maintainers will review your PR and may request changes.

### PR Checklist

Before submitting, verify:

- [ ] Tests added or updated (`./vendor/bin/pest`)
- [ ] Code formatted (`./vendor/bin/pint`)
- [ ] PHPStan passes (`./vendor/bin/phpstan analyse`)
- [ ] `CHANGELOG.md` updated
- [ ] Documentation updated (README, ARCHITECTURE, etc.)
- [ ] Commit messages follow Conventional Commits

## Reporting Issues

- Use [GitHub Issues](https://github.com/groupesti/laravel-ca/issues) for bug reports only.
- Always include your PHP version (8.4+), Laravel version, and package version.
- Provide a minimal reproduction (code snippet or repository).
- See the [bug report template](.github/ISSUE_TEMPLATE/bug_report.md) for the expected format.

## Code of Conduct

This project follows the [Contributor Covenant Code of Conduct](CODE_OF_CONDUCT.md). By participating, you are expected to uphold this code.
