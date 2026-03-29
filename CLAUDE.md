# CLAUDE.md — PHP 8.4 / Laravel 12/13 Open Source Package

## Rôle et contexte

Tu es un expert en développement de packages PHP open source. Quand on te demande de créer ou scaffolder un package, tu **dois** générer l'ensemble des fichiers `.md` décrits ci-dessous, en plus du code source. Tous les fichiers doivent être rédigés en **anglais**, sauf indication contraire explicite.

---

## ⚠️ RÈGLE ABSOLUE — MAINTIEN À JOUR EN TOUT TEMPS

> **Cette règle a priorité sur toutes les autres. Elle ne peut jamais être ignorée, contournée ou omise.**

### Principe fondamental

**Chaque modification apportée au code source DOIT être reflétée immédiatement dans les fichiers `.md` concernés, dans le même commit ou la même PR.** Un fichier `.md` désynchronisé du code est considéré comme une régression, au même titre qu'un test qui échoue.

---

### Tableau de responsabilité — qui mettre à jour et quand

| Action effectuée sur le code | Fichiers `.md` à mettre à jour obligatoirement |
|---|---|
| Ajout d'une nouvelle fonctionnalité | `README.md` (section Usage/Configuration), `CHANGELOG.md` (section `Added`), `ROADMAP.md` (cocher la tâche) |
| Correction d'un bug | `CHANGELOG.md` (section `Fixed`) |
| Changement d'une API publique | `README.md`, `CHANGELOG.md` (section `Changed`), `UPGRADE.md` si breaking change |
| Ajout/suppression d'une dépendance | `README.md` (Requirements), `CONTRIBUTING.md` (Prerequisites) |
| Ajout/suppression d'une option de config | `README.md` (section Configuration), `ARCHITECTURE.md` |
| Nouvelle commande Artisan | `README.md` (section Usage) |
| Nouvelle migration | `UPGRADE.md` si elle affecte des utilisateurs existants |
| Changement des requirements PHP/Laravel | `README.md` (badges + Requirements), `SECURITY.md` (Supported Versions), `composer.json` |
| Release d'une version | `CHANGELOG.md` (transformer `[Unreleased]` en `[x.y.z] - YYYY-MM-DD`), `SECURITY.md`, `ROADMAP.md` |
| Dépreciation d'une feature | `CHANGELOG.md` (section `Deprecated`), `README.md` (note de dépréciation inline) |
| Suppression d'une feature | `CHANGELOG.md` (section `Removed`), `README.md`, `UPGRADE.md` |
| Découverte ou correction de vulnérabilité | `SECURITY.md`, `CHANGELOG.md` (section `Security`) |
| Changement de processus de contribution | `CONTRIBUTING.md` |
| Changement d'architecture interne | `ARCHITECTURE.md` |
| Ajout d'un item au backlog | `ROADMAP.md` |

---

### Règles de mise à jour par fichier

#### `CHANGELOG.md` — mise à jour à chaque commit significatif
- Toute modification non triviale du code → entrée dans `[Unreleased]` immédiatement.
- Ne **jamais** accumuler des changements sans les documenter ; ne **jamais** rédiger le changelog rétrospectivement à partir des commits Git.
- Format strict : `### Added / Changed / Deprecated / Removed / Fixed / Security`.
- Les messages de commit Conventional Commits (`feat:`, `fix:`, etc.) servent de base, mais l'entrée CHANGELOG doit être rédigée pour un **utilisateur humain**, pas un développeur.

#### `README.md` — toujours le miroir exact de l'état actuel
- La section **Usage** doit toujours refléter l'API publique réelle, sans exception.
- La section **Requirements** doit indiquer les versions minimales exactes supportées.
- Si une option de configuration est ajoutée, modifiée ou supprimée dans `config/package-name.php`, la section **Configuration** du README doit être mise à jour dans le **même commit**.
- Les **badges** de version, PHP, Laravel et CI doivent toujours pointer vers les bonnes URLs.

#### `ARCHITECTURE.md` — synchronisé avec la structure `src/`
- Si une classe est ajoutée, déplacée, renommée ou supprimée dans `src/`, la section **Directory structure** et **Key classes** doivent être mises à jour.
- Si une décision de design est révisée, ajouter une entrée dans **Design decisions** avec la date et la justification.

#### `SECURITY.md` — mis à jour à chaque nouvelle version majeure ou mineure
- Le tableau **Supported Versions** doit refléter les versions **actuellement maintenues**, pas celles du passé.
- Dès qu'une version n'est plus supportée, la marquer `:x:` dans le tableau.

#### `ROADMAP.md` — journal de progression vivant
- Cocher `[x]` les items terminés au moment où ils sont mergés dans `main`.
- Déplacer les items dans la bonne section de version au moment de la planification.
- Ne jamais laisser des items terminés dans une section "future".

#### `UPGRADE.md` — obligatoire avant toute release majeure
- Doit exister avant de pousser un tag `vX.0.0`.
- Chaque breaking change doit avoir un exemple **Before** / **After** avec du code PHP réel.
- Mentionner explicitement si des migrations doivent être relancées ou si le fichier de config doit être republié (`php artisan vendor:publish --force`).

---

### Validation avant chaque PR merge

Avant de merger une PR, Claude doit vérifier cette checklist de documentation :

```
[ ] CHANGELOG.md mis à jour (section [Unreleased] complétée)
[ ] README.md reflète les changements (Usage, Configuration, Requirements)
[ ] ARCHITECTURE.md synchronisé si structure src/ modifiée
[ ] ROADMAP.md mis à jour si une tâche planifiée est complétée
[ ] UPGRADE.md créé/mis à jour si breaking change introduit
[ ] SECURITY.md à jour si requirements PHP/Laravel ont changé
[ ] .github/PULL_REQUEST_TEMPLATE.md checklist remplie
```

**Si l'un de ces items n'est pas coché, la PR ne peut pas être mergée.**

---

### Message d'erreur type à afficher si la documentation n'est pas à jour

Si Claude détecte qu'une modification de code n'a pas de mise à jour documentaire correspondante, il doit **arrêter** et afficher :

```
⛔ DOCUMENTATION DÉSYNCHRONISÉE

La modification suivante n'est pas documentée :
→ [décrire la modification]

Fichiers à mettre à jour avant de continuer :
→ [liste des fichiers .md concernés avec les sections précises]

Aucune autre modification de code ne sera effectuée tant que la documentation n'est pas à jour.
```

---

## Stack technique de référence

- **PHP** : 8.4+ (readonly properties, property hooks, asymmetric visibility, `#[\Override]`)
- **Laravel** : 12.x et 13.x (support simultané via `illuminate/support: ^12.0|^13.0`)
- **Composer** : schéma `composer.json` v2 avec autoload PSR-4
- **Tests** : Pest 3.x (`pestphp/pest`, `pestphp/pest-plugin-laravel`)
- **Style** : Laravel Pint (`laravel/pint`) — ruleset `@laravel`
- **Analyse statique** : PHPStan niveau 9 (`phpstan/phpstan`, `larastan/larastan`)
- **CI** : GitHub Actions
- **Versioning** : Semantic Versioning 2.0.0 (SemVer)
- **Changelog** : Keep a Changelog 1.0.0
- **Licence par défaut** : MIT

---

## Fichiers `.md` à générer — règles par fichier

### 1. `README.md` *(obligatoire)*

Structure exacte à respecter :

```
# Package Name

> One-line description of the package.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vendor/package.svg)](...)
[![PHP Version](https://img.shields.io/badge/php-8.4%2B-blue)](...)
[![Laravel](https://img.shields.io/badge/laravel-12.x%20|%2013.x-red)](...)
[![Tests](https://github.com/vendor/package/actions/workflows/tests.yml/badge.svg)](...)
[![License](https://img.shields.io/github/license/vendor/package)](...)

## Requirements
## Installation
## Configuration
## Usage
## Testing
## Changelog
## Contributing
## Security
## Credits
## License
```

- **Requirements** : PHP 8.4+, Laravel 12.x ou 13.x, liste des extensions PHP requises.
- **Installation** : `composer require vendor/package`, publication de config (`php artisan vendor:publish`).
- **Configuration** : décrire chaque clé du fichier `config/package.php`.
- **Usage** : exemples de code avec la syntaxe PHP 8.4 (named arguments, enums, readonly classes).
- **Testing** : `./vendor/bin/pest` et `./vendor/bin/pint --test`.

---

### 2. `CHANGELOG.md` *(obligatoire)*

- Suivre strictement le format **Keep a Changelog 1.0.0**.
- Sections dans chaque version : `Added`, `Changed`, `Deprecated`, `Removed`, `Fixed`, `Security`.
- Version initiale toujours `[Unreleased]` + `[0.1.0] - YYYY-MM-DD`.
- Ne jamais écrire "voir les commits Git" — chaque entrée doit être lisible par un humain.

```markdown
# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - YYYY-MM-DD
### Added
- Initial release.
```

---

### 3. `CONTRIBUTING.md` *(obligatoire)*

Inclure obligatoirement :

1. **Prerequisites** — PHP 8.4, Composer 2, Node (si assets), Git.
2. **Setup** — `git clone`, `composer install`, copier `.env.testing`.
3. **Branching strategy** — `main` (stable), `develop` (WIP), `feat/`, `fix/`, `docs/` prefixes.
4. **Coding standards** — Laravel Pint (`./vendor/bin/pint`), PHPStan niveau 9 (`./vendor/bin/phpstan analyse`).
5. **Tests** — Pest 3 (`./vendor/bin/pest --coverage`), couverture minimale 80 %.
6. **Commit messages** — Conventional Commits (`feat:`, `fix:`, `docs:`, `chore:`, `refactor:`, `test:`).
7. **Pull Request process** — fork → branch → PR vers `develop`, template PR obligatoire.
8. **PHP 8.4 specifics** — utiliser les nouvelles syntaxes (property hooks, asymmetric visibility) quand pertinent.

---

### 4. `CODE_OF_CONDUCT.md` *(obligatoire)*

- Utiliser le texte complet du **Contributor Covenant 2.1** en anglais.
- Remplacer `[INSERT CONTACT METHOD]` par l'email du mainteneur.
- Ajouter une section **Enforcement** claire.

---

### 5. `SECURITY.md` *(obligatoire)*

```markdown
# Security Policy

## Supported Versions

| Version | Supported          |
|---------|--------------------|
| 1.x     | :white_check_mark: |
| < 1.0   | :x:                |

## Reporting a Vulnerability

Please **do not** open a public GitHub issue for security vulnerabilities.

Report vulnerabilities by email to: security@vendor.com
You will receive a response within 72 hours.

Include: affected version, description, reproduction steps, potential impact.

## Disclosure Policy

We follow a 90-day coordinated disclosure policy.
```

---

### 6. `SUPPORT.md` *(recommandé)*

- Liens vers : GitHub Issues (bugs uniquement), GitHub Discussions (questions), documentation officielle.
- Préciser que les Issues ne sont **pas** un forum de support général.
- Mentionner la version de Laravel et PHP dans tout rapport de bug.

---

### 7. `ARCHITECTURE.md` *(recommandé)*

Structure :

1. **Overview** — but du package, problème résolu.
2. **Directory structure** — arbre commenté de `src/`.
3. **Service Provider** — ce qu'il enregistre (bindings, commands, routes, views, migrations, config).
4. **Key classes** — diagramme ASCII ou description de chaque classe principale et ses responsabilités.
5. **Design decisions** — pourquoi ces choix (ex. : pourquoi un Facade, pourquoi un HasMany vs polymorphique).
6. **PHP 8.4 features used** — liste des features PHP 8.4 utilisées et pourquoi.
7. **Extension points** — comment un développeur peut étendre le package (interfaces, événements, macros).

---

### 8. `ROADMAP.md` *(selon le projet)*

```markdown
# Roadmap

## v1.0.0 — Stable Release
- [ ] Feature A
- [ ] Feature B
- [x] Feature C (done)

## v1.1.0 — Planned
- [ ] Feature D

## Ideas / Backlog
- Feature E (not yet scheduled)
```

---

### 9. `UPGRADE.md` *(à créer dès la v2)*

- Une section par breaking change entre versions majeures.
- Toujours inclure : **Before** / **After** avec blocs de code PHP.
- Mentionner les migrations de config et de base de données si applicable.

---

### 10. `.github/PULL_REQUEST_TEMPLATE.md` *(obligatoire)*

```markdown
## Description
<!-- What does this PR do? -->

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Checklist
- [ ] Tests added / updated (`./vendor/bin/pest`)
- [ ] Code formatted (`./vendor/bin/pint`)
- [ ] PHPStan passes (`./vendor/bin/phpstan analyse`)
- [ ] CHANGELOG.md updated
- [ ] Documentation updated
```

---

### 11. `.github/ISSUE_TEMPLATE/bug_report.md` *(obligatoire)*

```markdown
---
name: Bug Report
about: Report a bug in the package
labels: bug
---

**Package version:**
**PHP version:** (must be 8.4+)
**Laravel version:** (12.x or 13.x)

**Description:**

**Steps to reproduce:**

**Expected behavior:**

**Actual behavior:**

**Minimal reproduction:** (GitHub repo or code snippet)
```

---

### 12. `.github/ISSUE_TEMPLATE/feature_request.md` *(obligatoire)*

```markdown
---
name: Feature Request
about: Suggest a new feature or improvement
labels: enhancement
---

**Problem to solve:**

**Proposed solution:**

**Alternatives considered:**

**Additional context:**
```

---

## Conventions de nommage — packages Laravel

| Élément | Convention |
|---|---|
| Namespace racine | `Vendor\PackageName\` |
| Service Provider | `PackageNameServiceProvider` |
| Facade | `PackageName` |
| Config file | `config/package-name.php` |
| Migrations | `YYYY_MM_DD_HHMMSS_create_table_name_table.php` |
| Artisan commands | `package-name:action` |
| Events | `PackageNameEventOccurred` |
| Jobs | `ProcessPackageNameAction` |

---

## Structure de dossiers à générer

```
package-root/
├── .github/
│   ├── workflows/
│   │   ├── tests.yml          ← Pest sur PHP 8.4, Laravel 12 et 13
│   │   └── static-analysis.yml
│   ├── ISSUE_TEMPLATE/
│   │   ├── bug_report.md
│   │   └── feature_request.md
│   └── PULL_REQUEST_TEMPLATE.md
├── config/
│   └── package-name.php
├── database/
│   └── migrations/
├── resources/
│   └── views/
├── routes/
├── src/
│   ├── Commands/
│   ├── Events/
│   ├── Exceptions/
│   ├── Facades/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Jobs/
│   ├── Models/
│   ├── Observers/
│   ├── Policies/
│   ├── Providers/
│   └── PackageNameServiceProvider.php
├── tests/
│   ├── Feature/
│   ├── Unit/
│   └── Pest.php
├── .gitignore
├── ARCHITECTURE.md
├── CHANGELOG.md
├── CLAUDE.md
├── CODE_OF_CONDUCT.md
├── CONTRIBUTING.md
├── LICENSE.md
├── README.md
├── ROADMAP.md
├── SECURITY.md
├── SUPPORT.md
├── composer.json
├── phpstan.neon
├── phpunit.xml
└── pint.json
```

---

## `composer.json` — template de référence

```json
{
    "name": "vendor/package-name",
    "description": "Short description of the package.",
    "type": "library",
    "license": "MIT",
    "keywords": ["laravel", "package-name"],
    "authors": [
        {
            "name": "Your Name",
            "email": "you@example.com",
            "role": "Developer"
        }
    ],
    "require": {
        "php": "^8.4",
        "illuminate/support": "^12.0|^13.0"
    },
    "require-dev": {
        "laravel/pint": "^1.0",
        "larastan/larastan": "^3.0",
        "orchestra/testbench": "^10.0",
        "pestphp/pest": "^3.0",
        "pestphp/pest-plugin-laravel": "^3.0",
        "phpstan/phpstan": "^2.0"
    },
    "autoload": {
        "psr-4": {
            "Vendor\\PackageName\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Vendor\\PackageName\\Tests\\": "tests/"
        }
    },
    "scripts": {
        "test": "./vendor/bin/pest",
        "test-coverage": "./vendor/bin/pest --coverage",
        "format": "./vendor/bin/pint",
        "analyse": "./vendor/bin/phpstan analyse"
    },
    "extra": {
        "laravel": {
            "providers": [
                "Vendor\\PackageName\\PackageNameServiceProvider"
            ],
            "aliases": {
                "PackageName": "Vendor\\PackageName\\Facades\\PackageName"
            }
        }
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}
```

---

## GitHub Actions — `tests.yml` de référence

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    strategy:
      matrix:
        php: ['8.4']
        laravel: ['12.*', '13.*']
        stability: [prefer-stable]

    name: PHP ${{ matrix.php }} - Laravel ${{ matrix.laravel }}

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          extensions: dom, curl, libxml, mbstring, zip, pdo, sqlite, pdo_sqlite
          coverage: xdebug

      - name: Install dependencies
        run: |
          composer require "laravel/framework:${{ matrix.laravel }}" --no-interaction --no-update
          composer update --${{ matrix.stability }} --no-interaction

      - name: Run tests
        run: ./vendor/bin/pest --coverage --min=80
```

---

## Règles générales pour Claude

### Code
1. **Ne jamais utiliser `var` ou le type `mixed` sans justification** — PHP 8.4 permet des types stricts partout.
2. **Toujours typer les propriétés, paramètres et retours** — utiliser les union types, intersection types et `never` si applicable.
3. **Utiliser les `readonly` classes et propriétés** pour les DTOs et Value Objects.
4. **Utiliser les enums PHP 8.1+** (backed enums `string`/`int`) à la place des constantes de classe.
5. **Respecter PSR-12** — Laravel Pint avec ruleset `@laravel` est la source de vérité.
6. **Chaque PR doit passer** : Pest (80 % min), Pint (0 erreur), PHPStan niveau 9.
7. **Ne pas utiliser `facade` alias dans les tests** — injecter les dépendances via le container IoC.

### Documentation — NON NÉGOCIABLE
8. **Toute modification de code = mise à jour documentation immédiate.** Pas après. Pas dans un commit séparé. Pas "à la prochaine PR". Maintenant.
9. **Ne jamais considérer une tâche comme terminée** si les fichiers `.md` concernés ne sont pas à jour. Le code sans documentation est une dette.
10. **Consulter le tableau de responsabilité** (section "RÈGLE ABSOLUE") avant chaque commit pour identifier quels fichiers `.md` doivent être mis à jour.
11. **Le `CHANGELOG.md` est le journal de bord du projet** — chaque entrée dans `[Unreleased]` doit être rédigée au moment du changement, en anglais, pour un utilisateur final.
12. **Avant de proposer un merge**, vérifier la checklist de documentation complète. Une PR sans mise à jour documentaire est rejetée automatiquement.
