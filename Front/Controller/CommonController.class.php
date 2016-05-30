<?php
/**
 * Front Module 通用基类
 * imbzd
 * 2015-12-07
 */
namespace Front\Controller;

class CommonController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        //检查登录
        $this->_CKUserLogon();
    }
}