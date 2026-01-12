<?php

declare(strict_types=1);

use plugin\tenant\api\controller\TenantController;
use Webman\Route;

Route::get('/tenants', [TenantController::class, 'index']);
Route::post('/tenants', [TenantController::class, 'store']);
