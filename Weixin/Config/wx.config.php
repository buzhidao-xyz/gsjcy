<?php
/**
 * 微信接口配置文件
 * buzhidao
 */

return array(
    //微信号
    'Account'        => 'sessdw',
    //应用ID
    'AppID'          => 'wx5dc76fc1b2f2b6cb',
    //应用密钥
    'AppSecret'      => '19417f7710d37f8e71714ccac228641d',
    //认证口令
    'Token'          => 'Szsamsungdw2016AWCVGHTK',
    //消息加密密钥
    'EncodingAESKey' => 'EyGpQEtN4j2XW8GplioQclxdRa1ZUqEqAzKBQm261KF',
    //微信API
    'API' => array(
        //获取access_token
        'GetAccessToken'    => 'https://api.weixin.qq.com/cgi-bin/token',
        //创建自定义菜单
        'CreateMenu'        => 'https://api.weixin.qq.com/cgi-bin/menu/create',
        //网页授权snsapi_base
        'SNSAPI_BASE'       => 'https://open.weixin.qq.com/connect/oauth2/authorize',
        //网页授权snsapi_userinfo
        'SNSAPI_USERINFO'   => 'https://open.weixin.qq.com/connect/oauth2/authorize',
        //网页获取access_token、openid等
        'Oauth2AccessToken' => 'https://api.weixin.qq.com/sns/oauth2/access_token',
        //网页获取用户详细信息
        'Oauth2User'        => 'https://api.weixin.qq.com/sns/userinfo',
    ),
    //自定义菜单
    'Menu' => array(
        'button' => array(
            array(
                'name' => '党务公示',
                'key'  => 'M1001',
                'sub_button' => array(
                    array(
                        'type' => 'view',
                        'name' => 'SESS党委简介',
                        'url'  => 'http://120.26.102.28/samsung/Weixin/index.php?s=Article/index',
                    ),
                    array(
                        'type' => 'click',
                        'name' => '党委组织结构',
                        'key'  => 'M1001_002_JIEGOU',
                    ),
                )
            ),
            array(
                'name' => '党建活动',
                'key'  => 'M2001',
                'sub_button' => array(
                    array(
                        'type' => 'click',
                        'name' => '活动公告',
                        'key'  => 'M2001_001_ACTIVITY',
                    ),
                    array(
                        'type' => 'click',
                        'name' => '活动分享',
                        'key'  => 'M2001_002_FENXIANG',
                    ),
                    array(
                        'type' => 'click',
                        'name' => '入党知识',
                        'key'  => 'M2001_003_ZHISHI',
                    ),
                    array(
                        'type' => 'click',
                        'name' => '来互动吧',
                        'key'  => 'M2001_004_HUDONG',
                    ),
                    array(
                        'type' => 'click',
                        'name' => '党刊',
                        'key'  => 'M2001_005_DANGKAN',
                    ),
                )
            ),
            array(
                'name' => '两学一做',
                'key'  => 'M3001',
                'sub_button' => array(
                    array(
                        'type' => 'view',
                        'name' => '新闻公告',
                        'url'  => 'http://139.196.199.135/Weixin/index.php?s=Article/index',
                    ),
                    array(
                        'type' => 'view',
                        'name' => '在线课程',
                        'url'  => 'http://139.196.199.135/Weixin/index.php?s=Course/index',
                    ),
                    array(
                        'type' => 'view',
                        'name' => '个人中心',
                        'url'  => 'http://139.196.199.135/Weixin/index.php?s=User/home',
                    ),
                )
            )
        )
    ),
);