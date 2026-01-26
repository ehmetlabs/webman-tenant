<?php

declare(strict_types=1);

namespace plugin\tenant\app\common;

final readonly class TenantInfo
{
    public function __construct(
        public int $tenantId,
        public string $tenantKey,
        public string $source,
        public bool $isGlobalAdmin,
    ) {
    }
}
