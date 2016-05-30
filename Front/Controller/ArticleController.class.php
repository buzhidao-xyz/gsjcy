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

    //新闻分类id
    public $arcclass = array(
        'news'   => array('id'=>1, 'name'=>'党建新闻'),
        'notice' => array('id'=>2, 'name'=>'平台公告'),
    );

    public function __construct()
    {
        parent::__construct();
    }

    //文章模型入口
    public function index(){}

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
        list($start, $length) = $this->_mkPage();
        $arclist = D('Article')->getArc(null, $this->arcclass['news']['id'], null, $start, $length);
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
        
        //浏览量+1
        M('article')->where(array('arcid'=>$arcprofile['arcid']))->save(array('viewnum'=>$arcprofile['viewnum']+1));

        //上一文章、下一文章
        $prevnextarc = D('Article')->getPrevNextArticle($arcid, $this->arcclass['news']['id']);
        $this->assign('prevnextarc', $prevnextarc);
        
        $this->display('Article/news_profile');
    }

    //公告
    public function notice()
    {
        $this->assign("resumenavflag", "notice");
        
        $arcid = $this->_getArcid();

        if ($arcid) {
            $this->_noticeprofile($arcid);
        } else {
            $this->_noticeindex();
        }
    }

    //公告列表
    private function _noticeindex()
    {
        list($start, $length) = $this->_mkPage();
        $arclist = D('Article')->getArc(null, $this->arcclass['notice']['id'], null, $start, $length);
        $total = $arclist['total'];
        $datalist = $arclist['data'];

        $this->assign('datalist', $datalist);

        $this->_mkPagination($total);
        $this->display('Article/notice_index');
    }
    
    //公告内容
    private function _noticeprofile($arcid=null)
    {
        $arcprofile = D('Article')->getArcByID($arcid);
        $this->assign('arcprofile', $arcprofile);
        
        //浏览量+1
        M('article')->where(array('arcid'=>$arcprofile['arcid']))->save(array('viewnum'=>$arcprofile['viewnum']+1));
        
        //上一文章、下一文章
        $prevnextarc = D('Article')->getPrevNextArticle($arcid, $this->arcclass['notice']['id']);
        $this->assign('prevnextarc', $prevnextarc);
        
        $this->display('Article/notice_profile');
    }
}