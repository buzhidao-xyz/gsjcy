<?php
/**
 * 文章模块控制器
 * buzhidao
 * 2015-12-23
 */
namespace Admin\Controller;

class ArticleController extends CommonController
{
    public function __construct()
    {
        parent::__construct();

        //文章分类
        $this->_article_class = D('Article')->getArcclass();
        $this->assign('articleclass', $this->_article_class);
    }

    private function _getArcclassid()
    {
        $classid = mRequest('classid');
        $this->assign('classid', $classid);

        return $classid;
    }

    private function _getArcclassname()
    {
        $classname = mRequest('classname');
        $this->assign('classname', $classname);

        return $classname;
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

    //新闻分类
    public function arcclass()
    {
        $this->assign('datalist', $this->_article_class);
        $this->display();
    }

    //新闻分类-编辑
    public function arcclassedit()
    {
        $classid = $this->_getArcclassid();
        if (!$classid) $this->ajaxReturn(1, '未知新闻分类！');

        $arcclassinfo = $this->_article_class[$classid];
        $this->assign('arcclassinfo', $arcclassinfo);

        $html = $this->fetch('Article/arcclassedit');

        $this->ajaxReturn(0, null, array(
            'html' => $html
        ));
    }

    //新闻分类-保存
    public function arcclasssave()
    {
        $classid = $this->_getArcclassid();
        $classname = $this->_getArcclassname();

        $data = array();
        if ($classid) {
            $data = array(
                'classname' => $classname,
                'updatetime' => TIMESTAMP,
            );
            $result = D('Article')->saveArcclass($classid, $data);
        } else {
            $data = array(
                'classname' => $classname,
                'status' => 1,
                'createtime' => TIMESTAMP,
                'updatetime' => TIMESTAMP,
            );
            $result = D('Article')->saveArcclass(null, $data);
        }
        if ($result) {
            $this->ajaxReturn(0, '保存成功！');
        } else {
            $this->ajaxReturn(1, '保存失败！');
        }
    }

    //新闻分类-删除
    public function delarcclass()
    {
        $classid = $this->_getArcclassid();
        if (!$classid) $this->ajaxReturn(1, '未知新闻分类！');

        $result = D('Article')->delarcclass($classid);
        if ($result>0) {
            $this->ajaxReturn(0, '删除成功！');
        } else if ($result==-1) {
            $this->ajaxReturn(1, '该新闻分类里有新闻条目！不能删除！');
        } else {
            $this->ajaxReturn(1, '删除失败！');
        }
    }

    //显示-隐藏
    public function arcclassshow()
    {
        $classid = $this->_getArcclassid();
        if (!$classid) $this->ajaxReturn(1, '未知新闻分类！');

        $status = mRequest('status');

        $result = M('article_class')->where(array('classid'=>$classid))->save(array('status'=>$status));
        if ($result) {
            $this->ajaxReturn(0, '成功！');
        } else {
            $this->ajaxReturn(1, '失败！');
        }
    }

    //党建新闻初始化
    private function _newsInit()
    {
        $this->assign("sidebar_active", array("Article","news"));

        $this->_page_location = __APP__.'?s=Article/news';
    }

    //党建新闻
    public function news()
    {
        $this->_newsInit();

        $classid = $this->_getArcclassid();
        $keywords = $this->_getKeywords();

        list($start, $length) = $this->_mkPage();
        $data = D('Article')->getArc(null, $classid, $keywords, 1, $start, $length);
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
        $classid = $this->_getArcclassid();
        $keyword = $this->_getKeyword();
        $content = $this->_getContent();

        if ($arcid) {
            $msg = '编辑';
            $data = array(
                'title'      => $title,
                'classid'    => $classid,
                'content'    => $content,
                'keyword'    => $keyword,
                'updatetime' => TIMESTAMP
            );
            $arcid = D('Article')->saveArc($arcid, $data);
        } else {
            $msg = '发布';
            $data = array(
                'title'      => $title,
                'classid'    => $classid,
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