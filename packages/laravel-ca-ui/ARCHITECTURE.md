# Architecture — laravel-ca-ui (Web Administration Interface)

## Overview

`laravel-ca-ui` provides a Blade-based web administration interface for managing the Certificate Authority system. It offers dashboards with statistics, CRUD views for CAs, certificates, CSRs, keys, and CRLs, and an audit log viewer. It is a presentation-only package that delegates all business logic to the other CA packages. It depends on `laravel-ca` (core), `laravel-ca-key`, `laravel-ca-csr`, `laravel-ca-crt`, `laravel-ca-crl`, and `laravel-ca-ocsp`.

## Directory Structure

```
src/
├── UiServiceProvider.php              # Registers middleware, routes, views, Blade components
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php    # Dashboard with aggregate statistics
│   │   ├── AuthorityController.php    # CA hierarchy management (list, create, show)
│   │   ├── CertificateController.php  # Certificate list and detail views
│   │   ├── CsrController.php         # CSR list, detail, and approval views
│   │   ├── KeyController.php          # Key list and detail views
│   │   ├── CrlController.php         # CRL list view
│   │   └── AuditController.php       # Audit log browser
│   └── Middleware/
│       └── CaUiAuthentication.php     # Auth guard for the admin UI
├── Services/
│   └── DashboardStatsService.php      # Aggregates statistics for the dashboard
└── (no Facades, Models, or Contracts)

resources/views/
├── layouts/
│   └── app.blade.php                  # Base layout with navigation and CSS
├── components/
│   ├── stats-card.blade.php           # Reusable card displaying a stat with label
│   ├── status-badge.blade.php         # Colored badge for certificate/key statuses
│   ├── certificate-chain.blade.php    # Visual representation of a certificate chain
│   ├── dn-display.blade.php           # Formatted Distinguished Name display
│   └── pagination.blade.php           # Pagination component
└── pages/
    ├── dashboard.blade.php            # Main dashboard view
    ├── authorities/
    │   ├── index.blade.php            # CA list with hierarchy tree
    │   ├── show.blade.php             # CA detail view
    │   ├── create.blade.php           # CA creation form
    │   └── _ca-tree-node.blade.php    # Recursive partial for CA hierarchy rendering
    ├── certificates/
    │   ├── index.blade.php            # Certificate list with filtering
    │   └── show.blade.php             # Certificate detail view
    ├── csrs/
    │   ├── index.blade.php            # CSR list with status filtering
    │   └── show.blade.php             # CSR detail and approval view
    ├── keys/
    │   ├── index.blade.php            # Key list view
    │   └── show.blade.php             # Key detail view
    ├── crls/
    │   └── index.blade.php            # CRL list view
    └── audit/
        └── index.blade.php            # Audit log browser with filtering
```

## Service Provider

`UiServiceProvider` (declared as `final`) registers the following:

| Category | Details |
|---|---|
| **Config** | Merges `config/ca-ui.php`; publishes under tags `ca-ui-config`, `ca-ui-views`, `ca-ui-assets` |
| **Middleware** | Alias `ca-ui-auth` pointing to `CaUiAuthentication` |
| **Routes** | Web routes under configurable prefix (default `ca-admin`), with configurable middleware (default `web, auth`) |
| **Views** | Loaded from `resources/views` with namespace `ca` |
| **Blade components** | `<x-ca-stats-card>`, `<x-ca-status-badge>`, `<x-ca-certificate-chain>`, `<x-ca-dn-display>`, `<x-ca-pagination>` |
| **No migrations** | This package has no database tables of its own |

## Key Classes

**DashboardController** -- Renders the main dashboard page showing aggregate statistics (total CAs, active certificates, pending CSRs, expiring certificates, recent revocations) via `DashboardStatsService`.

**AuthorityController** -- Manages CA views: lists all CAs in a hierarchical tree, shows CA details with child CAs and issued certificates, and provides a form for creating root or intermediate CAs.

**CertificateController** -- Lists certificates with status filtering (active, revoked, expired) and shows certificate detail views with DN, validity dates, extensions, and chain visualization.

**DashboardStatsService** -- Queries across multiple package models (CertificateAuthority, Certificate, Csr, Key, Crl) to compute dashboard statistics. This is the only service class in the UI package.

**CaUiAuthentication** -- Middleware that protects the admin UI. Can be configured or replaced to integrate with your application's authentication and authorization system.

## Design Decisions

- **Presentation only, no business logic**: The UI package contains zero business logic. All operations (create CA, approve CSR, revoke certificate) are delegated to the appropriate package's service layer. The UI controllers are thin wrappers that call services and pass data to views.

- **Blade instead of SPA**: The UI uses server-rendered Blade templates rather than a JavaScript SPA framework. This reduces frontend complexity, avoids JS build tooling requirements, and makes the UI easy to customize by publishing and editing Blade files.

- **Reusable Blade components**: Five custom Blade components (`stats-card`, `status-badge`, `certificate-chain`, `dn-display`, `pagination`) provide consistent UI patterns across all views and can be used in host application views.

- **Recursive CA tree rendering**: The `_ca-tree-node.blade.php` partial uses `@include` recursion to render arbitrarily deep CA hierarchies, handling any level of intermediate CAs.

- **`final` service provider**: `UiServiceProvider` is declared `final` since it is a leaf package with no intended subclassing. This signals to developers that customization should happen through config, view publishing, and middleware replacement.

- **Separate middleware from core**: The UI has its own `CaUiAuthentication` middleware (aliased `ca-ui-auth`) separate from the core's `ca.auth`, since the web UI and API may have different authentication requirements.

## PHP 8.4 Features Used

- **`final` class**: `UiServiceProvider` is declared `final class`.
- **Strict types**: Every PHP file declares `strict_types=1`.

## Extension Points

- **View publishing**: Run `php artisan vendor:publish --tag=ca-ui-views` to copy views to `resources/views/vendor/ca/` for full customization.
- **Asset publishing**: Run `php artisan vendor:publish --tag=ca-ui-assets` to publish CSS/JS to `public/vendor/ca-ui/`.
- **Config `ca-ui.route_prefix`**: Change the admin panel URL prefix.
- **Config `ca-ui.middleware`**: Replace or extend middleware (e.g., add role-based access control).
- **CaUiAuthentication middleware**: Replace the `ca-ui-auth` alias to use your own authorization logic.
- **Blade components**: Override individual components by publishing views and editing the component templates.
- **Config `ca-ui.enabled`**: Disable the entire UI.
