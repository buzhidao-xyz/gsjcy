<?php
/**
 * 作业模块控制器
 * buzhidao
 * 2015-12-27
 */
namespace Admin\Controller;

class WorkController extends CommonController
{
    // 作业分类
    public $_course_class = array(
        1 => array('id'=>1, 'name'=>'党建作业'),
        2 => array('id'=>2, 'name'=>'党史作业'),
    );

    // 作业类型
    public $_work_type = array(
        1 => array('id'=>1, 'name'=>'课程作业', 'remark'=>'*党员需要完成某课程的学习', 'checked'=>true),
        2 => array('id'=>2, 'name'=>'报告作业', 'remark'=>'*党员需要上传学习报告', 'checked'=>false),
    );
    
    public function __construct()
    {
        parent::__construct();

        $this->assign("sidebar_active", array("Work","index"));

        $this->_page_location = __APP__.'?s=Work/index';

        $this->assign("classlist", $this->_course_class);
        $this->assign("typelist", $this->_work_type);
    }

    //获取id
    private function _getWorkid()
    {
        $workid = mRequest('workid');

        return $workid;
    }

    //获取标题
    private function _getTitle()
    {
        $title = mRequest('title');
        if (!$title) $this->ajaxReturn(1, '请填写作业标题！');

        return $title;
    }

    //获取描述
    private function _getDesc()
    {
        $desc = mRequest('desc');
        if (!$desc) $this->ajaxReturn(1, '请填写作业描述！');

        return $desc;
    }

    //获取分类
    private function _getClassid()
    {
        $classid = mRequest('classid');
        if (!$classid) $this->ajaxReturn(1, '请选择作业分类！');

        return $classid;
    }

    //获取类型
    private function _getType()
    {
        $type = mRequest('type');
        if (!$type) $this->ajaxReturn(1, '请选择作业类型！');

        return $type;
    }

    //获取课程
    private function _getCourseid()
    {
        $courseid = mRequest('courseid');

        return $courseid;
    }

    //获取搜索关键字
    private function _getKeywords()
    {
        $keywords = mRequest('keywords');
        $this->assign('keywords', $keywords);

        return $keywords;
    }

    public function index()
    {
        $keywords = $this->_getKeywords();

        list($start, $length) = $this->_mkPage();
        $data = D('Work')->getWork(null, null, null, $keywords, $start, $length);
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

    //发布作业
    public function newwork()
    {
        //获取课程列表
        $courselist = D('Course')->getCourse();
        $courselist = $courselist['data'];
        $this->assign('courselist', $courselist);

        $this->display();
    }

    //查看作业 - 编辑
    public function profile()
    {
        $workid = $this->_getWorkid();
        if (!$workid) $this->pageReturn(1, '未知作业信息！', $this->_page_location);

        $workinfo = D('Work')->getWorkByID($workid);
        $this->assign('workinfo', $workinfo);

        //获取课程列表
        $courselist = D('Course')->getCourse();
        $courselist = $courselist['data'];
        $this->assign('courselist', $courselist);

        $this->display();
    }

    //保存作业信息
    public function worksave()
    {
        $workid = $this->_getWorkid();

        $title   = $this->_getTitle();
        $desc    = $this->_getDesc();
        $classid = $this->_getClassid();
        $type    = $this->_getType();
        $courseid = $this->_getCourseid();

        if ($type==1 && !$courseid) {
            $this->ajaxReturn(1, '请选择平台课程！');
        }
        if ($type==2) $courseid = 0;

        if ($workid) {
            $data = array(
                'title'    => $title,
                'desc'     => $desc,
                'classid'  => $classid,
                'type'     => $type,
                'courseid' => $courseid,
                'updatetime' => TIMESTAMP,
            );
            $workid = D('Work')->worksave($workid, $data);
        } else {
            $data = array(
                'title'    => $title,
                'desc'     => $desc,
                'classid'  => $classid,
                'type'     => $type,
                'courseid' => $courseid,
                'createtime' => TIMESTAMP,
                'updatetime' => TIMESTAMP,
            );
            $workid = D('Work')->worksave(null, $data);
        }
            
        if ($workid) {
            $this->ajaxReturn(0, '作业发布成功！');
        } else {
            $this->ajaxReturn(1, '作业发布失败！');
        }
    }
}