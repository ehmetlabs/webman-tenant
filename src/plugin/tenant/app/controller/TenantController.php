<?php

declare(strict_types=1);

namespace plugin\tenant\app\Controller;

use plugin\admin\app\controller\Crud;
use plugin\tenant\app\model\Tenant;
use support\exception\BusinessException;
use support\Request;
use support\Response;

/**
 * 租户管理.
 */
class TenantController extends Crud
{
    /**
     * @var Tenant
     */
    protected $model = null;

    /**
     * 构造函数.
     *
     * @return void
     */
    public function __construct()
    {
        $this->model = new Tenant();
    }

    /**
     * 浏览.
     */
    public function index(): Response
    {
        return raw_view('tenant/index');
    }

    /**
     * 插入.
     *
     * @throws BusinessException
     */
    public function insert(Request $request): Response
    {
        if ('POST' === $request->method()) {
            return parent::insert($request);
        }

        return raw_view('tenant/insert');
    }

    /**
     * 更新.
     *
     * @throws BusinessException
     */
    public function update(Request $request): Response
    {
        if ('POST' === $request->method()) {
            $tenantId = (int) $request->post($this->model->getKeyName());
            $this->guardSystemTenantMutation($tenantId, 'update', $request->post());

            return parent::update($request);
        }

        return raw_view('tenant/update');
    }

    public function delete(Request $request): Response
    {
        $primaryKey = $this->model->getKeyName();
        $ids = (array) $request->post($primaryKey, []);
        $this->guardSystemTenantMutation($ids, 'delete');

        return parent::delete($request);
    }

    private function guardSystemTenantMutation(int|array $tenantIds, string $action, array $data = []): void
    {
        $config = config('plugin.tenant.tenant', []);
        $globalTenantId = (int) ($config['global_tenant_id'] ?? 0);
        if (0 === $globalTenantId && [] === $tenantIds) {
            return;
        }

        $ids = \is_array($tenantIds) ? $tenantIds : [$tenantIds];
        $ids = \array_values(\array_filter($ids, static fn ($id) => null !== $id && '' !== $id));
        if ([] === $ids) {
            return;
        }

        $tenants = $this->model->whereIn($this->model->getKeyName(), $ids)->get();
        foreach ($tenants as $tenant) {
            $isSystemTenant = ((int) $tenant->id === $globalTenantId) || 1 === (int) $tenant->is_system;
            if (!$isSystemTenant) {
                continue;
            }
            if ('delete' === $action) {
                throw new BusinessException('系统租户不可删除');
            }
            if (isset($data['status']) && (int) $data['status'] !== (int) $tenant->status) {
                throw new BusinessException('系统租户不可修改状态');
            }
        }
    }
}
