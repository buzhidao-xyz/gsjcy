<?php
/**
 * 作业模型逻辑控制
 * buzhidao
 * 2015-12-08
 */
namespace Front\Controller;

use Any\Upload;

class WorkController extends CommonController
{
    //导航栏目navflag标识
    public $navflag = 'Work';

    public function __construct()
    {
        parent::__construct();

        $this->_work_class = C('USER.work_class');
        $this->assign('workclass', $this->_work_class);

        $this->_work_type = C('USER.work_type');
        $this->assign('worktype', $this->_work_type);

        //获取用户作业完成情况
        $this->userworkinfo = D('User')->getUserWorkDone($this->userinfo['userid'], $this->_work_class);
        $this->assign('userworkinfo', $this->userworkinfo);
    }

    //获取分类Id
    private function _getClassid()
    {
        $classid = mRequest('classid');

        return $classid;
    }

    //获取作业id
    private function _getWorkid()
    {
        $workid = mRequest('workid');

        return $workid;
    }

    //作业首页
    public function index()
    {
        $classid = $this->_getClassid();
        $classid = !$classid ? 1 : $classid;
        $this->assign('classid', $classid);

        $userid = $this->userinfo['userid'];

        //检查作业完成情况
        D('User')->ckUserCourseWork($userid);

        list($start, $length) = $this->_mkPage();
        $data = D('Work')->getWork(null, $classid, $userid, $start, $length);
        $total = $data['total'];
        $worklist = $data['data'];

        $this->assign('worklist', $worklist);

        $param = array(
            'classid' => $classid,
        );
        $this->assign('param', $param);
        //解析分页数据
        $this->_mkPagination($total, $param);

        $this->display();
    }

    //上传文件 - 作业报告
    public function workfileupload()
    {
        //初始化上传类
        $Upload = new Upload();
        $Upload->maxSize  = 2097152; //2M
        $Upload->exts     = array('doc', 'docx');
        $Upload->rootPath = UPLOAD_PATH;
        $Upload->savePath = 'file/workfile/';
        $Upload->saveName = array('uniqid', array('', true));
        $Upload->autoSub  = true;
        $Upload->subName  = array('date', 'Ym');

        //上传
        $error = null;
        $msg = '报告提交成功！';
        $data = array();
        $info = $Upload->upload();
        if (!$info) {
            $error = 1;
            $msg = $Upload->getError();
        } else {
            $workid = mRequest('workid');
            if (!$workid) $this->ajaxReturn(1, '请选择作业！');

            $fileinfo = array_shift($info);
            $data = array(
                'userid' => $this->userinfo['userid'],
                'workid' => $workid,
                'savepath' => '/'.UPLOAD_PT.$fileinfo['savepath'],
                'savename' => $fileinfo['savename'],
                'filename' => $fileinfo['name'],
                'filesize' => $fileinfo['size'],
                'ext' => $fileinfo['ext'],
                'createtime' => TIMESTAMP,
            );

            //开始事务
            M('testing')->startTrans();
            $userworkid = M('user_work')->add(array(
                'userid' => $this->userinfo['userid'],
                'workid' => $workid,
                'status' => 1,
                'createtime' => TIMESTAMP,
            ));
            $fileid = M('user_work_file')->add($data);
            if ($userworkid && $fileid) {
                M('user_work')->commit();
            } else {
                M('user_work')->rollback();
                $error = 1;
                $msg = '报告提交失败！请重新提交！';
            }
        }

        $this->ajaxReturn($error, $msg, $data);
    }
}