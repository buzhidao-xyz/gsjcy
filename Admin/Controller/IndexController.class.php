<?php
/**
 * Admin Module Main Enter
 * imbzd
 * 2015-05-11
 */
namespace Admin\Controller;

class IndexController extends CommonController
{
    public function __construct()
    {
        parent::__construct();
    }

    //系统主框架页面
    public function index()
    {
        $this->assign("sidebar_active", array("Index","dashboard"));

        $this->dashboard();
    }

    //系统主界面-控制面板
    public function dashboard()
    {
        $this->display('Index/dashboard');
    }
}