<?php
/**
 * 管理员管理
 * buzhidao
 * 2015-08-03
 */
namespace Admin\Controller;

use Org\Util\Filter;
use Org\Util\String;

class ManagerController extends CommonController
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->managerinfo['super']) $this->pageReturn(1, '账号没有管理权限！', __APP__.'?s=Index/index');

        $this->_page_location = __APP__.'?s=Manager/index';

        $this->assign("sidebar_active", array("Manager","index"));
    }

    //获取管理员ID
    private function _getManagerID()
    {
        $managerid = mRequest('managerid');
        return $managerid;
    }

    //获取账户Account
    private function _getAccount()
    {
        $account = mRequest('account');

        return $account;
    }

    //获取账户password
    private function _getPassword()
    {
        $password = mRequest('password');

        return $password;
    }

    //超级管理员标识
    private function _getSuper()
    {
        $super = mRequest('super');
        return $super;
    }

    //获取状态
    private function _getStatus()
    {
        $status = mRequest('status');
        $status = $status ? 1 : 0;

        return $status;
    }

    //获取角色信息
    private function _getRoleID()
    {
        $roleID = mRequest('roleID', false);
        return $roleID;
    }

    //获取管理员
    public function _getManager($start=0, $length=0)
    {
        //账户
        $account = $this->_getAccount();

        //获取管理员列表
        $result = D('Manager')->getManager(null, $account, $start, $length);
        $datatotal = $result['total'];
        $this->assign('datatotal', $datatotal);

        $datalist = array();
        if (is_array($result['data']) && !empty($result['data'])) {
            $autoindex = $start ? $start+1 : 1;
            foreach ($result['data'] as $manager) {
                $manager['autoindex'] = $autoindex++;

                $manager['supername'] = $manager['super'] ? '是' : '否';

                $datalist[] = $manager;
            }
        }
        $this->assign('datalist', $datalist);

        $param = array(
            'account'   => $account,
        );
        $this->assign('param', $param);
        //解析分页数据
        $this->_mkPagination($datatotal, $param);

        return array($datatotal, $datalist);
    }

    //判断是否是系统初始化默认管理员
    private function _ckSystemManager($managerid=null)
    {
        //判断是否是系统初始化默认管理员
        $system_manager = C('SYSTEM_MANAGER');
        if ((is_string($managerid) && $managerid==$system_manager['managerid'])
            || (is_array($managerid) && in_array($system_manager['managerid'], $managerid))) {
            $this->ajaxReturn(1, '系统初始化默认管理员禁止操作！');
        }
    }

    //管理员
    public function index()
    {
        list($start, $length) = $this->_mkPage();
        $this->_getManager($start, $length);

        $this->display();
    }

    //新增管理员
    public function newmanager()
    {
        $rolelist = D('Role')->getRole();
        $this->assign('rolelist', $rolelist['data']);

        $this->display();
    }

    //编辑管理员
    public function profile()
    {
        $managerid = $this->_getManagerID();
        $this->assign('managerid', $managerid);

        $minfo = D('Manager')->getManagerByID($managerid);
        $this->assign('minfo', $minfo);

        $rolelist = D('Role')->getRole();
        $this->assign('rolelist', $rolelist['data']);

        $this->display();
    }

    //保存新增、编辑管理员信息
    public function managersave()
    {
        $managerid = $this->_getManagerID();

        $password = $this->_getPassword();
        if (!Filter::F_Password($password)) $this->ajaxReturn(1, '请填写正确的密码！');
        $mkey = String::randString(6, 3, '');
        $password = D('User')->passwordEncrypt($password, $mkey);

        $status = $this->_getStatus();

        if ($managerid) {
            $data = array(
                'password'      => $password,
                'mkey'          => $mkey,
                'updatetime'    => TIMESTAMP,
            );
            $managerid = D('Manager')->saveManager($managerid, $data);
        } else {
            $account = $this->_getAccount();
            if (!Filter::F_Account($account)) $this->ajaxReturn(1, '请填写正确的账号！');

            //查询account是否已存在
            $flag = M('manager')->where(array('account'=>$account))->count();
            if ($flag) $this->ajaxReturn(1, '账号已存在！');

            $data = array(
                'account'       => $account,
                'password'      => $password,
                'mkey'          => $mkey,
                'status'        => $status,
                'supre'         => 0,
                'createtime'    => TIMESTAMP,
                'updatetime'    => TIMESTAMP,
                'createip'      => get_client_ip(0,true),
                'lastlogintime' => 0,
                'loginnum'      => 0,
                'isdelete'      => 0,
            );
            $managerid = D('Manager')->saveManager(null, $data);
        }
        
        if ($managerid) {
            $this->ajaxReturn(0, '保存成功！');
        } else {
            $this->ajaxReturn(1, '保存失败！');
        }
    }

    //启用、禁用管理员
    public function enable()
    {
        $managerid = $this->_getManagerID();
        if (!$managerid) $this->ajaxReturn(1, '未知管理员！');

        $status = $this->_getStatus();
        $status = $status ? 1 : 0;

        $result = M('manager')->where(array('managerid'=>$managerid))->save(array('status'=>$status));
        if ($result) {
            $this->ajaxReturn(0, '保存成功！');
        } else {
            $this->ajaxReturn(1, '保存失败！');
        }
    }

    //删除管理员
    public function managerdel()
    {
        $managerid = $this->_getManagerID();
        if (!$managerid) $this->ajaxReturn(1, '未知管理员！');

        $result = M('manager')->where(array('managerid'=>$managerid))->delete();
        if ($result) {
            $this->ajaxReturn(0, '删除成功！');
        } else {
            $this->ajaxReturn(1, '删除失败！');
        }
    }

    //日志管理
    public function log()
    {
        $this->display();
    }

    //管理员登录日志
    public function loginLog()
    {
        $this->display();
    }

    //管理员操作日志
    public function operateLog()
    {
        $this->display();
    }
}