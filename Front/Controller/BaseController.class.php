<?php
/**
 * 该模块 所有类的父类
 * buzhidao
 * 2015-12-07
 */
namespace Front\Controller;

use Any\Controller;
use Org\Util\Log;

class BaseController extends Controller
{
    //分页默认参数
    protected $_page     = 1;
    protected $_pagesize = 15;
    
    //用户登录信息 session存储
    protected $userinfo;

    //导航栏目navflag标识
    public $navflag;
    
    //上传文件标准 图片:2M 视频:200M
    protected $_upfile = array(
        'image' => array(
            'size' => 2097152,
            'exts' => array('jpg', 'gif', 'png', 'jpeg'),
        ),
        'video' => array(
            'size' => 209715200,
            'exts' => array('mp4', 'swf', 'flv', 'mp3'),
        ),
        'attach' => array(
            'size' => 20971520,
            'exts' => array('pdf', 'doc', 'docx', 'xls', 'xlsx'),
        ),
    );
    
    public function __construct()
    {
        parent::__construct();

        //加载语言包
        $this->_loadLang();

        //输出系统配置
        $this->_assignConfig();
        //输出系统参数
        $this->_assignSystem();
        //输出框架参数
        $this->_assignAny();

        //记录请求日志
        $this->_accessLog();

        //获取登录信息
        $this->_GSUserinfo();

        //输出导航栏目navflag标识
        $this->assign('navflag', $this->navflag);

        //获取作业数量（未完成）
        $undoneworknum = D('Work')->getUndoneWorkNum($this->userinfo['userid']);
        $this->assign('undoneworknum', $undoneworknum);
    }

    /**
     * 加载语言包
     */
    private function _loadLang()
    {
        $lang = C('DEFAULT_LANG');

        //加载公共语言包
        include(LANG_PATH.$lang.'.php');
        L($lang);
        //加载控制器语言包
        include(LANG_PATH.$lang.'/'.CONTROLLER_NAME.'.php');
        L($lang);
    }

    /**
     * 输出系统配置
     */
    private function _assignConfig()
    {
        $SERVER = array();

        //服务器HOST
        $this->assign('HOST_PATH', HOST_PATH);

        //服务器HOST
        $HOST = C('HOST');
        $SERVER['HOST'] = $HOST;
        $this->assign('SERVER', $SERVER);
    }

    //输出系统参数
    private function _assignSystem()
    {
        $SYSTEM = array(
            'systemtitle' => array(
                'name'  => '系统名称',
                'key'   => 'systemtitle',
                'value' => '三星半导体 - 两学一做平台',
            ),
        );
        $this->assign('SYSTEM', $SYSTEM);
    }

    //输出框架参数
    private function _assignAny()
    {
        $ANY = array(
            '__APP__' => __APP__,
        );
        $this->assign('ANY', $ANY);
    }

    /**
     * 记录请求日志
     */
    private function _accessLog()
    {
        Log::record('access',array(
            'ModuleName'  => MODULE_NAME,
            'ServerIp'    => $_SERVER['SERVER_ADDR'].':'.$_SERVER['SERVER_PORT'],
            'ClientIp'    => get_client_ip(),
            'DateTime'    => date('Y-m-d H:i:s', TIMESTAMP),
            'TimeZone'    => 'UTC'.date('O',TIMESTAMP),
            'Method'      => $_SERVER['REQUEST_METHOD'],
            'URL'         => $_SERVER['REQUEST_URI'],
            'Protocol'    => $_SERVER['SERVER_PROTOCOL'],
            'RequestData' => $_REQUEST,
        ));
    }

    /**
     * AJAX返回数据
     * @param int $error 是否产生错误信息 0没有错误信息 1有错误信息
     * @param string $msg 如果有错 msg为错误信息
     * @param array $data 返回的数据 多维数组
     * @return json 统一返回json数据
     */
    protected function ajaxReturn($error=0,$msg=null,$data=array())
    {
        if ($error && !$msg) {
            $error = 1;
            $msg   = L('ajaxreturn_error_msg');
            $data  = array();
        }

        if (!$error && !is_array($data)) {
            $error = 1;
            $msg = L('ajaxreturn_error_msg');
            $data = array();
        }

        //APP返回
        $return = array(
            'error' => $error,
            'msg'   => $msg,
            'data'  => $data
        );

        $type = 'json';
        switch ($type) {
            case 'json':
                header('Content-Type: application/json');
                $return = json_encode($return);
                break;
            default:
                header('Content-Type: application/json');
                $return = json_encode($return);
                break;
        }

        echo $return;
        exit;
    }

    /**
     * 页面返回数据 展示提示信息
     * @param int $error 是否产生错误信息 0没有错误信息 1有错误信息 大于1为其他错误码
     * @param string $msg 如果有错 msg为错误信息
     * @param array $data 返回的数据 多维数组
     */
    protected function pageReturn($error=0,$msg=null,$data=array())
    {
        if ($error && !$msg) {
            $error = 1;
            $msg   = L('pagereturn_error_msg');
            $data  = array();
        }

        if (!$error && !is_array($data)) {
            $error = 1;
            $msg = L('pagereturn_error_msg');
            $data = array();
        }

        //page数据
        $pageReturn = array(
            'error' => $error,
            'msg'   => $msg,
            'data'  => $data
        );
        $this->assign('pagereturn', $pageReturn);

        $this->display('Public/pagereturn');
        exit;
    }

    //跳转到系统首页
    protected function _gotoIndex($goto=true)
    {
        $location = __APP__.'?s=Index/index';
        if ($goto) {
            header('Location:'.$location);
            exit;
        } else {
            return $location;
        }
    }

    //获取页码 默认1
    protected function _getPage($page=0)
    {
        $_page = $page ? $page : $this->_page;
        $page = mGet('page');

        is_numeric($page)&&$page>0 ? $_page = $page : null;

        return $_page;
    }

    //获取每页记录数
    //默认每页记录数30
    protected function _getPagesize($pagesize=0)
    {
        $_pagesize = $pagesize ? $pagesize : $this->_pagesize;
        $pagesize  = mGet('pagesize');

        is_numeric($pagesize)&&$pagesize>0 ? $_pagesize = $pagesize : null;

        return $_pagesize;
    }

    /**
     * 分页预处理
     * @access private
     * @param void
     * @return void
     */
    protected function _mkPage()
    {
        $page     = $this->_getPage();
        $pagesize = $this->_getPagesize();

        //开始行号
        $start     = ($page-1)*$pagesize;
        //数据长度
        $length    = $pagesize;

        //返回
        return array($start,$length);
    }

    //总页数
    protected function _mkPagination($total=0, $param=array())
    {
        if (!$total) return false;

        $sshowpages = 5;

        //page参数
        $page     = $this->_getPage();
        $pagesize = $this->_getPagesize();

        //请求参数
        $query_string = explode('&', $_SERVER['QUERY_STRING']);
        foreach ($query_string as $k=>$q) {
            $p = explode('=', $q);
            if ($p[0]=='page' || in_array($p[0], array_keys($param))) {
                unset($query_string[$k]);
            }
        }
        foreach ($param as $k=>$v) {
            $query_string[] = $k.'='.$v;
        }
        $query_string[] = 'page=';
        //页面url
        $url = $_SERVER['PHP_SELF'].'?'.implode('&', $query_string);

        //总页数
        $totalpage = ceil($total/$pagesize);
        $start = 1;
        $end = $totalpage<(2*$sshowpages+1) ? $totalpage : (2*$sshowpages+1);
        if ($totalpage > (2*$sshowpages+1)) {
            if ($page <= ($sshowpages+1)) {
            } else if ($totalpage-$page < ($sshowpages+1)) {
                $start = $totalpage-2*$sshowpages;
                $end = $totalpage;
            } else {
                $start = $page-$sshowpages;
                $end = $page+$sshowpages;
            }
        }
        //列出的页码 当前页前后5页
        $listpage = range($start, $end);

        $prevpage = $page-1;
        $nextpage = $page+1;
        $pagination = array(
            'url' => $url,
            'totalpage' => $totalpage,
            'firstpage' => 1,
            'prevpage'  => $prevpage<0 ? 0 : $prevpage,
            'curtpage'  => $page,
            'nextpage'  => $nextpage>$totalpage ? 0 : $nextpage,
            'lastpage'  => $totalpage,
            'listpage'  => $listpage,
        );
        $this->assign('pagination', $pagination);
        return $pagination;
    }

    /**
     * 检查登录状态
     */
    protected function _CKUserLogon()
    {
        $userinfo = session('userinfo');
        //如果未登录 跳转到登录页Admin/Login
        if (!$userinfo || !is_array($userinfo)) {
            $this->_gotoIndex();
        }

        return true;
    }

    /**
     * 存取登录信息 session
     * @param int $isrefresh 是否刷新session 0:不刷新 1:刷新 默认1
     */
    protected function _GSUserinfo($userinfo=array(),$isrefresh=1)
    {
        if (!is_array($userinfo)) return false;

        $suserinfo = session('userinfo');
        !is_array($suserinfo) ? $suserinfo = array() : null;
        if (!empty($userinfo)) {
            $suserinfo = array_merge($suserinfo, $userinfo);

            session('userinfo',$suserinfo);
            // //如果60秒内连续请求 不刷新sessionid
            // $session_regenerate_expire_no = session('session_regenerate_expire_no');
            // if (!$session_regenerate_expire_no) {
            //     //刷新sessionid
            //     $isrefresh ? session('[regenerate]') : null;
            //     session('session_regenerate_expire_no', 1, 60);
            // }
        }

        $this->userinfo = $suserinfo;
        $this->assign('userinfo', $this->userinfo);

        return is_array($suserinfo)&&!empty($suserinfo) ? $suserinfo : array();
    }

    /**
     * 注销登录信息session
     */
    protected function _USUserinfo()
    {
        session('userinfo',null);
    }
}