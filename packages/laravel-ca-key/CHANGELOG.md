# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2026-03-29

### Added

- `Key` Eloquent model with soft deletes, UUID lookup, and SHA-256 fingerprint storage.
- `KeyManager` service for generating, importing, exporting, rotating, and destroying cryptographic keys.
- `KeyGenerator` factory with pluggable algorithm-specific generators.
- `RsaKeyGenerator` supporting RSA 2048-bit and 4096-bit key generation via phpseclib v3.
- `EcdsaKeyGenerator` supporting ECDSA P-256, P-384, and P-521 curves.
- `Ed25519KeyGenerator` for Ed25519 key generation.
- `KeyExporter` service supporting PEM, DER, and encrypted PEM (PKCS#8 with passphrase) export formats.
- Encrypted private key storage at rest using a configurable encryption strategy.
- SHA-256 fingerprint computation for public key identification.
- `KeyManagerInterface`, `KeyGeneratorInterface`, and `KeyStorageInterface` contracts.
- `CaKey` facade for convenient static access to key management operations.
- `KeyServiceProvider` with singleton bindings for `KeyGenerator`, `KeyExporter`, and `KeyManagerInterface`.
- Artisan command `ca:key:generate` for interactive or scripted key pair generation.
- Artisan command `ca:key:list` with filters for CA, tenant, algorithm, and status.
- Artisan command `ca:key:export` supporting public/private key export with optional passphrase and output file.
- Artisan command `ca:key:rotate` for key rotation with confirmation prompt.
- REST API routes for key listing, generation, show, destroy, export, and rotation.
- `KeyController` with `GenerateKeyRequest` form request validation and `KeyResource` API resource.
- `KeyGenerated`, `KeyRotated`, and `KeyDeleted` events dispatched on key lifecycle operations.
- Eloquent query scopes: `active()`, `byTenant()`, `byAlgorithm()`, `byFingerprint()`.
- Database migration for `ca_keys` table with UUID, fingerprint index, and soft deletes.
- Configurable options for default algorithm, RSA bits, EC curve, encryption strategy, storage, key rotation, and API routes.
- Multi-tenant support via optional `tenant_id` field.
