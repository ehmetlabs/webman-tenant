<?php

declare(strict_types=1);

use Ehmetlabs\Tenant\Middleware\TenantContextMiddleware;

return [
    '' => [
        TenantContextMiddleware::class,
    ],
];
