<?php
/**
 * 课程模块控制器
 * buzhidao
 * 2015-12-27
 */
namespace Admin\Controller;

use Any\Upload;

class CourseController extends CommonController
{
    // 课程分类
    public $_course_class = array(
        1 => array('id'=>1, 'name'=>'学党章党规(上)', 'weight'=>0.25),
        2 => array('id'=>2, 'name'=>'学党章党规(下)', 'weight'=>0.25),
        3 => array('id'=>3, 'name'=>'学系列讲话', 'weight'=>0.25),
    );

    //课程类型
    public $_course_type = array(
        1 => array('id'=>1, 'name'=>'视频课程'),
        2 => array('id'=>2, 'name'=>'图文课程'),
    );

    public function __construct()
    {
        parent::__construct();

        $this->assign("sidebar_active", array("Course","index"));

        $this->_page_location = __APP__.'?s=Course/index';

        $this->assign("classlist", $this->_course_class);
    }

    //获取课程id
    private function _getCourseid()
    {
        $courseid = mRequest('courseid');

        return $courseid;
    }

    //获取课程标题
    private function _getTitle()
    {
        $title = mRequest('title');
        if (!$title) $this->ajaxReturn(1, '请填写课程标题！');

        return $title;
    }

    //获取课程分类
    private function _getClassid()
    {
        $classid = mRequest('classid');

        return $classid;
    }

    //获取课程示例图
    private function _getShowimg($type=null)
    {
        $showimg = mRequest('showimg');

        return $showimg;
    }

    //获取课程类型
    private function _getType()
    {
        $type = mRequest('type');
        if (!isset($this->_course_type[$type])) $this->ajaxReturn(1, '请选择课程类型！');

        return $type;
    }

    //获取视频封面图
    private function _getVideoimg($type=null)
    {
        $videoimg = mRequest('videoimg');
        if ($type==1 && !$videoimg) $this->ajaxReturn(1, '请上传视频封面图！');

        return $videoimg;
    }

    //获取视频文件地址
    private function _getVideopath($type=null)
    {
        $videopath = mRequest('videopath');
        if ($type==1 && !$videopath) $this->ajaxReturn(1, '请填写视频文件地址！');

        return $videopath;
    }

    //获取视频文件时长
    private function _getVideotime($type=null)
    {
        $videotime_h = mRequest('videotime_h');
        if (!is_numeric($videotime_h)) $videotime_h = 0;
        $videotime_i = mRequest('videotime_i');
        if (!is_numeric($videotime_i)) $videotime_i = 0;
        $videotime_s = mRequest('videotime_s');
        if (!is_numeric($videotime_s)) $videotime_s = 0;

        $videotime = $videotime_h*3600+$videotime_i*60+$videotime_s;
        if ($type==1 && $videotime==0) $this->ajaxReturn(1, '请填写视频文件时长！');

        return $videotime;
    }

    //获取图文内容
    private function _getImgtext($type=null)
    {
        $imgtext = mRequest('imgtext');
        if ($type==2 && !$imgtext) $this->ajaxReturn(1, '请填写图文内容！');

        return $imgtext;
    }

    //获取复习资料id
    private function _getReviewid()
    {
        $reviewid = mRequest('reviewid');
        // if (!$reviewid) $this->ajaxReturn(1, '请上传复习资料！');

        return $reviewid;
    }

    //获取搜索关键字
    private function _getKeywords()
    {
        $keywords = mRequest('keywords');
        $this->assign('keywords', $keywords);

        return $keywords;
    }

    //课程管理
    public function index()
    {
        $keywords = $this->_getKeywords();

        list($start, $length) = $this->_mkPage();
        $data = D('Course')->getCourse(null, null, null, $keywords, $start, $length);
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

    //上传课程示例图
    public function showimgupload()
    {
        //初始化上传类
        $Upload = new Upload();
        $Upload->maxSize  = $this->_upfilesize['image']['size'];
        $Upload->exts     = $this->_upfilesize['image']['exts'];
        $Upload->rootPath = UPLOAD_PATH;
        $Upload->savePath = 'course/img/';
        $Upload->saveName = array('uniqid', array('', true));
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
    
    //上传视频封面图
    public function videoimgupload()
    {
        //初始化上传类
        $Upload = new Upload();
        $Upload->maxSize  = $this->_upfilesize['image']['size'];
        $Upload->exts     = $this->_upfilesize['image']['exts'];
        $Upload->rootPath = UPLOAD_PATH;
        $Upload->savePath = 'course/cover/';
        $Upload->saveName = array('uniqid', array('', true));
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

    //上传课程视频
    public function videofileupload()
    {
        //初始化上传类
        $Upload = new Upload();
        $Upload->maxSize  = $this->_upfilesize['video']['size'];
        $Upload->exts     = $this->_upfilesize['video']['exts'];
        $Upload->rootPath = UPLOAD_PATH;
        $Upload->savePath = 'course/video/';
        $Upload->saveName = array('uniqid', array('', true));
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
    
    //上传复习资料
    public function reviewfileupload()
    {
        //初始化上传类
        $Upload = new Upload();
        $Upload->maxSize  = $this->_upfilesize['attach']['size'];
        $Upload->exts     = $this->_upfilesize['attach']['exts'];
        $Upload->rootPath = UPLOAD_PATH;
        $Upload->savePath = 'course/review/';
        $Upload->saveName = array('uniqid', array('', true));
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
            $savepath = '/'.UPLOAD_PT.$fileinfo['savepath'];
            $savename = $fileinfo['savename'];
            $filename = $fileinfo['name'];
            $filesize = $fileinfo['size'];
            $ext = $fileinfo['ext'];

            //复习资料信息写入数据库
            $reviewdata = array(
                'courseid' => 0,
                'savepath' => $savepath,
                'savename' => $savename,
                'filename' => $filename,
                'filesize' => $filesize,
                'ext' => $ext,
                'dloadnum' => 0,
                'createtime' => TIMESTAMP,
                'updatetime' => TIMESTAMP,
            );
            $reviewid = D('Course')->saveReview($reviewdata);

            $data = array(
                'filepath' => '',
                'filename' => $reviewid,
            );
        }

        $this->ajaxReturn($error, $msg, $data);
    }

    //发布课程
    public function newcourse()
    {
        $this->display();
    }

    //编辑课程
    public function upcourse()
    {
        $courseid = $this->_getCourseid();
        if (!$courseid) $this->pageReturn(1, '未知课程信息！', $this->_page_location);

        $courseinfo = D('Course')->getCourseByID($courseid);

        //计算视频时长
        $videotimes = timestohis($courseinfo['videotime']);
        $courseinfo['videotime_h'] = $videotimes['h'];
        $courseinfo['videotime_i'] = $videotimes['i'];
        $courseinfo['videotime_s'] = $videotimes['s'];

        $this->assign('courseinfo', $courseinfo);
        $this->display();
    }

    //保存课程
    public function coursesave()
    {
        $courseid = $this->_getCourseid();

        $title = $this->_getTitle();
        $classid = $this->_getClassid();
        $showimg = $this->_getShowimg();
        $type = $this->_getType();
        $videoimg = $this->_getVideoimg($type);
        $videopath = $this->_getVideopath($type);
        $videotime = $this->_getVideotime($type);
        $imgtext = $this->_getImgtext($type);
        $reviewid = $this->_getReviewid();

        if ($courseid) {
            $data = array(
                'title'      => $title,
                'type'       => $type,
                'videoimg'   => $videoimg,
                'videopath'  => $videopath,
                'videotime'  => $videotime,
                'imgtext'    => $imgtext,
                'showimg'    => $showimg,
                'classid'    => $classid,
                'updatetime' => TIMESTAMP,
            );
            $return = D('Course')->saveCourse($courseid, $data);

            if ($reviewid) D('Course')->saveReview(array('courseid'=>$courseid), $reviewid);
            if ($return) {
                $this->ajaxReturn(0, '课程编辑成功！');
            } else {
                $this->ajaxReturn(0, '课程编辑失败！');
            }
        } else {
            $data = array(
                'title'      => $title,
                'type'       => $type,
                'videoimg'   => $videoimg,
                'videopath'  => $videopath,
                'videotime'  => $videotime,
                'imgtext'    => $imgtext,
                'showimg'    => $showimg,
                'classid'    => $classid,
                'viewnum'    => 0,
                'learnnum'   => 0,
                'istesting'  => 0,
                'isshow'     => 1,
                'createtime' => TIMESTAMP,
                'updatetime' => TIMESTAMP,
            );
            $courseid = D('Course')->saveCourse(null, $data);

            if ($courseid) {
                if ($reviewid) D('Course')->saveReview(array('courseid'=>$courseid), $reviewid);

                $this->ajaxReturn(0, '课程发布成功！');
            } else {
                $this->ajaxReturn(0, '课程发布失败！');
            }
        }
    }

    //显示、隐藏课程
    public function enable()
    {
        $courseid = $this->_getCourseid();
        if (!$courseid) $this->ajaxReturn(1, '未知课程信息！');

        $isshow = mRequest('isshow');
        if (!in_array($isshow, array(0,1))) $this->ajaxReturn(1, '数据错误！');

        $data = array(
            'isshow' => $isshow,
            'updatetime' => TIMESTAMP,
        );
        $result = M('course')->where(array('courseid'=>$courseid))->save($data);
        if ($result) {
            $this->ajaxReturn(0, '成功！');
        } else {
            $this->ajaxReturn(1, '失败！');
        }
    }
}