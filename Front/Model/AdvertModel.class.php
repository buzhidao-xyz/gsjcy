<?php
/**
 * 文章数据模型
 * 2015-12-22
 * buzhidao
 */
namespace Front\Model;

class AdvertModel extends CommonModel
{
    public function __construct()
    {
        parent::__construct();
    }

    //获取首页轮播图片
    public function getSimg()
    {
        $where = array(
            'isshow' => 1,
        );
        $result = M('scrollimage')->where($where)->order('sortno asc, createtime desc')->select();

        return is_array($result) ? $result : array();
    }
}