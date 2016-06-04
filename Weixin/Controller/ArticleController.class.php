<?php
/**
 * 文章逻辑层
 * buzhidao
 */
namespace Weixin\Controller;

class ArticleController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        //文章分类
        $this->_article_class = D('Article')->getArcclass();
        $this->assign('articleclass', $this->_article_class);
    }

    public function index()
    {
        $this->_setLocation();

        // $this->_CKWXUserLogon();

        $this->news();
    }

    //获取classid
    private function _getclassid()
    {
        $classid = mRequest('classid');
        $this->assign('classid', $classid);

        return $classid;
    }

    //获取arcid
    private function _getArcid()
    {
        $arcid = mRequest('arcid');

        return $arcid;
    }

    //新闻
    public function news()
    {
        // $this->_CKWXUserLogon();

        $this->assign("resumenavflag", "news");

        $arcid = $this->_getArcid();

        if ($arcid) {
            $this->_newsprofile($arcid);
        } else {
            $this->_newsindex();
        }
    }

    //新闻列表
    private function _newsindex()
    {
        $classid = $this->_getclassid();

        list($start, $length) = $this->_mkPage();
        $arclist = D('Article')->getArc(null, $classid, null, $start, $length);
        $total = $arclist['total'];
        $datalist = $arclist['data'];

        $this->assign('datalist', $datalist);

        $this->_mkPagination($total);
        $this->display('Article/news_index');
    }
    
    //新闻内容
    private function _newsprofile($arcid=null)
    {
        $arcprofile = D('Article')->getArcByID($arcid);

        //浏览量+1
        M('article')->where(array('arcid'=>$arcprofile['arcid']))->save(array('viewnum'=>$arcprofile['viewnum']+1));
        
        $this->assign('arcprofile', $arcprofile);
        $this->assign('classid', $arcprofile['classid']);
        $this->display('Article/news_profile');
    }
}