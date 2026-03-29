# Architecture — laravel-ca-oid (OID Registry)

## Overview

`laravel-ca-oid` provides a comprehensive Object Identifier (OID) registry for the Laravel CA ecosystem. It maintains a database of standard, Microsoft, and Extended Validation OIDs used throughout X.509 certificate operations. The package supports OID lookup by dotted notation or name, registration of custom OIDs, and validation of OID syntax. It depends only on `laravel-ca` (core).

## Directory Structure

```
src/
├── OidServiceProvider.php             # Registers registry, resolver, validator, commands, routes
├── Console/
│   └── Commands/
│       ├── OidListCommand.php         # List registered OIDs with filtering (ca-oid:list)
│       ├── OidRegisterCommand.php     # Register a custom OID (ca-oid:register)
│       ├── OidResolveCommand.php      # Resolve an OID to its name/description (ca-oid:resolve)
│       ├── OidSearchCommand.php       # Search OIDs by name or partial dotted notation
│       └── OidSeedCommand.php         # Seed standard OID database (ca-oid:seed)
├── Contracts/
│   ├── OidRegistryInterface.php       # Contract for the OID registry service
│   └── OidResolverInterface.php       # Contract for OID resolution service
├── DTOs/
│   └── OidInfo.php                    # Immutable DTO carrying OID details (dotted, name, description, source)
├── Events/
│   ├── OidRegistered.php              # Fired when a new OID is registered
│   └── OidUnregistered.php            # Fired when an OID is removed
├── Facades/
│   └── CaOid.php                      # Facade resolving OidRegistryInterface
├── Http/
│   ├── Controllers/
│   │   └── OidController.php          # REST API for OID lookup and registration
│   ├── Requests/
│   │   └── RegisterOidRequest.php     # Validation for OID registration
│   └── Resources/
│       └── OidResource.php            # JSON API resource for OID serialization
├── Models/
│   └── OidEntry.php                   # Eloquent model for stored OID records
├── Registry/
│   ├── StandardOids.php               # Built-in registry of standard X.509/PKIX OIDs
│   ├── MicrosoftOids.php              # Built-in registry of Microsoft AD CS OIDs
│   └── ExtendedValidationOids.php     # Built-in registry of EV certificate OIDs
└── Services/
    ├── OidRegistry.php                # Core registry: in-memory + database OID store
    ├── OidResolver.php                # Resolves OIDs to names and vice versa
    └── OidValidator.php               # Validates OID dotted notation syntax
```

## Service Provider

`OidServiceProvider` registers the following:

| Category | Details |
|---|---|
| **Config** | Merges `config/ca-oid.php`; publishes under tag `ca-oid-config` |
| **Singletons** | `OidRegistry`, `OidValidator`, `OidResolver`, `OidRegistryInterface` (resolved to `OidRegistry`), `OidResolverInterface` (resolved to `OidResolver`) |
| **Migrations** | `ca_oids` table |
| **Commands** | `ca-oid:search`, `ca-oid:register`, `ca-oid:list`, `ca-oid:seed`, `ca-oid:resolve` |
| **Routes** | API routes under configurable prefix (default `api/ca`) |

## Key Classes

**OidRegistry** -- The central OID store combining built-in (in-memory) registries with database-persisted custom OIDs. It loads `StandardOids`, `MicrosoftOids`, and `ExtendedValidationOids` at construction, and supplements with `OidEntry` records from the database. Provides methods to register, unregister, and look up OIDs.

**OidResolver** -- Provides bidirectional resolution between OID dotted notation (e.g., `2.5.29.15`) and human-readable names (e.g., `keyUsage`). Delegates to `OidRegistry` for the actual lookup.

**OidValidator** -- Validates OID syntax: checks dotted notation format, ensures arc values are non-negative integers, and verifies the first two arcs comply with ITU-T X.660 rules (first arc 0-2, second arc <40 when first is 0 or 1).

**StandardOids / MicrosoftOids / ExtendedValidationOids** -- Static registry classes containing arrays of well-known OIDs. `StandardOids` covers RFC 5280 extensions (Key Usage, SAN, Basic Constraints, etc.), PKIX, and common algorithms. `MicrosoftOids` covers AD CS-specific OIDs. `ExtendedValidationOids` covers CA/Browser Forum EV certificate policy OIDs.

## Design Decisions

- **Hybrid in-memory + database**: Standard OIDs are hardcoded for performance (no database queries for common operations), while custom OIDs are stored in the database. This provides fast resolution for the 99% case while allowing extensibility.

- **Three separate registry classes**: Splitting standard, Microsoft, and EV OIDs into separate classes keeps each focused and makes it easy to add new OID families (e.g., post-quantum algorithm OIDs) as new registry classes.

- **Seeder command**: The `ca-oid:seed` command populates the database from the built-in registries, allowing users to customize descriptions or disable specific OIDs via database operations.

## PHP 8.4 Features Used

- **Strict types**: Every file declares `strict_types=1`.
- **Constructor property promotion**: Used in services and the `OidInfo` DTO.
- **Named arguments**: Used in service wiring.

## Extension Points

- **OidRegistryInterface**: Replace to integrate with an external OID repository or LDAP-based OID directory.
- **OidResolverInterface**: Bind a custom resolver for application-specific OID name mappings.
- **Custom registry classes**: Add new registry classes following the pattern of `StandardOids` and register them in the `OidRegistry` constructor.
- **Events**: Listen to `OidRegistered`, `OidUnregistered` for audit trail.
- **Database `ca_oids` table**: Directly manage custom OIDs via Eloquent for runtime extensibility.
