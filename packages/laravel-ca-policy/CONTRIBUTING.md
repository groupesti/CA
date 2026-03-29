# Contributing to Laravel CA Policy

Thank you for considering contributing to Laravel CA Policy! This document provides guidelines and instructions for contributing.

## Prerequisites

- **PHP** 8.4 or higher
- **Composer** 2.x
- **Git**
- **SQLite** (for running tests)
- A working knowledge of Laravel 12.x and PKI/X.509 concepts

## Setup

1. Fork the repository and clone your fork:

```bash
git clone git@github.com:your-username/laravel-ca-policy.git
cd laravel-ca-policy
```

2. Install dependencies:

```bash
composer install
```

3. Run the test suite to confirm everything works:

```bash
./vendor/bin/pest
```

## Branching Strategy

- `main` — stable, release-ready code
- `develop` — work in progress, integration branch
- `feat/description` — new features
- `fix/description` — bug fixes
- `docs/description` — documentation-only changes

Always branch from `develop` for new work.

## Coding Standards

This project follows the Laravel coding style enforced by **Laravel Pint**:

```bash
# Check formatting
./vendor/bin/pint --test

# Fix formatting
./vendor/bin/pint
```

Static analysis is enforced at **PHPStan level 9**:

```bash
./vendor/bin/phpstan analyse
```

Both checks must pass before a PR can be merged.

### PHP 8.4 Specifics

- Use `readonly` classes and properties for DTOs and Value Objects.
- Use PHP enums (backed `string`/`int`) instead of class constants where appropriate.
- Use property hooks and asymmetric visibility when they improve clarity.
- Always type properties, parameters, and return values. Avoid `mixed` without justification.
- Use named arguments for improved readability in complex method calls.

## Tests

Tests are written with **Pest 3**:

```bash
# Run all tests
./vendor/bin/pest

# Run with coverage
./vendor/bin/pest --coverage

# Run a specific test file
./vendor/bin/pest tests/Unit/PolicyEngineTest.php
```

**Minimum code coverage: 80%.** All new features and bug fixes must include tests.

### Test Organization

- `tests/Unit/` — unit tests for individual classes (rules, validators, DTOs)
- `tests/Feature/` — integration tests involving the service provider, database, and HTTP layer

## Commit Messages

Follow [Conventional Commits](https://www.conventionalcommits.org/):

```
feat: add new validation rule for certificate transparency
fix: correct DNS suffix matching for single-label domains
docs: update README with name constraint examples
chore: upgrade PHPStan to v2.1
refactor: extract subtree matching into dedicated methods
test: add coverage for IP CIDR name constraint matching
```

## Pull Request Process

1. Fork the repository.
2. Create a feature branch from `develop`: `git checkout -b feat/my-feature develop`
3. Make your changes, including tests and documentation updates.
4. Ensure all checks pass:
   ```bash
   ./vendor/bin/pest
   ./vendor/bin/pint --test
   ./vendor/bin/phpstan analyse
   ```
5. Push to your fork and open a Pull Request targeting `develop`.
6. Fill in the PR template completely.
7. Wait for code review. Address any feedback promptly.

### PR Checklist

Before submitting, verify:

- [ ] Tests added or updated (`./vendor/bin/pest`)
- [ ] Code formatted (`./vendor/bin/pint`)
- [ ] PHPStan passes (`./vendor/bin/phpstan analyse`)
- [ ] `CHANGELOG.md` updated (section `[Unreleased]`)
- [ ] `README.md` reflects any API or configuration changes
- [ ] `ARCHITECTURE.md` updated if `src/` structure changed

## Reporting Bugs

Use the [bug report template](https://github.com/groupesti/laravel-ca-policy/issues/new?template=bug_report.md). Include:

- Package version
- PHP version (must be 8.4+)
- Laravel version (12.x)
- Steps to reproduce
- Expected vs. actual behavior

## Security Vulnerabilities

Do **not** open a public issue for security vulnerabilities. See [SECURITY.md](SECURITY.md) for reporting instructions.

## Code of Conduct

This project follows the [Contributor Covenant Code of Conduct](CODE_OF_CONDUCT.md). By participating, you agree to abide by its terms.
