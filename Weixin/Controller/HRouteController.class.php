<?php
/**
 * 路由模型逻辑控制
 * buzhidao
 * 2015-12-08
 */
namespace Weixin\Controller;

use Any\Controller;

class HRouteController extends BaseController
{
    public function __construct()
    {
        // parent::__construct();
    }

    public function index(){}

    //新闻公告
    public function article()
    {
    	header('location:http://139.196.199.135/Weixin/index.php?s=Article/index');
    	exit;
    }

    //在线课程
    public function course()
    {
    	header('location:http://139.196.199.135/Weixin/index.php?s=Course/index');
    	exit;
    }

    //个人中心
    public function user()
    {
    	header('location:http://139.196.199.135/Weixin/index.php?s=User/home');
    	exit;
    }
}