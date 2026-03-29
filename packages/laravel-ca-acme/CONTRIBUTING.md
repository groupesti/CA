# Contributing

Thank you for considering contributing to Laravel CA ACME! This document provides guidelines and instructions for contributing.

## Prerequisites

- PHP 8.4+
- Composer 2
- Git
- A working Laravel 12.x or 13.x application (for integration testing)

## Setup

1. Fork and clone the repository:

```bash
git clone https://github.com/your-fork/laravel-ca-acme.git
cd laravel-ca-acme
composer install
```

2. Run the test suite to confirm everything works:

```bash
./vendor/bin/pest
```

## Branching Strategy

- `main` — stable, release-ready code.
- `develop` — work-in-progress integration branch.
- `feat/` — new features (e.g., `feat/external-account-binding`).
- `fix/` — bug fixes (e.g., `fix/nonce-expiration`).
- `docs/` — documentation updates only.

Always branch from `develop` and submit pull requests targeting `develop`.

## Coding Standards

This project follows the Laravel coding style enforced by Laravel Pint:

```bash
# Check code style
./vendor/bin/pint --test

# Fix code style
./vendor/bin/pint
```

Static analysis is enforced at PHPStan level 9:

```bash
./vendor/bin/phpstan analyse
```

## Tests

We use Pest 3 for testing. All new code must include tests:

```bash
# Run all tests
./vendor/bin/pest

# Run with coverage (minimum 80%)
./vendor/bin/pest --coverage --min=80
```

- **Unit tests** go in `tests/Unit/`.
- **Feature tests** go in `tests/Feature/`.

## Commit Messages

Follow [Conventional Commits](https://www.conventionalcommits.org/):

- `feat:` — a new feature
- `fix:` — a bug fix
- `docs:` — documentation changes only
- `chore:` — maintenance tasks (CI, dependencies)
- `refactor:` — code change that neither fixes a bug nor adds a feature
- `test:` — adding or updating tests

Examples:

```
feat: add external account binding support
fix: prevent nonce reuse after expiration
docs: update configuration table in README
```

## Pull Request Process

1. Fork the repository.
2. Create a feature branch from `develop`.
3. Make your changes with tests and documentation updates.
4. Ensure all checks pass: `pest`, `pint --test`, `phpstan analyse`.
5. Submit a pull request to `develop` using the PR template.
6. Wait for review — address any feedback promptly.

## PHP 8.4 Specifics

When contributing, use PHP 8.4 features where appropriate:

- Readonly classes and properties for DTOs and Value Objects.
- Property hooks and asymmetric visibility where they improve clarity.
- Backed enums (`string`/`int`) instead of class constants.
- Strict typing with union types, intersection types, and `never` where applicable.
- `#[\Override]` attribute on overridden methods.

## Questions?

Open a [GitHub Discussion](https://github.com/groupesti/laravel-ca-acme/discussions) for questions or ideas before starting significant work.
