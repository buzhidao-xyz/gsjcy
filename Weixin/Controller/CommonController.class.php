<?php
/**
 * Weixin Module 通用基类
 * imbzd
 * 2015-12-07
 */
namespace Weixin\Controller;

class CommonController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        //记录location
        $this->_setLocation();
        
        //检查登录
        $this->_CKUserLogon();
    }
}