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

        //文章分类
        $this->_article_class = D('Article')->getArcclass();
        $this->assign('articleclass', $this->_article_class);
    }

    //系统首页
    public function index()
    {
        //轮播图片
        $simglist = D('Advert')->getSimg();
        $this->assign('simglist', $simglist);

        //党建新闻
        $djarcclassinfo = current($this->_article_class);
        $this->assign('djarcclassinfo', $djarcclassinfo);
        $djarclist = D('Article')->getArc(null, $djarcclassinfo['id'], null, 0, 7);
        $this->assign('djarclist', $djarclist['data']);

        //平台公告
        $ntarcclassinfo = next($this->_article_class);
        $this->assign('ntarcclassinfo', $ntarcclassinfo);
        $ntarclist = D('Article')->getArc(null, $ntarcclassinfo['id'], null, 0, 7);
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