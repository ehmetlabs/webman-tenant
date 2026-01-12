<?php

namespace plugin\tenant\app\Controller;

use support\Request;
use support\Response;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;
use plugin\tenant\app\model\Tenant;

/**
 * 租户管理 
 */
class TenantController extends Crud
{
    
    /**
     * @var Tenant
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new Tenant;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('tenant/index');
    }

    /**
     * 插入
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::insert($request);
        }
        return view('tenant/insert');
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException
    */
    public function update(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::update($request);
        }
        return view('tenant/update');
    }

}
