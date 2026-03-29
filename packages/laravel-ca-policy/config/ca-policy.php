<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Enforce Policies
    |--------------------------------------------------------------------------
    |
    | When enabled, all certificate issuance requests are evaluated against
    | the configured policies and rules before a certificate is issued.
    |
    */
    'enforce_policies' => true,

    /*
    |--------------------------------------------------------------------------
    | Default Policy OID
    |--------------------------------------------------------------------------
    |
    | The default certificate policy OID to embed in issued certificates
    | when no specific policy is configured.
    |
    */
    'default_policy_oid' => null,

    /*
    |--------------------------------------------------------------------------
    | Require CPS URI
    |--------------------------------------------------------------------------
    |
    | When enabled, every certificate policy must have a CPS URI defined.
    |
    */
    'require_cps_uri' => false,

    /*
    |--------------------------------------------------------------------------
    | Name Constraints
    |--------------------------------------------------------------------------
    |
    | Enable or disable name constraint validation for issued certificates.
    |
    */
    'name_constraints_enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Max Path Length Enforcement
    |--------------------------------------------------------------------------
    |
    | Enforce basicConstraints pathLength down the CA chain.
    |
    */
    'max_path_length_enforcement' => true,

    /*
    |--------------------------------------------------------------------------
    | Key Usage Enforcement
    |--------------------------------------------------------------------------
    |
    | Enforce that requested key usage values match the certificate type.
    |
    */
    'key_usage_enforcement' => true,

    /*
    |--------------------------------------------------------------------------
    | Validity Enforcement
    |--------------------------------------------------------------------------
    |
    | Enforce that certificate validity periods do not exceed CA maximums.
    |
    */
    'validity_enforcement' => true,

    /*
    |--------------------------------------------------------------------------
    | Subject Validation
    |--------------------------------------------------------------------------
    |
    | Validate subject distinguished names against policy rules.
    |
    */
    'subject_validation' => true,

];
