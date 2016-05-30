<?php
/**
 * 帮助数据模型
 * 2015-12-22
 * buzhidao
 */
namespace Admin\Model;

class HelpModel extends CommonModel
{
    public function __construct()
    {
        parent::__construct();
    }

    //获取qa
    public function getQA($qaid=null, $keyword=null, $start=0, $length=9999)
    {
        $where = array();
        if ($qaid) $where['qaid'] = $qaid;
        if ($keyword) $where['_complex'] = array(
            '_logic' => 'or',
            'title'  => array('like', '%'.$keyword.'%'),
            'answer' => array('like', '%'.$keyword.'%'),
        );

        $total = M('qa')->where($where)->count();
        $result = M('qa')->where($where)->order('createtime desc')->select();

        return array('total'=>$total, 'data'=>is_array($result)?$result:array());
    }

    //获取问题 通过ID
    public function getQAByID($qaid=null)
    {
        if (!$qaid) return false;

        $qainfo = $this->getQA($qaid);

        return $qainfo['total'] ? $qainfo['data'][0] : array();
    }

    //保存问题
    public function qasave($qaid=null, $data=array())
    {
        if (!is_array($data) || empty($data)) return false;

        if ($qaid) {
            $result = M('qa')->where(array('qaid'=>$qaid))->save($data);
            !$result ? $qaid = false : null;
        } else {
            $qaid = M('qa')->add($data);
        }

        return $qaid ? $qaid : false;
    }
}