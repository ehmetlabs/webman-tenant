<?php

declare(strict_types=1);

return [
    'global_tenant_id' => (int) \getenv('global_tenant_id' ?? 1),
    'global_admin_id' => (int) \getenv('global_admin_id' ?? 1),
    'base_domain' => \getenv('tenant_base_domain') ?: null,
    'path_prefix' => \getenv('tenant_path_prefix' ?? 't'),
    'header_key' => \getenv('tenant_header_key' ?? 'X-Tenant-Id'),
    'token_claim_key' => \getenv('tenant_token_claim_key' ?? 'tenant_id'),
    'token_admin_claim_key' => \getenv('tenant_token_admin_claim_key' ?? 'admin_id'),
    'resolve_order' => ['token', 'header', 'path', 'subdomain'],
    'allow_unverified_jwt' => \filter_var(\getenv('tenant_allow_unverified_jwt' ?? false), \FILTER_VALIDATE_BOOL),
    'require_tenant' => \filter_var(\getenv('tenant_require' ?? true), \FILTER_VALIDATE_BOOL),
    'exempt_paths' => [
        '/app/admin/account/login',
        '/app/admin/account/captcha',
        '/app/admin/account/logout',
    ],
];
