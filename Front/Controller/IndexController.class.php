<?php
/**
 * 系统入口逻辑控制
 * buzhidao
 * 2015-12-08
 */
namespace Front\Controller;

use Any\Controller;

class IndexController extends BaseController
{
    //导航栏目navflag标识
    public $navflag = 'Index';

    public function __construct()
    {
        parent::__construct();
    }

    //系统首页
    public function index()
    {
        //轮播图片
        $simglist = D('Advert')->getSimg();
        $this->assign('simglist', $simglist);

        //党建新闻
        $djarclist = D('Article')->getArc(null, CR('Article')->arcclass['news']['id'], null, 0, 7);
        $this->assign('djarclist', $djarclist['data']);

        //平台公告
        $ntarclist = D('Article')->getArc(null, CR('Article')->arcclass['notice']['id'], null, 0, 7);
        $this->assign('ntarclist', $ntarclist['data']);

        //获取课程总数
        $coursenum = D('Course')->getCoursenum();
        $this->assign('coursenum', $coursenum);

        //获取党员总数
        $usernum = D('User')->getUsernum();
        $this->assign('usernum', $usernum);

    	$this->display();
    }
}