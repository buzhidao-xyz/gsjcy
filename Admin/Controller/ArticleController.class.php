<?php
/**
 * 文章模块控制器
 * buzhidao
 * 2015-12-23
 */
namespace Admin\Controller;

class ArticleController extends CommonController
{
    // 文章分类
    private $_article_class = array(
        1 => array('id'=>1, 'name'=>'党建新闻'),
        2 => array('id'=>2, 'name'=>'平台公告'),
    );

    public function __construct()
    {
        parent::__construct();

        $this->assign('articleclass', $this->_article_class);
    }

    //获取文章id - arcid
    private function _getArcid()
    {
        $arcid = mRequest('arcid');

        return $arcid;
    }

    //获取文章标题
    private function _getTitle()
    {
        $title = mRequest('title');
        if (!$title) {
            $this->ajaxReturn(1, "清输入文章标题！");
        }

        return $title;
    }

    //获取文章关键字
    private function _getKeyword()
    {
        $keyword = mRequest('keyword');
        if (!$keyword) {
            $this->ajaxReturn(1, "清输入文章关键字！");
        }

        return $keyword;
    }

    //获取搜索关键字
    private function _getKeywords()
    {
        $keywords = mRequest('keywords');
        $this->assign('keywords', $keywords);

        return $keywords;
    }

    //获取文章内容
    private function _getContent()
    {
        $content = mRequest('content');
        if (!$content) {
            $this->ajaxReturn(1, "请输入文章内容！");
        }

        return $content;
    }

    //党建新闻初始化
    private function _newsInit()
    {
        $this->_classid = $this->_article_class[1]['id'];
        $this->_classname = $this->_article_class[1]['name'];
        $this->assign("classid", $this->_classid);
        $this->assign("classname", $this->_classname);

        $this->assign("sidebar_active", array("Article","news"));

        $this->_page_location = __APP__.'?s=Article/news';
    }

    //党建新闻
    public function news()
    {
        $this->_newsInit();

        $keywords = $this->_getKeywords();

        list($start, $length) = $this->_mkPage();
        $data = D('Article')->getArc(null, $this->_classid, $keywords, 1, $start, $length);
        $total = $data['total'];
        $datalist = $data['data'];

        $this->assign('datalist', $datalist);

        $param = array(
            'keywords'   => $keywords,
        );
        $this->assign('param', $param);
        //解析分页数据
        $this->_mkPagination($total, $param);

        $this->display();
    }

    //发布党建新闻
    public function newnews()
    {
        $this->_newsInit();

        $this->display();
    }

    //编辑党建新闻
    public function upnews()
    {
        $this->_newsInit();

        $arcid = $this->_getArcid();
        if (!$arcid) $this->pageReturn(1, '未知新闻公告ID！', $this->_page_location);

        $arcinfo = D('Article')->getArcByID($arcid);

        $this->assign('arcinfo', $arcinfo);
        $this->display();
    }

    //保存党建新闻
    public function newssave()
    {
        $this->_newsInit();
        
        $arcid = $this->_getArcid();

        $title = $this->_getTitle();
        $keyword = $this->_getKeyword();
        $content = $this->_getContent();

        if ($arcid) {
            $msg = '编辑';
            $data = array(
                'title'      => $title,
                'content'    => $content,
                'keyword'    => $keyword,
                'updatetime' => TIMESTAMP
            );
            $arcid = D('Article')->saveArc($arcid, $data);
        } else {
            $msg = '发布';
            $data = array(
                'title'      => $title,
                'content'    => $content,
                'classid'    => $this->_classid,
                'keyword'    => $keyword,
                'status'     => 1,
                'viewnum'    => 0,
                'createtime' => TIMESTAMP,
                'updatetime' => TIMESTAMP
            );
            $arcid = D('Article')->saveArc(null, $data);
        }
        
        if ($arcid) {
            $this->pageReturn(0, '新闻'.$msg.'成功！', $this->_page_location);
        } else {
            $this->pageReturn(1, '新闻'.$msg.'失败！', $this->_page_location);
        }
    }

    //平台公告初始化
    private function _noticeInit()
    {
        $this->_classid = $this->_article_class[2]['id'];
        $this->_classname = $this->_article_class[2]['name'];
        $this->assign("classid", $this->_classid);
        $this->assign("classname", $this->_classname);

        $this->assign("sidebar_active", array("Article","notice"));

        $this->_page_location = __APP__.'?s=Article/notice';
    }

    //平台公告
    public function notice()
    {
        $this->_noticeInit();

        $keywords = $this->_getKeywords();

        list($start, $length) = $this->_mkPage();
        $data = D('Article')->getArc(null, $this->_classid, $keywords, 1, $start, $length);
        $total = $data['total'];
        $datalist = $data['data'];

        $this->assign('datalist', $datalist);

        $param = array(
            'keywords'   => $keywords,
        );
        $this->assign('param', $param);
        //解析分页数据
        $this->_mkPagination($total, $param);

        $this->display();
    }

    //发布党建新闻
    public function newnotice()
    {
        $this->_noticeInit();

        $this->display();
    }

    //编辑党建新闻
    public function upnotice()
    {
        $this->_noticeInit();

        $arcid = $this->_getArcid();
        if (!$arcid) $this->pageReturn(1, '未知新闻公告ID！', $this->_page_location);

        $arcinfo = D('Article')->getArcByID($arcid);

        $this->assign('arcinfo', $arcinfo);
        $this->display();
    }

    //保存党建新闻
    public function noticesave()
    {
        $this->_noticeInit();
        
        $arcid = $this->_getArcid();

        $title = $this->_getTitle();
        $keyword = $this->_getKeyword();
        $content = $this->_getContent();

        if ($arcid) {
            $msg = '编辑';
            $data = array(
                'title'      => $title,
                'content'    => $content,
                'keyword'    => $keyword,
                'updatetime' => TIMESTAMP
            );
            $arcid = D('Article')->saveArc($arcid, $data);
        } else {
            $msg = '发布';
            $data = array(
                'title'      => $title,
                'content'    => $content,
                'classid'    => $this->_classid,
                'keyword'    => $keyword,
                'status'     => 1,
                'viewnum'    => 0,
                'createtime' => TIMESTAMP,
                'updatetime' => TIMESTAMP
            );
            $arcid = D('Article')->saveArc(null, $data);
        }
        
        if ($arcid) {
            $this->pageReturn(0, '公告'.$msg.'成功！', $this->_page_location);
        } else {
            $this->pageReturn(1, '公告'.$msg.'失败！', $this->_page_location);
        }
    }

    //删除文章 -> 回收站
    public function delarc()
    {
        $arcid = $this->_getArcid();
        if (!$arcid) $this->ajaxReturn(1, '未知新闻公告ID！');

        $data = array(
            'status' => 0,
            'updatetime' => TIMESTAMP,
        );
        $result = M('article')->where(array('arcid'=>$arcid))->save($data);
        if ($result) {
            $this->ajaxReturn(0, '新闻公告删除成功！');
        } else {
            $this->ajaxReturn(1, '新闻公告删除失败！');
        }
    }

    //回收站
    public function recycle()
    {
        $keywords = $this->_getKeywords();

        list($start, $length) = $this->_mkPage();
        $data = D('Article')->getArc(null, null, $keywords, 0, $start, $length);
        $total = $data['total'];
        $datalist = $data['data'];

        $this->assign('datalist', $datalist);

        $param = array(
            'keywords'   => $keywords,
        );
        $this->assign('param', $param);
        //解析分页数据
        $this->_mkPagination($total, $param);

        $this->display();
    }

    //还原
    public function recover()
    {
        $arcid = $this->_getArcid();
        if (!$arcid) $this->ajaxReturn(1, '未知新闻公告ID！');

        $data = array(
            'status' => 1,
            'updatetime' => TIMESTAMP,
        );
        $result = M('article')->where(array('arcid'=>$arcid))->save($data);
        if ($result) {
            $this->ajaxReturn(0, '成功！');
        } else {
            $this->ajaxReturn(1, '失败！');
        }
    }
}