<?php
/**
 * 作业数据模型
 * 2015-12-22
 * buzhidao
 */
namespace Front\Model;

class WorkModel extends CommonModel
{
    public function __construct()
    {
        parent::__construct();
    }

    //获取作业
    public function getWork($workid=null, $classid=null, $userid=null, $start=0, $length=9999)
    {
        if (!$userid) return false;

        $where = array();
        if ($workid) $where['a.workid'] = $workid;
        if ($classid) $where['a.classid'] = $classid;

        $total = M('work')->alias('a')->where($where)->count();
        $result = M('work')->alias('a')->field('a.*, b.title as coursetitle, b.classid as courseclass, c.status, c.completetime, d.savepath, d.savename')
                           ->join(' left join __COURSE__ b on a.courseid=b.courseid ')
                           ->join(' left join __USER_WORK__ c on a.workid=c.workid and c.userid='.$userid)
                           ->join(' left join __USER_WORK_FILE__ d on a.workid=c.workid and c.userid='.$userid)
                           ->where($where)->order('a.createtime desc')->select();

        return array('total'=>$total, 'data'=>is_array($result)?$result:array());
    }

    //获取作业信息 通过ID
    public function getWorkByID($workid=null, $userid=null)
    {
        if (!$workid || !$userid) return false;

        $workinfo = $this->getWork($workid);

        return $workinfo['total'] ? $workinfo['data'][0] : array();
    }

    //获取未完成的作业数量
    public function getUndoneWorkNum($userid=null)
    {
        if (!$userid) return false;

        $where = array();

        $totalnum = M('work')->count();
        $donenum = M('work')->alias('a')->field('a.workid')->join(' __USER_WORK__ c on a.workid=c.workid and c.userid='.$userid)->count();

        return $totalnum-$donenum;
    }
}