# Contributing to Laravel CA UI

Thank you for considering contributing to Laravel CA UI! This document provides guidelines and instructions for contributing.

## Prerequisites

- PHP 8.4+
- Composer 2
- Git
- Node.js (if working on frontend assets)

## Setup

1. Fork the repository on GitHub.
2. Clone your fork locally:

```bash
git clone git@github.com:your-username/laravel-ca-ui.git
cd laravel-ca-ui
composer install
```

3. Create a branch for your work:

```bash
git checkout -b feat/your-feature-name
```

## Branching Strategy

- `main` — Stable, release-ready code.
- `develop` — Work in progress, integration branch.
- `feat/` — New features (e.g., `feat/dark-mode`).
- `fix/` — Bug fixes (e.g., `fix/pagination-offset`).
- `docs/` — Documentation changes only.

## Coding Standards

This project follows Laravel conventions enforced by automated tools:

- **Laravel Pint** for code formatting:

```bash
./vendor/bin/pint
```

- **PHPStan level 9** for static analysis:

```bash
./vendor/bin/phpstan analyse
```

- Use PHP 8.4 features where appropriate: readonly properties, property hooks, asymmetric visibility, `#[\Override]`.
- Always type properties, parameters, and return values.
- Use enums instead of class constants where applicable.

## Tests

We use Pest 3 for testing. Run the test suite with:

```bash
./vendor/bin/pest
```

With coverage (minimum 80% required):

```bash
./vendor/bin/pest --coverage --min=80
```

All new features and bug fixes must include corresponding tests.

## Commit Messages

Follow [Conventional Commits](https://www.conventionalcommits.org/):

- `feat:` — New feature
- `fix:` — Bug fix
- `docs:` — Documentation only
- `chore:` — Maintenance tasks
- `refactor:` — Code restructuring without behavior change
- `test:` — Adding or updating tests

Examples:

```
feat: add dark mode toggle to dashboard
fix: correct pagination offset on certificates list
docs: update configuration section in README
```

## Pull Request Process

1. Fork the repository and create your branch from `develop`.
2. Ensure all tests pass and code is formatted.
3. Update `CHANGELOG.md` with your changes under `[Unreleased]`.
4. Update relevant documentation (README, ARCHITECTURE, etc.).
5. Submit a PR to `develop` using the PR template.
6. Wait for code review and address any feedback.

## PHP 8.4 Specifics

When contributing, prefer modern PHP 8.4 syntax:

- Use `readonly` classes and properties for DTOs and Value Objects.
- Use property hooks for computed properties where it improves clarity.
- Use asymmetric visibility (`public private(set)`) to protect state.
- Use `#[\Override]` attribute when overriding parent methods.
- Use backed enums (`string` or `int`) instead of class constants.

## Code of Conduct

This project follows the [Contributor Covenant Code of Conduct](CODE_OF_CONDUCT.md). By participating, you are expected to uphold this code.
