<?php
/**
 * 文章逻辑层
 * buzhidao
 */
namespace Weixin\Controller;

class ArticleController extends BaseController
{
    //新闻分类id
    public $arcclass = array(
        'news'   => array('id'=>1, 'name'=>'党建新闻'),
        'notice' => array('id'=>2, 'name'=>'平台公告'),
    );

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->_setLocation();

        // $this->_CKWXUserLogon();

        $this->news();
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

        //浏览量+1
        M('article')->where(array('arcid'=>$arcprofile['arcid']))->save(array('viewnum'=>$arcprofile['viewnum']+1));
        
        $this->assign('arcprofile', $arcprofile);
        $this->display('Article/news_profile');
    }

    //公告
    public function notice()
    {
        // $this->_CKWXUserLogon();
        
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
        
        //浏览量+1
        M('article')->where(array('arcid'=>$arcprofile['arcid']))->save(array('viewnum'=>$arcprofile['viewnum']+1));
        
        $this->assign('arcprofile', $arcprofile);
        $this->display('Article/notice_profile');
    }
}