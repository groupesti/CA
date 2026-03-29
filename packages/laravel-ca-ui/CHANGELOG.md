# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2026-03-29

### Added

- Initial release of the Laravel CA UI package.
- Dashboard page with PKI statistics via `DashboardStatsService`.
- Certificate Authorities management (list, create, store, show) with visual CA tree hierarchy.
- Certificates management (list, show, revoke, export).
- Keys management (list, show).
- CSR management (list, show, approve, reject).
- CRL management (list, generate).
- Audit log viewer.
- Blade components: `stats-card`, `status-badge`, `certificate-chain`, `dn-display`, `pagination`.
- `CaUiAuthentication` middleware with `ca-ui-auth` alias.
- Configurable route prefix, middleware stack, pagination, and theme colors.
- Publishable config (`ca-ui-config`), views (`ca-ui-views`), and assets (`ca-ui-assets`).
- Tailwind CSS-based responsive admin layout.
