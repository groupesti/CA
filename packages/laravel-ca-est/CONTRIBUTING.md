# Contributing to Laravel CA EST

Thank you for considering contributing to this project! This document outlines the process and guidelines.

## Prerequisites

- PHP 8.4+
- Composer 2
- Git
- A working understanding of the EST protocol (RFC 7030)

## Setup

```bash
git clone https://github.com/groupesti/laravel-ca-est.git
cd laravel-ca-est
composer install
```

## Branching Strategy

- `main` -- stable, release-ready code.
- `develop` -- work in progress, integration branch.
- `feat/` -- new features (e.g. `feat/cmc-full-support`).
- `fix/` -- bug fixes (e.g. `fix/reenrollment-subject-match`).
- `docs/` -- documentation-only changes.

Always branch from `develop` and open PRs targeting `develop`.

## Coding Standards

This project follows the Laravel coding style enforced by [Laravel Pint](https://laravel.com/docs/pint):

```bash
./vendor/bin/pint
```

Static analysis is enforced at PHPStan level 9:

```bash
./vendor/bin/phpstan analyse
```

### PHP 8.4 Specifics

- Use `readonly` classes and properties for DTOs and Value Objects.
- Use property hooks and asymmetric visibility where they improve clarity.
- Use backed enums instead of class constants.
- Always type properties, parameters, and return values.

## Tests

Tests are written with [Pest 3](https://pestphp.com/):

```bash
./vendor/bin/pest
./vendor/bin/pest --coverage
```

Minimum code coverage: **80%**.

All new features and bug fixes must include tests.

## Commit Messages

This project uses [Conventional Commits](https://www.conventionalcommits.org/):

- `feat:` -- a new feature
- `fix:` -- a bug fix
- `docs:` -- documentation changes
- `chore:` -- maintenance tasks
- `refactor:` -- code refactoring without behavior change
- `test:` -- adding or updating tests

Examples:

```
feat: add support for ecdsa-p521 key generation
fix: correct subject DN matching in re-enrollment
docs: update configuration table in README
```

## Pull Request Process

1. Fork the repository.
2. Create a feature branch from `develop`.
3. Make your changes with tests.
4. Ensure all checks pass: Pest, Pint, PHPStan.
5. Update `CHANGELOG.md` under `[Unreleased]`.
6. Update `README.md` if your change affects the public API or configuration.
7. Open a Pull Request against `develop` using the PR template.

## Code of Conduct

This project follows the [Contributor Covenant Code of Conduct](CODE_OF_CONDUCT.md). By participating, you agree to uphold this code.
