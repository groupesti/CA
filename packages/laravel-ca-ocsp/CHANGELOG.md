# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2026-03-29

### Added

- RFC 6960 OCSP responder with full ASN.1 DER encoding/decoding via phpseclib v3.
- `OcspResponderInterface` and `CertificateStatusResolverInterface` contracts for extensibility.
- `CaOcsp` facade with `handleRequest()`, `parseRequest()`, and `buildResponse()` methods.
- ASN.1 maps for OCSPRequest, OCSPResponse, BasicOCSPResponse, CertID, CertStatus, ResponseData, SingleResponse, and TBSRequest.
- `OcspRequestParser` for decoding DER-encoded OCSP requests.
- `OcspResponseBuilder` for constructing signed OCSP responses.
- `CertificateStatusResolver` service for determining certificate revocation status.
- `OcspController` with automatic route registration under a configurable prefix.
- `OcspContentType` middleware for enforcing proper OCSP content types.
- `OcspResponderCertificate` and `OcspResponse` Eloquent models.
- `CertStatusResult` DTO for structured status information.
- `OcspResponseGenerated` event dispatched after each response.
- `ca-ocsp:setup` Artisan command to initialize the OCSP responder.
- `ca-ocsp:status` Artisan command to check responder health.
- Configurable response caching, nonce requirements, certificate validity, and response validity.
- Publishable configuration (`ca-ocsp-config`) and migrations (`ca-ocsp-migrations`).
