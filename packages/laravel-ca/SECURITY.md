# Security Policy

## Supported Versions

| Version | Supported          |
|---------|--------------------|
| 0.1.x   | :white_check_mark: |
| < 0.1   | :x:                |

## Reporting a Vulnerability

Please **do not** open a public GitHub issue for security vulnerabilities.

Report vulnerabilities by email to: **security@groupesti.com**

You will receive a response within 72 hours.

### What to include

- Affected package version
- Description of the vulnerability
- Steps to reproduce
- Potential impact assessment
- Suggested fix (if any)

## Disclosure Policy

We follow a 90-day coordinated disclosure policy. After a vulnerability is reported:

1. We will acknowledge receipt within 72 hours.
2. We will investigate and work on a fix.
3. A security advisory and patched release will be published once the fix is ready.
4. Credit will be given to the reporter (unless anonymity is requested).

## Security Considerations

This package deals with cryptographic key material and certificate management. Operators should be aware of the following:

- **Private key encryption** -- Private keys are encrypted at rest using the configured `EncryptionStrategyInterface` implementation. The default `laravel` strategy uses your application `APP_KEY`. If the `APP_KEY` is rotated, previously encrypted keys become unrecoverable.
- **Passphrase strategy** -- When using the `passphrase` encryption strategy, passphrases are never stored by the package. Losing the passphrase means losing access to the private key.
- **Audit logging** -- All CA operations are logged in the `ca_audit_logs` table. Do not disable or truncate this table in production.
- **API authentication** -- The built-in REST API is protected by the `ca.auth` middleware alias. Ensure you bind a proper authentication guard before exposing the API.
- **Tenant isolation** -- When multi-tenancy is enabled, all queries are automatically scoped. Disabling tenancy in a multi-tenant deployment may expose data across tenants.
