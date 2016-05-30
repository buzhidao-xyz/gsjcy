<?php
/**
 * 广告数据模型
 * 2015-12-22
 * buzhidao
 */
namespace Admin\Model;

class AdvertModel extends CommonModel
{
    public function __construct()
    {
        parent::__construct();
    }

    //获取轮播图片信息
    public function getScrollimage()
    {
        $total = M('scrollimage')->count();
        $result = M('scrollimage')->order('sortno asc, createtime desc')->select();

        return array('total'=>$total, 'data'=>is_array($result)?$result:array());
    }

    //保存轮播图片信息
    public function saveScrollimage($data=array())
    {
        if (!is_array($data)||empty($data)) return false;

        $result = M('scrollimage')->add($data);

        return $result ? $result : false;
    }
}