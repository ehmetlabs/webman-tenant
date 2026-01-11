<?php

declare(strict_types=1);

namespace Ehmetlabs\WebmanTenant\Controller;

use Ehmetlabs\WebmanTenant\Model\Tenant;
use Ehmetlabs\WebmanTenant\TenantContext;
use support\Request;
use support\Response;

class TenantController
{
    public function index(Request $request): Response
    {
        if (!TenantContext::isGlobalAdmin()) {
            return json(['code' => 403, 'msg' => 'Forbidden', 'data' => []], 403);
        }

        $tenants = Tenant::query()
            ->orderBy('id')
            ->get(['id', 'name', 'slug', 'status', 'is_system', 'created_at', 'updated_at']);

        return json(['code' => 0, 'msg' => 'ok', 'data' => $tenants]);
    }

    public function store(Request $request): Response
    {
        if (!TenantContext::isGlobalAdmin()) {
            return json(['code' => 403, 'msg' => 'Forbidden', 'data' => []], 403);
        }

        $name = \trim((string) $request->post('name', ''));
        $slug = \trim((string) $request->post('slug', ''));
        $status = (int) $request->post('status', 1);

        if ('' === $name || '' === $slug) {
            return json(['code' => 400, 'msg' => 'name and slug required', 'data' => []], 400);
        }

        if (Tenant::where('slug', $slug)->exists()) {
            return json(['code' => 409, 'msg' => 'Tenant slug already exists', 'data' => []], 409);
        }

        $tenant = new Tenant();
        $tenant->name = $name;
        $tenant->slug = $slug;
        $tenant->status = $status > 0 ? 1 : 0;
        $tenant->is_system = false;
        $tenant->save();

        return json([
            'code' => 0,
            'msg' => 'ok',
            'data' => ['id' => $tenant->id],
        ]);
    }
}
