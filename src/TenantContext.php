<?php

declare(strict_types=1);

namespace Ehmetlabs\WebmanTenant;

use support\Context;

final class TenantContext
{
    private const string CONTEXT_KEY = 'tenant.context';

    public static function set(TenantInfo $tenant): void
    {
        Context::set(self::CONTEXT_KEY, $tenant);
    }

    public static function get(): ?TenantInfo
    {
        $tenant = Context::get(self::CONTEXT_KEY);

        return $tenant instanceof TenantInfo ? $tenant : null;
    }

    public static function clear(): void
    {
        Context::set(self::CONTEXT_KEY, null);
    }

    public static function getTenantId(): ?int
    {
        return self::get()?->tenantId;
    }

    public static function getTenantKey(): ?string
    {
        return self::get()?->tenantKey;
    }

    public static function getSource(): ?string
    {
        return self::get()?->source;
    }

    public static function isGlobalAdmin(): bool
    {
        return self::get()?->isGlobalAdmin ?? false;
    }
}
