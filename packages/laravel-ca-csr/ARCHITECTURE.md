# Architecture — laravel-ca-csr (CSR Management)

## Overview

`laravel-ca-csr` manages the creation, validation, import, and approval workflow of Certificate Signing Requests (PKCS#10). It provides a fluent CSR builder, pluggable validation, and a status-based approval pipeline. It depends on `laravel-ca` for shared models and on `laravel-ca-key` for key pair access during CSR generation.

## Directory Structure

```
src/
├── CsrServiceProvider.php             # Registers validator, manager, builder, commands, routes
├── Console/
│   └── Commands/
│       ├── CsrCreateCommand.php       # Create a new CSR (ca-csr:create)
│       ├── CsrListCommand.php         # List CSRs with status filtering
│       ├── CsrImportCommand.php       # Import an external PEM/DER CSR
│       └── CsrApproveCommand.php      # Approve a pending CSR
├── Contracts/
│   ├── CsrManagerInterface.php        # Contract for the CSR management service
│   └── CsrValidatorInterface.php      # Contract for CSR validation logic
├── Events/
│   ├── CsrCreated.php                 # Fired when a CSR is created
│   ├── CsrApproved.php               # Fired when a CSR is approved
│   └── CsrRejected.php               # Fired when a CSR is rejected
├── Facades/
│   └── CaCsr.php                      # Facade resolving CsrManagerInterface
├── Http/
│   ├── Controllers/
│   │   └── CsrController.php         # REST API for CSR operations
│   ├── Requests/
│   │   ├── CreateCsrRequest.php       # Validation for CSR creation
│   │   └── ImportCsrRequest.php       # Validation for CSR import
│   └── Resources/
│       └── CsrResource.php           # JSON API resource for CSR serialization
├── Models/
│   ├── Csr.php                        # Eloquent model for the CSR entity
│   └── CsrStatus.php                 # Lookup subclass: pending, approved, rejected
└── Services/
    ├── CsrBuilder.php                 # Fluent builder for constructing CSRs step by step
    ├── CsrManager.php                 # Core service: create, import, approve, reject CSRs
    └── CsrValidator.php               # Validates CSR structure, signature, and DN fields
```

## Service Provider

`CsrServiceProvider` registers the following:

| Category | Details |
|---|---|
| **Config** | Merges `config/ca-csr.php`; publishes under tag `ca-csr-config` |
| **Singletons** | `CsrValidatorInterface` (resolved to `CsrValidator`), `CsrManagerInterface` (resolved to `CsrManager`) |
| **Bindings** | `CsrBuilder` (non-singleton, fresh instance per resolve) |
| **Alias** | `ca-csr` points to `CsrManagerInterface` |
| **Migrations** | `ca_csrs` table |
| **Commands** | `ca-csr:create`, `ca-csr:list`, `ca-csr:import`, `ca-csr:approve` |
| **Routes** | API routes under configurable prefix (default `api/ca/csrs`) |

## Key Classes

**CsrManager** -- The central service for CSR lifecycle management. It creates CSRs by combining a distinguished name with a key pair (via `KeyManagerInterface`), imports externally generated CSRs from PEM/DER data, manages the approval workflow (pending -> approved/rejected), and validates CSRs before processing.

**CsrBuilder** -- A fluent builder pattern allowing step-by-step construction of CSRs. Developers chain method calls to set the DN, key, extensions, and then call `build()` to generate the PKCS#10 request. It is bound as a non-singleton so each builder starts fresh.

**CsrValidator** -- Validates CSR integrity: checks that the embedded signature matches the public key, validates DN field requirements, and verifies structural conformance to PKCS#10. Implements `CsrValidatorInterface` for replaceability.

**Csr (Model)** -- Eloquent model storing the PEM-encoded CSR, parsed subject DN, associated key reference, status, and metadata. Supports tenant scoping through the core `BelongsToTenant` trait.

## Design Decisions

- **Approval workflow**: CSRs are not automatically used for certificate issuance. They go through a `pending -> approved/rejected` pipeline, allowing human or automated review before certificates are issued. This matches enterprise CA workflows.

- **Builder is non-singleton**: `CsrBuilder` is bound via `$this->app->bind()` (not singleton) so each resolution provides a clean, stateless builder instance. This prevents state leakage between requests.

- **Validation at import**: Imported CSRs are validated on ingestion (signature verification, structure check) rather than at approval time, failing fast on malformed requests.

## PHP 8.4 Features Used

- **`readonly` constructor promotion**: `CsrManager` and `CsrValidator` use promoted readonly properties for dependencies.
- **Named arguments**: Used in service construction (`keyManager:`, `validator:`) and event dispatch.
- **Strict types**: Every file declares `strict_types=1`.

## Extension Points

- **CsrValidatorInterface**: Replace the default validator with custom validation logic (e.g., enforcing specific DN patterns, checking against an LDAP directory).
- **CsrManagerInterface**: Bind a custom implementation for alternative CSR processing workflows.
- **Events**: Listen to `CsrCreated`, `CsrApproved`, `CsrRejected` to trigger approval notifications, webhook callbacks, or automatic certificate issuance.
- **Config**: Customize route prefix, middleware, and validation rules via `config/ca-csr.php`.
