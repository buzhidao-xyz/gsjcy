<?php
/**
 * Weixin Module 入口类
 * buzhidao
 */
namespace Weixin\Controller;

class IndexController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    //系统入口
    public function index()
    {
        $this->_wxlogic();

        $this->display();
    }

    //判断是否微信请求 分发
    private function _wxlogic()
    {
        //接口认证
        if (IS_GET && $_GET['signature'] && $_GET['timestamp'] && $_GET['nonce'] && $_GET['echostr']) {
            $this->_auth();
            exit;
        }

        $signature = $_REQUEST['signature'];
        $msg_signature = $_REQUEST['msg_signature'];
        $timestamp = $_REQUEST['timestamp'];
        $nonce = $_REQUEST['nonce'];
        $postdata = file_get_contents("php://input");

        //处理微信消息
        if ($signature && $timestamp && $nonce) {
            include(VENDOR_PATH.'Weixin/wxBizMsgCrypt.php');

            $Token = C('WX.Token');
            $EncodingAESKey = C('WX.EncodingAESKey');
            $AppID = C('WX.AppID');
            $Weixin = new \WXBizMsgCrypt($Token, $EncodingAESKey, $AppID);

            $xmldata = '';
            $errCode = $Weixin->decryptMsg($msg_signature, $timestamp, $nonce, $postdata, $xmldata);
            if ($errCode != 0) {
                echo $errCode;
                exit;
            } else {
                $XMLDom = new \DOMDocument();
                $XMLDom->loadXML($xmldata);
                $MsgType = $XMLDom->getElementsByTagName('MsgType')->item(0)->nodeValue;
                $Event = $XMLDom->getElementsByTagName('Event')->item(0)->nodeValue;

                //记录地理位置
                if ($MsgType == 'event' && $Event == 'LOCATION') {
                    $FromUserName = $XMLDom->getElementsByTagName('FromUserName')->item(0)->nodeValue;
                    $Latitude = $XMLDom->getElementsByTagName('Latitude')->item(0)->nodeValue;
                    $Longitude = $XMLDom->getElementsByTagName('Longitude')->item(0)->nodeValue;

                    D('User')->saveWXUserLatLng($FromUserName, $Latitude, $Longitude);
                }

                echo true;
                exit;
            }
        }
    }

    //微信接口认证
    private function _auth()
    {
        CR('Auth')->CKAuth();
    }
}