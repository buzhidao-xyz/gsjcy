<?php
/**
 * 帮助模块控制器
 * buzhidao
 * 2015-12-27
 */
namespace Admin\Controller;

use Org\Util\Filter;
use Org\Util\String;

class HelpController extends CommonController
{
    public function __construct()
    {
        parent::__construct();
    }

    //QA功能初始化
    private function _qaInit()
    {
        $this->assign("sidebar_active", array("Help","qa"));

        $this->_page_location = __APP__.'?s=Help/qa';
    }

    //获取qaid
    private function _getQAid()
    {
        $qaid = mRequest('qaid');

        return $qaid;
    }

    //获取问题
    private function _getTitle()
    {
        $title = mRequest('title');
        if (!$title) $this->pageReturn(1, '请填写问题！', $this->_page_location);

        return $title;
    }

    //获取答案
    private function _getAnswer()
    {
        $answer = mRequest('answer');
        if (!$answer) $this->pageReturn(1, '请填写答案！', $this->_page_location);

        return $answer;
    }

    //获取搜索关键字
    private function _getKeywords()
    {
        $keywords = mRequest('keywords');
        $this->assign('keywords', $keywords);

        return $keywords;
    }

    //常见问题 qa
    public function qa()
    {
        $this->_qaInit();

        $keywords = $this->_getKeywords();

        list($start, $length) = $this->_mkPage();
        $data = D('Help')->getQA($keywords, $start, $length);
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

    //新建常见问题
    public function newqa()
    {
        $this->_qaInit();

        $this->display();
    }

    //编辑问题
    public function upqa()
    {
        $this->_qaInit();

        $qaid = $this->_getQAid();
        if (!$qaid) $this->pageReturn(0, '未知问题ID！', $this->_page_location);

        //获取问题
        $qainfo = D('Help')->getQAByID($qaid);
        $this->assign('qainfo', $qainfo);

        $this->display();
    }

    //保存问题
    public function qasave()
    {
        $this->_qaInit();

        $qaid = $this->_getQAid();

        $title = $this->_getTitle();
        $answer = $this->_getAnswer();

        if ($qaid) {
            $data = array(
                'title'      => $title,
                'answer'     => $answer,
                'updatetime' => TIMESTAMP,
            );
            $qaid = D('Help')->qasave($qaid, $data);
        } else {
            $data = array(
                'title'      => $title,
                'answer'     => $answer,
                'createtime' => TIMESTAMP,
                'updatetime' => TIMESTAMP,
            );
            $qaid = D('Help')->qasave(null, $data);
        }

        if ($qaid) {
            $this->pageReturn(0, '问题保存成功！', $this->_page_location);
        } else {
            $this->pageReturn(1, '问题保存失败！', $this->_page_location);
        }
    }

    //删除问题
    public function qadel()
    {
        $qaid = $this->_getQAid();
        if (!$qaid) $this->ajaxReturn(1, '未知问题ID！');

        $result = M('qa')->where(array('qaid'=>$qaid))->delete();
        if ($result) {
            $this->ajaxReturn(0, '问题删除成功！');
        } else {
            $this->ajaxReturn(1, '问题删除失败！');
        }
    }
}