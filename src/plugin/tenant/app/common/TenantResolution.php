<?php

declare(strict_types=1);

namespace plugin\tenant\app\common;

final readonly class TenantResolution
{
    public function __construct(
        public ?TenantInfo $tenant,
        public ?string $errorMessage = null,
        public int $errorStatus = 400,
    ) {
    }

    public function isResolved(): bool
    {
        return $this->tenant instanceof TenantInfo;
    }
}
