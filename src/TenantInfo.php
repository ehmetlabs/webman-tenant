<?php

declare(strict_types=1);

namespace Ehmetlabs\Tenant;

final class TenantInfo
{
    public function __construct(
        public readonly int $tenantId,
        public readonly string $tenantKey,
        public readonly string $source,
        public readonly bool $isGlobalAdmin
    ) {
    }
}
