# Architecture — laravel-ca-pki (Meta-Package)

## Overview

`laravel-ca-pki` is a Composer meta-package that installs the complete Laravel CA PKI ecosystem with a single `composer require` command. It contains no source code, no configuration, and no service providers -- only dependency declarations. Installing this package pulls in all 15 CA packages, providing a fully functional PKI system including certificate authority management, key management, certificate lifecycle, revocation (CRL + OCSP), enrollment protocols (ACME, SCEP, EST), timestamping (TSA), cryptographic messaging (CMS), OID registry, policy engine, and web administration UI.

## Dependency Graph

```
laravel-ca-pki (meta-package)
│
├── laravel-ca  ← Core (shared models, DTOs, events, storage, encryption)
│
├── laravel-ca-oid  ← OID Registry
│   └── laravel-ca
│
├── laravel-ca-key  ← Key Management
│   └── laravel-ca
│
├── laravel-ca-csr  ← CSR Management
│   ├── laravel-ca
│   └── laravel-ca-key
│
├── laravel-ca-crt  ← Certificate Management
│   ├── laravel-ca
│   ├── laravel-ca-key
│   └── laravel-ca-csr
│
├── laravel-ca-crl  ← Certificate Revocation Lists
│   ├── laravel-ca
│   └── laravel-ca-crt
│
├── laravel-ca-ocsp  ← OCSP Responder
│   ├── laravel-ca
│   └── laravel-ca-crt
│
├── laravel-ca-pkcs12  ← PKCS#12 Bundles
│   ├── laravel-ca
│   ├── laravel-ca-crt
│   └── laravel-ca-key
│
├── laravel-ca-acme  ← ACME Server (Let's Encrypt compatible)
│   ├── laravel-ca
│   ├── laravel-ca-crt
│   ├── laravel-ca-csr
│   └── laravel-ca-key
│
├── laravel-ca-scep  ← SCEP Server (device enrollment)
│   ├── laravel-ca
│   ├── laravel-ca-crt
│   ├── laravel-ca-csr
│   └── laravel-ca-key
│
├── laravel-ca-est  ← EST Server (enrollment over HTTPS)
│   ├── laravel-ca
│   ├── laravel-ca-crt
│   ├── laravel-ca-csr
│   └── laravel-ca-key
│
├── laravel-ca-tsa  ← Time Stamping Authority
│   ├── laravel-ca
│   ├── laravel-ca-crt
│   └── laravel-ca-key
│
├── laravel-ca-cms  ← Cryptographic Message Syntax
│   ├── laravel-ca
│   ├── laravel-ca-crt
│   └── laravel-ca-key
│
├── laravel-ca-policy  ← Policy Engine
│   ├── laravel-ca
│   └── laravel-ca-crt
│
└── laravel-ca-ui  ← Web Administration UI
    ├── laravel-ca
    ├── laravel-ca-key
    ├── laravel-ca-csr
    ├── laravel-ca-crt
    ├── laravel-ca-crl
    └── laravel-ca-ocsp
```

## Package Dependency Layers

```
Layer 0 (Foundation):    laravel-ca
Layer 1 (Primitives):   laravel-ca-key, laravel-ca-oid
Layer 2 (Core PKI):     laravel-ca-csr, laravel-ca-crt
Layer 3 (Revocation):   laravel-ca-crl, laravel-ca-ocsp
Layer 3 (Packaging):    laravel-ca-pkcs12
Layer 4 (Protocols):    laravel-ca-acme, laravel-ca-scep, laravel-ca-est
Layer 4 (Services):     laravel-ca-tsa, laravel-ca-cms, laravel-ca-policy
Layer 5 (Presentation): laravel-ca-ui
```

## Design Decisions

- **Meta-package (no code)**: `laravel-ca-pki` has `"type": "metapackage"` in its `composer.json`. Composer does not install any files for this package -- it only resolves and installs its dependencies. This means there is no `src/` directory, no service provider, and no configuration.

- **All packages at `^1.0`**: All dependencies are pinned to `^1.0`, allowing any 1.x release while preventing accidental major version bumps.

- **Individual package installation**: Users who need only a subset of PKI functionality can install individual packages (e.g., `composer require groupesti/laravel-ca groupesti/laravel-ca-key groupesti/laravel-ca-crt`) without the meta-package.

- **Layered dependency architecture**: Packages are organized in dependency layers. Lower layers never depend on higher layers. This ensures that installing `laravel-ca-key` alone does not pull in ACME or SCEP, keeping deployments lean.

## Extension Points

- **Package selection**: Install the meta-package for the complete suite, or pick individual packages for a minimal footprint.
- **Each sub-package**: Has its own config file, service provider, events, and interfaces for customization. See individual package ARCHITECTURE.md files for details.
