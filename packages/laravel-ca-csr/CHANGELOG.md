# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2026-03-29

### Added
- `Csr` Eloquent model with UUID support, tenant scoping, and audit trail.
- `CsrManager` service for creating, importing, validating, approving, and rejecting CSRs.
- `CsrBuilder` fluent builder for constructing CSRs with subject DN, SANs, and custom extensions.
- `CsrValidator` service for CSR signature and structure validation.
- Distinguished Name (DN) builder supporting CN, O, OU, C, ST, L, emailAddress, and serialNumber fields.
- Subject Alternative Name (SAN) support for DNS, IP, email, and URI types.
- Approval workflow with pending, approved, and rejected statuses.
- Auto-approve mode via `CA_CSR_AUTO_APPROVE` configuration option.
- `CaCsr` facade providing static access to the CSR manager.
- Artisan commands: `ca:csr:create`, `ca:csr:list`, `ca:csr:approve`, `ca:csr:import`.
- REST API routes for CSR CRUD operations, import, approval, and rejection.
- `CsrResource` API resource for consistent JSON responses.
- Events: `CsrCreated`, `CsrApproved`, `CsrRejected`.
- Eloquent scopes: `pending()`, `approved()`, `forCa()`, `forTemplate()`.
- Configurable CSR validity period, required DN fields, and allowed SAN types.
- Certificate template association support.
- Database migrations for the `ca_csrs` table.
- Publishable configuration file (`ca-csr.php`).
