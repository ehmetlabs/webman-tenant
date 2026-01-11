<?php

declare(strict_types=1);

namespace Ehmetlabs\WebmanTenant;

final class TenantResolution
{
    public function __construct(
        public readonly ?TenantInfo $tenant,
        public readonly ?string $errorMessage = null,
        public readonly int $errorStatus = 400,
    ) {
    }

    public function isResolved(): bool
    {
        return null !== $this->tenant;
    }
}
