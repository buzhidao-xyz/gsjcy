<?php
/**
 * 作业数据模型
 * 2015-12-22
 * buzhidao
 */
namespace Admin\Model;

class WorkModel extends CommonModel
{
    public function __construct()
    {
        parent::__construct();
    }

    //获取作业
    public function getWork($workid=null, $classid=null, $type=null, $keyword=null, $start=0, $length=9999)
    {
        $where = array();
        if ($workid) $where['a.workid'] = $workid;
        if ($classid) $where['a.classid'] = $classid;
        if ($type) $where['a.type'] = $type;
        if ($keyword) $where['_complex'] = array(
            '_logic' => 'or',
            'a.title'  => array('like', '%'.$keyword.'%'),
            'a.desc'   => array('like', '%'.$keyword.'%'),
        );

        $total = M('work')->alias('a')->where($where)->count();
        $result = M('work')->alias('a')->field('a.*, b.title as coursetitle')->join(' left join __COURSE__ b on a.courseid=b.courseid ')->where($where)->order('createtime desc')->limit($start, $length)->select();

        return array('total'=>$total, 'data'=>is_array($result)?$result:array());
    }

    //获取作业信息 通过ID
    public function getWorkByID($workid=null)
    {
        if (!$workid) return false;

        $workinfo = $this->getWork($workid);

        return $workinfo['total'] ? $workinfo['data'][0] : array();
    }

    //保存作业信息
    public function worksave($workid=null, $data=array())
    {
        if (!is_array($data) || empty($data)) return false;

        if ($workid) {
            $result = M('work')->where(array('workid'=>$workid))->save($data);
            $result = $result ? $workid : false;
        } else {
            $workid = M('work')->add($data);
        }

        return $workid ? $workid : false;
    }
}