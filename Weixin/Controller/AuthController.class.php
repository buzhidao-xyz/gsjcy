<?php
/**
 * Weixin Module 认证类
 * buzhidao
 */
namespace Weixin\Controller;

class AuthController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    //接收signature 加密签名
    private function _getSignature()
    {
        $signature = mRequest('signature');
        return $signature;
    }

    //接收timestamp 时间戳
    private function _getTimestamp()
    {
        $timestamp = mRequest('timestamp');
        return $timestamp;
    }

    //接收nonce 随机数
    private function _getNonce()
    {
        $nonce = mRequest('nonce');
        return $nonce;
    }

    //接收echostr 随机字符串
    private function _getEchostr()
    {
        $echostr = mRequest('echostr');
        return $echostr;
    }

    //生成服务端加密字符串
    private function _GCMySignature()
    {
        $token = C('WX.Token');
        $timestamp = $this->_getTimestamp();
        $nonce = $this->_getNonce();

        $signatureArr = array($token, $timestamp, $nonce);
        sort($signatureArr, SORT_STRING);
        $signatureStr = implode('', $signatureArr);

        $mysignature = sha1($signatureStr);
        return $mysignature;
    }

    //认证微信请求
    public function CKAuth()
    {
        $echostr = $this->_getEchostr();
        $signature = $this->_getSignature();

        $mysignature = $this->_GCMySignature();

        if ($signature == $mysignature) {
            echo $echostr;
            return true;
        } else {
            return false;
        }
    }
}