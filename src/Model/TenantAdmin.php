<?php

declare(strict_types=1);

namespace Ehmetlabs\WebmanTenant\Model;

use support\Model;

class TenantAdmin extends Model
{
    /**
     * @var string
     */
    protected $table = 'tenant_admins';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var bool
     */
    public $timestamps = false;

    public static function resolveTenantIdByAdminId(int $adminId): ?int
    {
        $tenantId = static::query()->where('admin_id', $adminId)->value('tenant_id');
        if (null === $tenantId) {
            return null;
        }

        return (int) $tenantId;
    }
}
