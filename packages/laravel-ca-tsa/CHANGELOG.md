# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2026-03-29

### Added

- RFC 3161 Time-Stamp Authority server with full ASN.1 DER encoding/decoding via phpseclib v3.
- `TsaServerInterface` and `TsaVerifierInterface` contracts for timestamping and verification.
- `CaTsa` facade with `handleRequest()`, `parseRequest()`, and `createTimestamp()` methods.
- ASN.1 maps for TimeStampReq, TimeStampResp, TSTInfo, MessageImprint, PKIStatusInfo, and Accuracy.
- `TsaRequestParser` for decoding DER-encoded TSQ requests.
- `TsaResponseBuilder` for constructing signed TSR responses.
- `TsaSerialGenerator` for cryptographically random serial number generation.
- `TsaServer` service implementing the full RFC 3161 timestamp issuance workflow.
- `TsaVerifier` service for verifying timestamp responses and extracting TSTInfo.
- `TsaController` with automatic route registration under a configurable prefix.
- `TsaContentType` middleware for enforcing proper TSA content types.
- `TimestampToken` and `TsaCertificate` Eloquent models.
- `TimestampCreated` and `TimestampVerified` events.
- `ca-tsa:setup` Artisan command to initialize the TSA.
- `ca-tsa:status` Artisan command to check TSA health and configuration.
- `ca-tsa:verify` Artisan command to verify timestamp response files.
- `ca-tsa:token-list` Artisan command to list issued tokens.
- Configurable policy OID, hash algorithm, accuracy, ordering, nonce requirements, and serial number bit length.
- Publishable configuration (`ca-tsa-config`) and migrations (`ca-tsa-migrations`).
