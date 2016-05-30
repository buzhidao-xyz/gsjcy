<?php
/**
 * 广告模块控制器
 * buzhidao
 * 2015-12-23
 */
namespace Admin\Controller;

use Any\Upload;

class AdvertController extends CommonController
{
    public function __construct()
    {
        parent::__construct();
    }

    //获取simgid
    private function _getSimgid()
    {
        $simgid = mRequest('simgid');

        return $simgid;
    }

    //获取标题
    private function _getTitle()
    {
        $title = mRequest('title');
        if (!$title) $this->ajaxReturn(1, '请填写标题！');

        return $title;
    }

    //获取序号
    private function _getSortno()
    {
        $sortno = mRequest('sortno');
        if (!$sortno) $this->ajaxReturn(1, '请填写序号！');

        return $sortno;
    }

    //获取链接
    private function _getLink()
    {
        $link = mRequest('link');
        if (!$link) $this->ajaxReturn(1, '请填写链接！');

        return $link;
    }

    //获取图片
    private function _getSimgpath()
    {
        $simgpath = mRequest('simgpath');
        if (!$simgpath) $this->ajaxReturn(1, '请上传图片！');

        return $simgpath;
    }

    //图片上传
    public function simgupload()
    {
        //初始化上传类
        $Upload = new Upload();
        $Upload->maxSize  = $this->_upfilesize['image']['size'];
        $Upload->exts     = $this->_upfilesize['image']['exts'];
        $Upload->rootPath = UPLOAD_PATH;
        $Upload->savePath = 'scrollimage/';
        $Upload->saveName = array('uniqid', '');
        $Upload->autoSub  = true;
        $Upload->subName  = array('date', 'Ym');

        //上传
        $error = null;
        $msg = '上传成功！';
        $data = array();
        $info = $Upload->upload();
        if (!$info) {
            $error = 1;
            $msg = $Upload->getError();
        } else {
            $fileinfo = array_shift($info);
            $data = array(
                'filepath' => '/'.UPLOAD_PT.$fileinfo['savepath'],
                'filename' => $fileinfo['savename'],
            );
        }

        $this->ajaxReturn($error, $msg, $data);
    }

    //轮播图片 - 首页
    public function scrollimage()
    {
        $simginfo = D('Advert')->getScrollimage();
        $total = $simginfo['total'];
        $datalist = $simginfo['data'];

        $this->assign('datalist', $datalist);
        $this->display();
    }

    //轮播图片 - 首页 保存
    public function scrollimagesave()
    {
        $title    = $this->_getTitle();
        $sortno   = $this->_getSortno();
        $link     = $this->_getLink();
        $simgpath = $this->_getSimgpath();

        $data = array(
            'simgpath' => $simgpath,
            'title' => $title,
            'link' => $link,
            'isshow' => 1,
            'sortno' => $sortno,
            'createtime' => TIMESTAMP,
            'updatetime' => TIMESTAMP,
        );
        $scrollimageid = D('Advert')->saveScrollimage($data);

        if ($scrollimageid) {
            $this->ajaxReturn(0, '保存成功！');
        } else {
            $this->ajaxReturn(1, '保存失败！');
        }
    }

    //轮播图片 - 首页 排序
    public function scrollimagesort()
    {
        $sortnosid = mRequest('sortnosid', false);
        $sortnos = mRequest('sortnos', false);

        $data = array();
        foreach ($sortnosid as $k=>$simgid) {
            $data = array(
                'sortno' => $sortnos[$k],
                'updatetime' => TIMESTAMP,
            );
            $result = M('scrollimage')->where(array('simgid'=>$simgid))->save($data);
        }

        $this->ajaxReturn(0, '保存成功！');
    }

    //轮播图片 - 首页 显示/隐藏
    public function scrollimageenable()
    {
        $simgid = $this->_getSimgid();
        if (!$simgid) $this->ajaxReturn(1, '未知轮播图片信息！');

        $isshow = mRequest('isshow');
        if (!in_array($isshow, array(0,1))) $this->ajaxReturn(1, '数据错误！');

        $data = array(
            'isshow' => $isshow,
            'updatetime' => TIMESTAMP,
        );
        $result = M('scrollimage')->where(array('simgid'=>$simgid))->save($data);
        if ($result) {
            $this->ajaxReturn(0, '成功！');
        } else {
            $this->ajaxReturn(1, '失败！');
        }
    }

    //轮播图片 - 首页 删除
    public function scrollimagedelete()
    {
        $simgid = $this->_getSimgid();
        if (!$simgid) $this->ajaxReturn(1, '未知轮播图片信息！');

        $result = M('scrollimage')->where(array('simgid'=>$simgid))->delete();
        if ($result) {
            $this->ajaxReturn(0, '删除成功！');
        } else {
            $this->ajaxReturn(1, '删除失败！');
        }
    }
}