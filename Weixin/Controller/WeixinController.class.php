<?php
/**
 * Weixin Module 服务类
 * buzhidao
 */
namespace Weixin\Controller;

use Org\Net\Weixin;

class WeixinController extends BaseController
{
    //微信配置信息
    protected $_wxconfig = array();

    //微信服务信息
    protected $_weixininfo = array(
        'access_token' => '',
    );

    public function __construct()
    {
        parent::__construct();

        //加载微信配置文件
        $this->_loadWXConfig();

        $this->_GSAccessToken();
    }

    //加载微信配置信息
    private function _loadWXConfig()
    {
        $wxconfig = C('WX');
        $this->_wxconfig = $wxconfig;
    }

    public function index()
    {

    }

    //存取Access_Token
    protected function _GSAccessToken()
    {
        $access_token = '';

        $account = C('WX.Account');
        $ServiceInfo = D('Weixin')->getServiceByAccount($account);
        if (empty($ServiceInfo)) {
            echo '未知微信公众号！';exit;
        }

        if (TIMESTAMP < $ServiceInfo['expiretime']) {
            //未过期
            $access_token = $ServiceInfo['accesstoken'];
        } else {
            //已过期 重新请求微信服务端
            $WXObj = new Weixin($this->_weixininfo);
            $result = $WXObj->GetAccessToken();

            if ($result['error']) $this->pageReturn(1, $result['msg']);

            $result_data = $result['data'];
            //存储access_token
            $access_token = $result_data['access_token'];
            $expiretime = TIMESTAMP+$result_data['expires_in']-1800;
            D('Weixin')->setServiceAccessTokenByAccount($account, $access_token, $expiretime);
        }

        $this->_weixininfo['access_token'] = $access_token;
    }

    //申请授权 获取微信用户基本信息 openid等
    public function getWXSNSUserBase($ck=1)
    {
        $mystate = 'Sessdw_snsapi_base';

        $state = mRequest('state');
        if (!$state) {
            $redirect_uri = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'].'?s=User/login';
            $redirect_location = session('location');
            if ($ck==0 && $redirect_location) $redirect_uri = $redirect_location;
            $location = $this->_wxconfig['API']['SNSAPI_BASE'];
            $location .= '?appid='.$this->_wxconfig['AppID'].'&redirect_uri='.urlencode($redirect_uri).'&response_type=code&scope=snsapi_base&state='.$mystate.'#wechat_redirect';
        
            header('location:'.$location);
            exit;
        }

        $code = mRequest('code');
        if (($state && $state!=$mystate) || !$code) $this->pageReturn();

        //调用Oauth2AccessToken获取用户基本信息
        $WXObj = new Weixin($this->_weixininfo);
        $result = $WXObj->Oauth2AccessToken($code);

        if ($result['error']) $this->pageReturn(1, '微信用户授权未完成！');

        $result_data = $result['data'];
        return array(
            'accesstoken'  => $result_data['access_token'],
            'expiresin'    => $result_data['expires_in'],
            'expiretime'   => TIMESTAMP+$result_data['expires_in']-1800,
            'refreshtoken' => $result_data['refresh_token'],
            'openid'       => $result_data['openid'],
            'scope'        => $result_data['scope'],
            'unionid'      => $result_data['unionid'],
        );
    }

    //申请授权 获取微信用户详细信息
    public function getWXSNSUserInfo($ck=1)
    {
        $mystate = 'Sessdw_snsapi_userinfo';

        $state = mRequest('state');
        if (!$state) {
            $redirect_uri = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'].'?s=User/login';
            $redirect_location = session('location');
            if ($ck==0 && $redirect_location) $redirect_uri = $redirect_location;
            $location = $this->_wxconfig['API']['SNSAPI_USERINFO'];
            $location .= '?appid='.$this->_wxconfig['AppID'].'&redirect_uri='.urlencode($redirect_uri).'&response_type=code&scope=snsapi_userinfo&state='.$mystate.'#wechat_redirect';

            header('location:'.$location);
            exit;
        }

        $code = mRequest('code');
        if (($state && $state!=$mystate) || !$code) $this->pageReturn();

        //调用Oauth2AccessToken获取用户基本信息
        $WXObj = new Weixin($this->_weixininfo);
        $result = $WXObj->Oauth2AccessToken($code);

        if ($result['error']) $this->pageReturn(1, '微信用户授权未完成！');

        $result_data = $result['data'];
        $WXUserBaseInfo = array(
            'accesstoken'  => $result_data['access_token'],
            'expiresin'    => $result_data['expires_in'],
            'expiretime'   => TIMESTAMP+$result_data['expires_in']-1800,
            'refreshtoken' => $result_data['refresh_token'],
            'openid'       => $result_data['openid'],
            'scope'        => $result_data['scope'],
        );

        //调用SNSUserInfo接口获取用户详细信息
        $WXObj = new Weixin($this->_weixininfo);
        $result = $WXObj->Oauth2SNSUser($WXUserBaseInfo['accesstoken'], $WXUserBaseInfo['openid']);

        if ($result['error']) $this->pageReturn(1, '微信用户信息获取失败！');

        $result_data = $result['data'];
        return array(
            'openid'    => $result_data['openid'],
            'nickname'  => $result_data['nickname'],
            'sex'       => $result_data['sex'],
            'province'  => $result_data['province'],
            'city'      => $result_data['city'],
            'country'   => $result_data['country'],
            'avatar'    => $result_data['headimgurl'],
            'privilege' => implode(',', $result_data['privilege']),
            'unionid'   => $result_data['unionid'],
        );
    }

    //创建菜单
    public function createmenu()
    {
        return false;

        $menu = C('WX.Menu');

        $WXObj = new Weixin($this->_weixininfo);
        $result = $WXObj->CreateMenu($menu);
        if ($result['error']) {
            echo $result['msg'];
            exit;
        }

        $result_data = $result['data'];
        echo $result_data['errmsg'];
    }
}