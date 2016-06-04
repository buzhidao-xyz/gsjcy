<?php
/**
 * 文章模型逻辑控制
 * buzhidao
 * 2015-12-14
 */
namespace Front\Controller;

use Any\Controller;

class ArticleController extends BaseController
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

    //文章模型入口
    public function index(){}

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
        $this->assign('arcprofile', $arcprofile);
        
        $classid = $arcprofile['classid'];
        $this->assign('classid', $classid);

        //浏览量+1
        M('article')->where(array('arcid'=>$arcprofile['arcid']))->save(array('viewnum'=>$arcprofile['viewnum']+1));

        //上一文章、下一文章
        $prevnextarc = D('Article')->getPrevNextArticle($arcid, $classid);
        $this->assign('prevnextarc', $prevnextarc);
        
        $this->display('Article/news_profile');
    }
}