<?php
/**
 * 微信Http处理逻辑
 * buzhidao
 */
namespace Org\Net;

class Weixin
{
    //微信服务信息
    private $_wxinfo = array();

    //微信配置信息
    private $_wxconfig = array();

    //返回数据
    private $_result = array(
        'error' => 0,
        'msg'   => '',
        'data'  => array(),
    );

    public function __construct($wxinfo=array())
    {
        $this->_wxinfo = $wxinfo;

        //加载微信配置信息
        $this->_loadWXConfig();
    }

    //加载微信配置信息
    private function _loadWXConfig()
    {
        $wxconfig = C('WX');
        $this->_wxconfig = $wxconfig;
    }

    //解析返回结果
    private function _parseResult($result=array())
    {
        $_result = $this->_result;

        if ($result['error']) {
            $_result['error'] = 1;
            if ($result['error'] == 'curl_timeout') {
                $_result['msg'] = '网络错误 请求超时！';
            } else {
                $_result['msg'] = '请求出错 请稍后重试！';
            }
        } else {
            $result_result = json_decode($result['result'], true);
            if (isset($result_result['errcode']) && $result_result['errcode']>0) {
                $_result['error'] = 1;
                $_result['msg'] = isset($result_result['errmsg'])&&$result_result['errmsg'] ? $result_result['errmsg'] : '未知错误！';
            } else {
                $_result['data'] = $result_result;
            }
        }

        $this->_result = $_result;

        return $_result;
    }

    //获取access_token
    public function GetAccessToken()
    {
        $api = $this->_wxconfig['API']['GetAccessToken'];
        $api .= '?grant_type=client_credential&appid='.$this->_wxconfig['AppID'].'&secret='.$this->_wxconfig['AppSecret'];

        $HttpClient = Http::Init($api, 1);
        $result = $HttpClient->get(null, array(), array(), '', 0, array(
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ));

        $result = $this->_parseResult($result);
        return $result;
    }

    //获取微信用户基本信息 网页授权 SNSAPI_BASE
    public function Oauth2AccessToken($code=null)
    {
        if (!$code) return false;

        $api = $this->_wxconfig['API']['Oauth2AccessToken'];
        $api .= '?appid='.$this->_wxconfig['AppID'].'&secret='.$this->_wxconfig['AppSecret'].'&code='.$code.'&grant_type=authorization_code';

        $HttpClient = Http::Init($api, 1);
        $result = $HttpClient->get(null, array(), array(), '', 0, array(
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ));

        $result = $this->_parseResult($result);
        return $result;
    }

    //获取微信用户详细信息 网页授权 SNSUser
    public function Oauth2SNSUser($AccessToken=null, $OpenID=null)
    {
        if (!$AccessToken || !$OpenID) return false;

        $api = $this->_wxconfig['API']['Oauth2User'];
        $api .= '?access_token='.$AccessToken.'&openid='.$OpenID.'&lang=zh_CN';

        $HttpClient = Http::Init($api, 1);
        $result = $HttpClient->get(null, array(), array(), '', 0, array(
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ));

        $result = $this->_parseResult($result);
        return $result;
    }

    //创建菜单
    public function CreateMenu($menu=array())
    {
        if (!is_array($menu) || empty($menu)) return false;

        $api = $this->_wxconfig['API']['CreateMenu'];
        $api .= '?access_token='.$this->_wxinfo['access_token'];

        $datavars = json_encode($menu, JSON_UNESCAPED_UNICODE);

        $HttpClient = Http::Init($api, 1);
        $result = $HttpClient->post(null, $datavars, array(), '', 0, array(
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ));

        $result = $this->_parseResult($result);
        return $result;
    }
}