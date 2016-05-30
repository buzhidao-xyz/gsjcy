<?php
/**
 * 帮助数据模型
 * 2015-12-22
 * buzhidao
 */
namespace Front\Model;

class HelpModel extends CommonModel
{
    public function __construct()
    {
        parent::__construct();
    }

    //获取常见问题
    public function getQA($qaid=null, $start=0, $length=9999)
    {
        $where = array();
        if ($qaid) $where['qaid'] = $qaid;

        $total = M('qa')->where($where)->count();
        $data = M('qa')->where($where)->order(array('createtime asc'))->limit($start, $length)->select();

        return array('total'=>$total, 'data'=>is_array($data)?$data:array());
    }

    //获取问题详细
    public function getQAByID($qaid=null)
    {
        if (!$qaid) return false;

        $qainfo = $this->getQA($qaid);

        return $qainfo['total']>0 ? array_pop($qainfo['data']) : array();
    }
}