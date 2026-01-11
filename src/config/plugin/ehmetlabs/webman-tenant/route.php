<?php

declare(strict_types=1);

use Ehmetlabs\Tenant\Controller\TenantController;
use Webman\Route;

Route::get('/tenants', [TenantController::class, 'index']);
Route::post('/tenants', [TenantController::class, 'store']);
