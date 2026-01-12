<?php

declare(strict_types=1);

namespace plugin\tenant\app\model;

use Illuminate\Database\Eloquent\Builder;
use plugin\tenant\app\common\TenantContext;
use support\Model;

abstract class TenantModel extends Model
{
    protected string $tenantColumn = 'tenant_id';

    protected bool $tenantScoped = true;

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', static function (Builder $builder): void {
            $model = $builder->getModel();
            if (!$model instanceof self || !$model->isTenantScoped()) {
                return;
            }
            $tenantId = TenantContext::getTenantId();
            if (null === $tenantId || TenantContext::isGlobalAdmin()) {
                return;
            }
            $builder->where($model->getQualifiedTenantColumn(), $tenantId);
        });

        static::creating(static function (self $model): void {
            if (!$model->isTenantScoped()) {
                return;
            }
            $tenantColumn = $model->getTenantColumn();
            if (TenantContext::isGlobalAdmin()) {
                if (null === $model->getAttribute($tenantColumn)) {
                    $tenantId = TenantContext::getTenantId();
                    if (null !== $tenantId) {
                        $model->setAttribute($tenantColumn, $tenantId);
                    }
                }

                return;
            }
            $tenantId = TenantContext::getTenantId();
            if (null === $tenantId) {
                throw new \RuntimeException('租户上下文缺失');
            }
            $model->setAttribute($tenantColumn, $tenantId);
        });
    }

    public function isTenantScoped(): bool
    {
        return $this->tenantScoped;
    }

    public function getTenantColumn(): string
    {
        return $this->tenantColumn;
    }

    public function getQualifiedTenantColumn(): string
    {
        return $this->getTable() . '.' . $this->getTenantColumn();
    }
}
