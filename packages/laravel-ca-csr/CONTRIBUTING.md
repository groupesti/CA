# Contributing

Thank you for considering contributing to Laravel CA CSR! This document provides guidelines and instructions for contributing.

## Prerequisites

- PHP 8.4+
- Composer 2.x
- Git
- SQLite (for testing)

## Setup

1. Fork the repository and clone your fork:

```bash
git clone git@github.com:your-username/laravel-ca-csr.git
cd laravel-ca-csr
```

2. Install dependencies:

```bash
composer install
```

3. Verify the setup by running the test suite:

```bash
./vendor/bin/pest
```

## Branching Strategy

- `main` — stable, production-ready code
- `develop` — work-in-progress integration branch
- `feat/` — new features (e.g., `feat/csr-export`)
- `fix/` — bug fixes (e.g., `fix/validation-error`)
- `docs/` — documentation updates

Always branch from `develop` for new work. Target your pull requests to `develop`.

## Coding Standards

This project follows the Laravel coding style enforced by [Laravel Pint](https://laravel.com/docs/pint).

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

## Tests

Tests are written with [Pest 3](https://pestphp.com/). All new features and bug fixes must include tests.

```bash
# Run the full test suite
./vendor/bin/pest

# Run with coverage (minimum 80% required)
./vendor/bin/pest --coverage --min=80
```

## Commit Messages

This project follows [Conventional Commits](https://www.conventionalcommits.org/):

- `feat:` — new feature
- `fix:` — bug fix
- `docs:` — documentation changes
- `chore:` — maintenance tasks
- `refactor:` — code refactoring without behavior change
- `test:` — adding or updating tests

Examples:

```
feat: add CSR batch import command
fix: correct DN field mapping for serialNumber
docs: update configuration section in README
```

## Pull Request Process

1. Fork the repository.
2. Create a feature branch from `develop`.
3. Make your changes, including tests and documentation updates.
4. Ensure all checks pass:
   - `./vendor/bin/pest`
   - `./vendor/bin/pint --test`
   - `./vendor/bin/phpstan analyse`
5. Update `CHANGELOG.md` under `[Unreleased]`.
6. Submit your PR targeting `develop`.
7. Fill out the PR template completely.

## PHP 8.4 Specifics

This package targets PHP 8.4+ and encourages use of modern syntax:

- **Readonly classes and properties** for DTOs and value objects.
- **Backed enums** (`string`/`int`) instead of class constants where appropriate.
- **Property hooks and asymmetric visibility** when they improve clarity.
- **Named arguments** for constructor calls and method invocations.
- **Union and intersection types** for strict type declarations.
- **`#[\Override]`** attribute on interface implementations.

## Code of Conduct

Please review our [Code of Conduct](CODE_OF_CONDUCT.md) before contributing.

## Questions?

Open a [GitHub Discussion](https://github.com/groupesti/laravel-ca-csr/discussions) for questions or ideas before starting work on large changes.
