<?php

declare(strict_types=1);

use plugin\tenant\api\controller\TenantController;
use Webman\Route;

Route::get('/api/tenants', [TenantController::class, 'index']);
Route::post('/api/tenants', [TenantController::class, 'store']);
