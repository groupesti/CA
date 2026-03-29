# Roadmap

## v0.1.0 -- Initial Release (done)

- [x] RSA key generation (2048, 4096)
- [x] ECDSA key generation (P-256, P-384, P-521)
- [x] Ed25519 key generation
- [x] Key import from PEM
- [x] Key export (PEM, DER, encrypted PEM)
- [x] Key rotation
- [x] Key destruction with data wipe
- [x] Encrypted private key storage at rest
- [x] SHA-256 fingerprint computation
- [x] Multi-tenant support
- [x] Artisan commands (generate, list, export, rotate)
- [x] REST API with configurable routes
- [x] Events (KeyGenerated, KeyRotated, KeyDeleted)

## v1.0.0 -- Stable Release

- [ ] HSM (Hardware Security Module) storage backend
- [ ] HashiCorp Vault integration for key storage
- [ ] Key expiration scheduling and automatic cleanup
- [ ] Automatic key rotation via scheduled command
- [ ] Key usage audit logging
- [ ] Key access policies and scoped permissions
- [ ] PKCS#12 export format support
- [ ] JWK (JSON Web Key) export format support
- [ ] Key backup and restore functionality
- [ ] Comprehensive test coverage (90%+)

## v1.1.0 -- Planned

- [ ] Key ceremony workflow (multi-party key generation)
- [ ] Key escrow support
- [ ] Cloud KMS integration (AWS KMS, Azure Key Vault, Google Cloud KMS)
- [ ] Key usage statistics and reporting dashboard

## Ideas / Backlog

- PKCS#11 interface support
- OpenPGP key format support
- Key migration tooling between storage backends
- Webhook notifications for key lifecycle events
