# Contributing to Laravel CA Key

Thank you for considering contributing to Laravel CA Key! This document provides guidelines and instructions for contributing.

## Prerequisites

- **PHP 8.4+** with extensions: `mbstring`, `openssl`, `json`, `pdo`, `sqlite`
- **Composer 2**
- **Git**

## Setup

1. Fork the repository on GitHub.

2. Clone your fork locally:

   ```bash
   git clone git@github.com:YOUR-USERNAME/laravel-ca-key.git
   cd laravel-ca-key
   ```

3. Install dependencies:

   ```bash
   composer install
   ```

4. Verify that tests pass:

   ```bash
   ./vendor/bin/pest
   ```

## Branching Strategy

- `main` -- stable, tagged releases only.
- `develop` -- integration branch for upcoming work.
- `feat/description` -- new features.
- `fix/description` -- bug fixes.
- `docs/description` -- documentation changes.
- `refactor/description` -- code refactoring.
- `test/description` -- test additions or improvements.

Always branch from `develop` and target `develop` in your pull request.

## Coding Standards

This project follows the Laravel coding style, enforced by [Laravel Pint](https://laravel.com/docs/pint):

```bash
# Check formatting
./vendor/bin/pint --test

# Auto-fix formatting
./vendor/bin/pint
```

Static analysis is enforced at PHPStan level 9 via [Larastan](https://github.com/larastan/larastan):

```bash
./vendor/bin/phpstan analyse
```

### PHP 8.4 Specifics

- Use `readonly` classes and properties for DTOs and value objects.
- Use typed properties, parameters, and return types everywhere.
- Use property hooks and asymmetric visibility where they improve clarity.
- Use backed enums instead of class constants for fixed value sets.
- Use named arguments for clarity when calling methods with many parameters.

## Tests

Tests are written with [Pest 3](https://pestphp.com/):

```bash
# Run tests
./vendor/bin/pest

# Run tests with coverage (minimum 80%)
./vendor/bin/pest --coverage --min=80
```

- Place feature tests in `tests/Feature/`.
- Place unit tests in `tests/Unit/`.
- Every new feature or bug fix must include corresponding tests.
- Do not use facade aliases in tests; inject dependencies via the IoC container.

## Commit Messages

Follow [Conventional Commits](https://www.conventionalcommits.org/):

```
feat: add ECDSA P-521 support
fix: handle empty passphrase in key export
docs: update configuration section in README
chore: bump phpseclib to 3.1
refactor: extract PEM-to-DER conversion
test: add rotation test for Ed25519 keys
```

## Pull Request Process

1. Fork the repository and create your branch from `develop`.
2. Make your changes, ensuring tests pass and code is formatted.
3. Update documentation:
   - `CHANGELOG.md` (add entry under `[Unreleased]`)
   - `README.md` if the public API changes
   - `ARCHITECTURE.md` if the `src/` structure changes
4. Fill in the pull request template completely.
5. Submit your pull request targeting `develop`.

### PR Checklist

Before submitting, verify:

- [ ] Tests added or updated (`./vendor/bin/pest`)
- [ ] Code formatted (`./vendor/bin/pint`)
- [ ] PHPStan passes (`./vendor/bin/phpstan analyse`)
- [ ] `CHANGELOG.md` updated
- [ ] Documentation updated

## Code of Conduct

This project follows the [Contributor Covenant Code of Conduct](CODE_OF_CONDUCT.md). By participating, you agree to abide by its terms.

## Questions?

For questions, open a [GitHub Discussion](https://github.com/groupesti/laravel-ca-key/discussions). Issues are reserved for bug reports and feature requests.
