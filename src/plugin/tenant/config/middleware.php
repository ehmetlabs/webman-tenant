<?php

declare(strict_types=1);

use plugin\tenant\app\middleware\TenantContextMiddleware;

return [
    '' => [
        TenantContextMiddleware::class,
    ],
];
