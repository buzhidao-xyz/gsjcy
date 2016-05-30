<?php
/**
 * 用户模块控制器
 * buzhidao
 * 2015-12-27
 */
namespace Admin\Controller;

use Org\Util\Filter;
use Org\Util\String;

class UserController extends CommonController
{
    public function __construct()
    {
        parent::__construct();

        $this->_page_location = __APP__.'?s=User/index';

        $this->_course_class = CR('Course')->_course_class;
        $this->assign('courseclass', $this->_course_class);
    }

    //获取用户id
    private function _getUserid()
    {
        $userid = mRequest('userid');

        return $userid;
    }

    //获取账号
    private function _getAccount()
    {
        $account = mRequest('account');
        if (!Filter::F_Account($account)) $this->ajaxReturn(1, '请填写正确的账号！');

        return $account;
    }

    //获取密码
    private function _getPassword()
    {
        $password = mRequest('password');

        return $password;
    }

    //获取状态
    private function _getStatus()
    {
        $status = mRequest('status');
        if (!in_array($status, array(0,1))) $this->ajaxReturn(1, '请选择账号状态！');

        return $status;
    }

    //获取姓名
    private function _getUsername()
    {
        $username = mRequest('username');
        if (!$username) $this->ajaxReturn(1, '请填写党员真实姓名！');

        return $username;
    }

    //获取部门
    private function _getDepartment()
    {
        $department = mRequest('department');
        if (!$department) $this->ajaxReturn(1, '请填写党员所属部门！');

        return $department;
    }

    //获取职位
    private function _getPosition()
    {
        $position = mRequest('position');
        if (!$position) $this->ajaxReturn(1, '请填写党员工作职位！');

        return $position;
    }

    //获取党支部
    private function _getDangzhibu()
    {
        $dangzhibu = mRequest('dangzhibu');
        // if (!$dangzhibu) $this->ajaxReturn(1, '请填写党支部！');

        return $dangzhibu;
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
        $this->zhibulist = D('User')->getDangzhibu();
        $this->assign('zhibulist', $this->zhibulist['data']);

        $keywords = $this->_getKeywords();

        list($start, $length) = $this->_mkPage();
        $data = D('User')->getUser(null, null, $keywords, $start, $length);
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

    //新建账号
    public function newuser()
    {
        $this->zhibulist = D('User')->getDangzhibu();
        $this->assign('zhibulist', $this->zhibulist['data']);

        $this->assign("sidebar_active", array("User","index"));

        $this->display();
    }

    //查看账号 - 编辑
    public function profile()
    {
        $this->zhibulist = D('User')->getDangzhibu();
        $this->assign('zhibulist', $this->zhibulist['data']);
        
        $this->assign("sidebar_active", array("User","index"));

        $userid = $this->_getUserid();
        if (!$userid) $this->pageReturn(1, '未知账号信息！', $this->_page_location);

        $userinfo = D('User')->getUserByID($userid);
        $this->assign('userinfo', $userinfo);

        //统计课程学习情况
        $usercourselearninfo = D('User')->gcUserCourseLearn($userid, $this->_course_class);
        //统计作业完成情况
        $userworkfiledinfo = D('User')->getUserWorkFiled($userid, C('work_weight'));
        $this->assign('usergotscore', $usercourselearninfo['total']['weightscore']+$userworkfiledinfo['weightscore']);

        $this->display();
    }

    //保存账号
    public function usersave()
    {
        $userid = $this->_getUserid();

        $password = $this->_getPassword();
        if ($userid) {
            if ($password && !Filter::F_Password($password)) $this->ajaxReturn(1, '请填写正确的密码！');
        } else {
            if (!Filter::F_Password($password)) $this->ajaxReturn(1, '请填写正确的密码！');
        }

        $status = $this->_getStatus();
        $username = $this->_getUsername();
        $department = $this->_getDepartment();
        $position = $this->_getPosition();
        $dangzhibu = $this->_getDangzhibu();

        if ($password) {
            $ukey = String::randString(6, 3, '');
            $password = D('User')->passwordEncrypt($password, $ukey);
        }

        if ($userid) {
            $data = array(
                'username'   => $username,
                'department' => $department,
                'position'   => $position,
                'dangzhibu'  => $dangzhibu,
                'updatetime' => TIMESTAMP,
            );
            if ($password) {
                $data['password'] = $password;
                $data['ukey'] = $ukey;
            }
            $userid = D('User')->usersave($userid, $data);
        } else {
            $account = $this->_getAccount();
            //查询account是否已存在
            $flag = M('user')->where(array('account'=>$account))->count();
            if ($flag) $this->ajaxReturn(1, '账号已存在！');
            
            $data = array(
                'account'    => $account,
                'password'   => $password,
                'username'   => $username,
                'department' => $department,
                'position'   => $position,
                'dangzhibu'  => $dangzhibu,
                'ukey'       => $ukey,
                'status'     => $status,
                'loginnum'   => 0,
                'createtime' => TIMESTAMP,
                'updatetime' => TIMESTAMP,
            );
            $userid = D('User')->usersave(null, $data);
        }

        if ($userid) {
            $this->ajaxReturn(0, '党员账号保存成功！');
        } else {
            $this->ajaxReturn(1, '党员账号保存失败！');
        }
    }

    //启用、禁用账号
    public function enable()
    {
        $userid = $this->_getUserid();
        if (!$userid) $this->ajaxReturn(1, '未知账号信息！');

        $status = $this->_getStatus();
        if (!in_array($status, array(0,1))) $this->ajaxReturn(1, '数据错误！');

        $data = array(
            'status' => $status,
            'updatetime' => TIMESTAMP,
        );
        $result = M('user')->where(array('userid'=>$userid))->save($data);
        if ($result) {
            $this->ajaxReturn(0, '成功！');
        } else {
            $this->ajaxReturn(1, '失败！');
        }
    }

    //获取wid
    private function _getWid()
    {
        $wid = mRequest('wid');

        return $wid;
    }

    //反馈意见
    public function lvword()
    {
        $keywords = $this->_getKeywords();

        list($start, $length) = $this->_mkPage();
        $data = D('User')->getLvword($keywords, $start, $length);
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

    //反馈意见 - 删除
    public function lvworddel()
    {
        $wid = $this->_getWid();
        if (!$wid) $this->ajaxReturn(1, '未知反馈留言！');

        $result = M('lvword')->where(array('wid'=>$wid))->delete();
        if ($result) {
            $this->ajaxReturn(0, '反馈留言删除成功！');
        } else {
            $this->ajaxReturn(1, '反馈留言删除失败！');
        }
    }

    //学习达人
    public function learning()
    {
        $this->display();
    }
}