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
        return view('tenant/index');
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

        return view('tenant/insert');
    }

    /**
     * 更新.
     *
     * @throws BusinessException
     */
    public function update(Request $request): Response
    {
        if ('POST' === $request->method()) {
            return parent::update($request);
        }

        return view('tenant/update');
    }
}
