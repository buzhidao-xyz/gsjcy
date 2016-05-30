<?php
/**
 * 用户模块逻辑控制
 * buzhidao
 * 2015-12-08
 */
namespace Front\Controller;

use Org\Util\Filter;
use Org\Util\String;

class UserController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->_course_class = C('USER.course_class');
        $this->assign('courseclass', $this->_course_class);
    }

    public function index(){}

    //获取账号
    private function _getAccount()
    {
        $account = mRequest('account');
        if (!Filter::F_Account($account)) $this->ajaxReturn(1, '账号或密码错误！');

        return $account;
    }

    //获取密码
    private function _getPassword()
    {
        $password = mRequest('password');
        if (!Filter::F_Password($password)) $this->ajaxReturn(1, '账号或密码错误！');

        return $password;
    }

    //登录
    public function login()
    {

    }

    //登录请求检查
    public function logincheck()
    {
        $account = $this->_getAccount();
        $password = $this->_getPassword();

        $userinfo = D('User')->getUser(null, $account);
        //登录失败
        if (!$userinfo || !is_array($userinfo) || empty($userinfo) || $userinfo['password']!=D('User')->passwordEncrypt($password,$userinfo['ukey'])) {
            $this->ajaxReturn(1, '账号或密码错误！');
        }

        $userid = $userinfo['userid'];

        //更新用户登录信息
        $ip = get_client_ip(0, true);
        M('user')->where(array('userid'=>$userid))->save(array(
            'lastlogintime' => TIMESTAMP,
            'lastloginip'   => $ip,
            'loginnum'      => $userinfo['loginnum']+1,
        ));

        //登录用户信息
        $userinfo = array(
            'userid'     => $userid,
            'account'    => $userinfo['account'],
            'username'   => $userinfo['username'],
            'department' => $userinfo['department'],
            'position'   => $userinfo['position'],
        );
        $this->_GSUserinfo($userinfo);

        $location = $this->_gotoIndex(false);
        $this->ajaxReturn(0, '', array(
            'location' => $location
        ));
    }

    //退出
    public function logout()
    {
        $this->_USUserinfo();

        $this->_gotoIndex();
    }

    //个人中心
    public function home()
    {
        $this->_CKUserLogon();
        $this->assign("homemenuflag", "home");

        $this->display();
    }

    //保存用户信息
    public function userinfosave()
    {
        $this->_CKUserLogon();

        $userid = $this->userinfo['userid'];

        $username = mRequest('username');
        if (!$username) $this->ajaxReturn(1, '请填写真实姓名！');
        $department = mRequest('department');
        if (!$department) $this->ajaxReturn(1, '请填写所在部门！');
        $position = mRequest('position');
        if (!$position) $this->ajaxReturn(1, '请填写个人职务！');

        $result = M('user')->where(array('userid'=>$userid))->save(array(
            'username'   => $username,
            'department' => $department,
            'position'   => $position,
            'updatetime' => TIMESTAMP,
        ));
        if ($result) {
            $this->ajaxReturn(0, '个人信息修改成功！');
        } else {
            $this->ajaxReturn(1, '个人信息修改失败！');
        }
    }

    //修改密码
    public function chpasswd()
    {
        $this->_CKUserLogon();
        $this->assign("homemenuflag", "chpasswd");

        $this->display();
    }

    //保存修改密码
    public function chpasswdsave()
    {
        $this->_CKUserLogon();

        $userid = $this->userinfo['userid'];

        $oldpasswd = mRequest('oldpasswd');
        if (!Filter::F_Password($oldpasswd)) $this->ajaxReturn(1, '原密码错误！');
        $newpasswd = mRequest('newpasswd');
        if (!Filter::F_Password($newpasswd)) $this->ajaxReturn(1, '新密码错误！');
        $newpasswd1 = mRequest('newpasswd1');
        if ($newpasswd != $newpasswd1) $this->ajaxReturn(1, '两次密码不一致！');

        $userinfo = M('user')->where(array('userid'=>$userid))->find();
        if ($userinfo['password'] != D('User')->passwordEncrypt($oldpasswd, $userinfo['ukey'])) {
            $this->ajaxReturn(1, '原密码错误！');
        }

        $result = M('user')->where(array('userid'=>$userid))->save(array(
            'password' => D('User')->passwordEncrypt($newpasswd, $userinfo['ukey']),
            'updatetime' => TIMESTAMP,
        ));
        if ($result) {
            $this->ajaxReturn(0, '密码修改成功！');
        } else {
            $this->ajaxReturn(1, '密码修改失败！');
        }
    }

    //反馈留言
    public function lvword()
    {
        $this->_CKUserLogon();
        $this->assign("homemenuflag", "lvword");

        $this->display();
    }

    //保存反馈留言
    public function lvwordsave()
    {
        $this->_CKUserLogon();

        $userid = $this->userinfo['userid'];

        $title = mRequest('title');
        if (!$title) $this->ajaxReturn(1, '请填写标题！');
        $content = mRequest('content');
        if (!$content) $this->ajaxReturn(1, '请填写内容！');

        $result = M('lvword')->add(array(
            'userid' => $userid,
            'title' => $title,
            'content' => $content,
            'createtime' => TIMESTAMP,
            'updatetime' => TIMESTAMP,
        ));
        if ($result) {
            $this->ajaxReturn(0, '反馈留言提交成功！');
        } else {
            $this->ajaxReturn(1, '反馈留言提交失败！');
        }
    }

    //课程学习经历
    public function courselist()
    {
        $this->_CKUserLogon();
        $this->assign("homemenuflag", "courselist");

        $userid = $this->userinfo['userid'];

        list($start, $length) = $this->_mkPage();
        $data = D('User')->getUserCourse($userid, null, $start, $length);
        $total = $data['total'];
        $usercourselist = $data['data'];

        $this->assign('usercourselist', $usercourselist);

        //统计课程学习情况
        $usercourselearninfo = D('User')->gcUserCourseLearn($userid, $this->_course_class);
        //统计作业完成情况
        $userworkfiledinfo = D('User')->getUserWorkFiled($userid, C('USER.work_weight'));
        $this->assign('usergotscore', $usercourselearninfo['total']['weightscore']+$userworkfiledinfo['weightscore']);

        //解析分页数据
        $this->_mkPagination($total);

        $this->display();
    }

    //学习达人
    public function learning()
    {
        //获取党员学习得分排名 前十名
        $userlearninglist = D('User')->getUserLearninglist();
        $this->assign('userlearninglist', $userlearninglist);
        
        $this->display();
    }

    //支部学习管理
    public function zbxx()
    {
        //各支部学习进度统计
        $zhibuLearnStats = D('User')->zhibuLearnStats();
        $this->assign('zhibuLearnStats', $zhibuLearnStats);

        $this->display();
    }
}