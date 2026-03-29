# Security Policy

## Supported Versions

| Version | Supported          |
|---------|--------------------|
| 0.1.x   | :white_check_mark: |

## Reporting a Vulnerability

Please **do not** open a public GitHub issue for security vulnerabilities.

Report vulnerabilities by email to: **security@groupesti.com**

You will receive a response within 72 hours.

When reporting, please include:

- Affected version(s)
- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

## Disclosure Policy

We follow a 90-day coordinated disclosure policy. After a fix is released, we will publish a security advisory with full details.

## Security Considerations

This package handles cryptographic private keys. Please ensure:

- The `APP_KEY` in your Laravel application is strong and kept secret -- it is used by the default `laravel` encryption strategy to encrypt private keys at rest.
- Database access is properly secured, as encrypted private keys are stored in the `ca_keys` table.
- API routes (`api/ca/keys`) are protected with appropriate authentication and authorization middleware.
- The `--private` flag on `ca:key:export` should only be used in trusted environments.
