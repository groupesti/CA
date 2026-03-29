# Contributing

Thank you for considering contributing to the Laravel CA PKI ecosystem!

## Important: Contribute to Individual Packages

**This is a meta-package.** It contains no source code of its own -- it exists solely to provide a convenient single `composer require` for the entire PKI system.

Contributions such as bug fixes, new features, and improvements should be directed to the **individual sub-packages**:

| Package | Repository |
|---|---|
| `laravel-ca` | [groupesti/laravel-ca](https://github.com/groupesti/laravel-ca) |
| `laravel-ca-key` | [groupesti/laravel-ca-key](https://github.com/groupesti/laravel-ca-key) |
| `laravel-ca-csr` | [groupesti/laravel-ca-csr](https://github.com/groupesti/laravel-ca-csr) |
| `laravel-ca-crt` | [groupesti/laravel-ca-crt](https://github.com/groupesti/laravel-ca-crt) |
| `laravel-ca-crl` | [groupesti/laravel-ca-crl](https://github.com/groupesti/laravel-ca-crl) |
| `laravel-ca-ocsp` | [groupesti/laravel-ca-ocsp](https://github.com/groupesti/laravel-ca-ocsp) |
| `laravel-ca-pkcs12` | [groupesti/laravel-ca-pkcs12](https://github.com/groupesti/laravel-ca-pkcs12) |
| `laravel-ca-oid` | [groupesti/laravel-ca-oid](https://github.com/groupesti/laravel-ca-oid) |
| `laravel-ca-acme` | [groupesti/laravel-ca-acme](https://github.com/groupesti/laravel-ca-acme) |
| `laravel-ca-scep` | [groupesti/laravel-ca-scep](https://github.com/groupesti/laravel-ca-scep) |
| `laravel-ca-est` | [groupesti/laravel-ca-est](https://github.com/groupesti/laravel-ca-est) |
| `laravel-ca-tsa` | [groupesti/laravel-ca-tsa](https://github.com/groupesti/laravel-ca-tsa) |
| `laravel-ca-cms` | [groupesti/laravel-ca-cms](https://github.com/groupesti/laravel-ca-cms) |
| `laravel-ca-policy` | [groupesti/laravel-ca-policy](https://github.com/groupesti/laravel-ca-policy) |
| `laravel-ca-ui` | [groupesti/laravel-ca-ui](https://github.com/groupesti/laravel-ca-ui) |

## When to Contribute Here

Contributions to this meta-package are appropriate only when:

- A new sub-package needs to be added to the dependency list.
- A sub-package needs to be removed or replaced.
- Version constraints need to be adjusted.
- Documentation for the meta-package itself needs to be updated.

## Prerequisites

- **PHP** 8.4+
- **Composer** 2.x
- **Git**

## Branching Strategy

- `main` -- stable, tagged releases only.
- `develop` -- work in progress.
- Branch prefixes: `feat/`, `fix/`, `docs/`, `chore/`.

## Coding Standards (for sub-packages)

Each sub-package follows these standards:

- **Formatting**: Laravel Pint (`./vendor/bin/pint`) with the `@laravel` ruleset.
- **Static Analysis**: PHPStan level 9 (`./vendor/bin/phpstan analyse`) with Larastan.
- **Tests**: Pest 3 (`./vendor/bin/pest --coverage`) with a minimum 80% coverage threshold.
- **PHP 8.4**: Use readonly classes, property hooks, asymmetric visibility, backed enums, and strict typing throughout.

## Commit Messages

Follow [Conventional Commits](https://www.conventionalcommits.org/):

- `feat:` -- new feature
- `fix:` -- bug fix
- `docs:` -- documentation changes
- `chore:` -- maintenance (dependency updates, CI changes)
- `refactor:` -- code restructuring without behavior change
- `test:` -- adding or updating tests

## Pull Request Process

1. Fork the repository.
2. Create a branch from `develop` (e.g., `chore/update-constraints`).
3. Make your changes.
4. Ensure `CHANGELOG.md` is updated.
5. Open a Pull Request targeting `develop`.
6. Fill in the PR template completely.

## Code of Conduct

This project follows the [Contributor Covenant 2.1](CODE_OF_CONDUCT.md). By participating, you agree to abide by its terms.
