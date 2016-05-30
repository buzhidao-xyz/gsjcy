<?php
/**
 * 会员数据模型
 * 2015-12-22
 * buzhidao
 */
namespace Admin\Model;

class UserModel extends CommonModel
{
    public function __construct()
    {
        parent::__construct();
    }

    //加密会员密码
    public function passwordEncrypt($password=null, $ukey=null)
    {
        return md5(md5($password).$ukey);
    }

    //获取会员
    public function getUser($userid=null, $status=null, $keyword=null, $start=0, $length=9999)
    {
        $where = array();
        if ($userid) $where['userid'] = $userid;
        if ($status !== null) $where['status'] = $status;
        if ($keyword) $where['_complex'] = array(
            '_logic' => 'or',
            'account'  => array('like', '%'.$keyword.'%'),
            'username'   => array('like', '%'.$keyword.'%'),
        );

        $total = M('user')->where($where)->count();
        $result = M('user')->where($where)->order('createtime desc')->limit($start, $length)->select();

        $data = array();
        $userids = array(0);
        if (is_array($result)&&!empty($result)) {
            foreach ($result as $d) {
                $data[$d['userid']] = array_merge($d, array(
                    'coursednum' => 0,
                    'courseid' => 0,
                    'coursetitle' => '',
                    'coursestatus' => 0,
                    'completetime' => 0,
                ));
                $userids[] = $d['userid'];
            }
        }

        //获取党员已完成课时（已测评）
        $coursednum = M('user_course')->alias('a')->field('a.userid, count(a.courseid) as coursednum')
                    ->where(array('a.userid'=>array('in', $userids), 'a.status'=>array('in', array(2))))
                    ->group('a.userid')
                    ->select();
        if (is_array($coursednum)&&!empty($coursednum)) {
            foreach ($coursednum as $d) {
                $data[$d['userid']]['coursednum'] = $d['coursednum'];
            }
        }

        //获取党员学习经历
        $usersubsql = M('user_course')->alias('a')
                    ->field('a.*, b.title')->join(' __COURSE__ b on a.courseid=b.courseid ')
                    ->where(array('a.userid'=>array('in', $userids), 'a.status'=>array('in', array(1,2))))
                    ->order('a.completetime desc')
                    ->buildSql();
        $usercourse = M('user_course')->table($usersubsql.' sub')->group('sub.userid')->select();
        //分析数据
        if (is_array($usercourse)&&!empty($usercourse)) {
            foreach ($usercourse as $d) {
                $data[$d['userid']]['courseid'] = $d['courseid'];
                $data[$d['userid']]['coursetitle'] = $d['title'];
                $data[$d['userid']]['coursestatus'] = $d['status'];
                $data[$d['userid']]['completetime'] = $d['completetime'];
            }
        }

        return array('total'=>$total, 'data'=>$data);
    }

    //获取会员信息 通过ID
    public function getUserByID($userid=null)
    {
        if (!$userid) return false;

        $userinfo = $this->getUser($userid);
        $userinfo = $userinfo['total'] ? array_pop($userinfo['data']) : array();

        $where = array(
            'a.userid' => $userid,
            'a.status' => array('in', array(1,2)),
        );
        $usercourselist = M('user_course')->alias('a')->join(' __COURSE__ b on a.courseid=b.courseid and b.isshow=1 ')->field('a.*, b.title, b.classid')->where($where)->order('a.completetime desc')->select();

        $userinfo['usercourselist'] = is_array($usercourselist) ? $usercourselist : array();

        return $userinfo;
    }
    //统计课程学习情况
    public function gcUserCourseLearn($userid=null, $courseclass=array())
    {
        if (!$userid || !is_array($courseclass) || empty($courseclass)) return false;

        //课程学习情况
        $usercourselearninfo = array(
            'listi' => array(),
            'total' => array(
                'coursetotalnum' => 0,
                'courselearnnum' => 0,
                'coursenonenum'  => 0,
                'testingtotalnum' => 0,
                'testingdonenum' => 0,
                'testingnonenum'  => 0,
                'percent'        => 0,
                'totalscore' => 0,
                'avgscore' => 0,
                'weightscore' => 0,
            ),
        );

        //课程试卷完成情况 按分类统计
        foreach ($courseclass as $classinfo) {
            $subquery = M('testing')->alias('a')->join(' __COURSE__ b on a.courseid=b.courseid and b.isshow=1 and b.classid='.$classinfo['id'])->field('a.*, b.title, b.classid')->where(array('a.status'=>1))->buildSql();
            //该分类总课程数
            $coursetotalnum = M('course')->where(array('isshow'=>1, 'classid'=>$classinfo['id']))->count();
            //已学习课程数
            $courselearnnum = M('course')->alias('a')->join(' __USER_COURSE__ b on a.courseid=b.courseid and b.userid='.$userid.' and b.status in (1,2) ')->where(array('isshow'=>1, 'classid'=>$classinfo['id']))->count();
            
            //该分类总测评数
            $testingtotalnum = M('testing')->table($subquery.' sub')->count();
            //已完成测评数
            $testingdonenum = M('testing')->table($subquery.' sub')->join(' __USER_TESTING__ ut on ut.testingid=sub.testingid and ut.userid='.$userid.' and ut.status=1 ')->count();
            //未完成测评数
            $testingnonenum = $testingtotalnum-$testingdonenum;
            $testingpercent = $testingtotalnum>0 ? floor($testingdonenum/$testingtotalnum*100) : 0;

            //课程测评总得分数
            $totalscore = M('user_testing')->alias('ut')->join(' inner join '.$subquery.' sub on ut.testingid=sub.testingid ')->where(array('ut.userid'=>$userid))->sum('ut.gotscore');
            $totalscore = $totalscore>0 ? $totalscore : 0;

            //计算该类平均分
            $avgscore = $testingtotalnum>0 ? floor($totalscore/$testingtotalnum) : 0;
            //计算权重分
            $weightscore = floor($avgscore*$classinfo['weight']);
            
            $coursenonenum = $coursetotalnum-$courselearnnum;
            $percent = $coursetotalnum>0 ? floor($courselearnnum/$coursetotalnum*100) : 0;
            $usercourselearninfo['listi'][$classinfo['id']] = array(
                'coursetotalnum' => $coursetotalnum,
                'courselearnnum' => $courselearnnum,
                'coursenonenum'  => $coursenonenum,
                'testingtotalnum'  => $testingtotalnum,
                'testingdonenum'   => $testingdonenum,
                'testingnonenum'   => $testingnonenum,
                'percent'        => $percent,
                'testingpercent' => $testingpercent,
                'totalscore'     => $totalscore,
                'avgscore'       => $avgscore,
                'weightscore'    => $weightscore,
            );

            //合计 课程数量、总分、权重分
            $usercourselearninfo['total']['coursetotalnum'] += $coursetotalnum;
            $usercourselearninfo['total']['courselearnnum'] += $courselearnnum;
            $usercourselearninfo['total']['coursenonenum'] += $coursenonenum;
            $usercourselearninfo['total']['testingtotalnum'] += $testingtotalnum;
            $usercourselearninfo['total']['testingdonenum']  += $testingdonenum;
            $usercourselearninfo['total']['testingnonenum']  += $testingnonenum;
            $usercourselearninfo['total']['totalscore'] += $totalscore;
            $usercourselearninfo['total']['weightscore'] += $weightscore;
        }

        //合计 计算平均分
        $usercourselearninfo['total']['avgscore'] = $usercourselearninfo['total']['testingtotalnum']>0 ? floor($usercourselearninfo['total']['totalscore']/$usercourselearninfo['total']['testingtotalnum']) : 0;
        $usercourselearninfo['total']['percent'] = $usercourselearninfo['total']['coursetotalnum']>0 ? floor($usercourselearninfo['total']['courselearnnum']/$usercourselearninfo['total']['coursetotalnum']*100) : 0;
        $usercourselearninfo['total']['testingpercent'] = $usercourselearninfo['total']['testingtotalnum']>0 ? floor($usercourselearninfo['total']['testingdonenum']/$usercourselearninfo['total']['testingtotalnum']*100) : 0;

        return $usercourselearninfo;
    }
    //获取用户上传报告的作业完成情况
    public function getUserWorkFiled($userid=null, $weight=1)
    {
        if (!$userid) return false;

        //上传报告的作业总数
        $worktotalnum = M('work')->where(array('type'=>2))->count();
        //已完成的上传报告的作业总数
        $workdonenum = M('user_work')->alias('a')->join(' __WORK__ b on b.workid=a.workid and b.type=2 ')->where(array('a.userid'=>$userid, 'a.status'=>1))->count();
        //总得分
        $totalscore = $workdonenum*100;
        //平均得分
        $avgscore = $worktotalnum>0 ? floor($totalscore/$worktotalnum) : 0;
        //权重得分
        $weightscore = floor($avgscore*$weight);

        return array(
            'worktotalnum' => $worktotalnum,
            'workdonenum'  => $workdonenum,
            'totalscore'   => $totalscore,
            'avgscore'     => $avgscore,
            'weightscore'  => $weightscore,
        );
    }

    //保存会员信息
    public function usersave($userid=null, $data=array())
    {
        if (!is_array($data) || empty($data)) return false;

        if ($userid) {
            $result = M('user')->where(array('userid'=>$userid))->save($data);
            $result = $result ? $userid : false;
        } else {
            $userid = M('user')->add($data);
        }

        return $userid ? $userid : false;
    }

    //获取党支部信息
    public function getDangzhibu($zhibuid=null)
    {
        $where = array();
        if ($zhibuid) $where['zhibuid'] = $zhibuid;

        $total = M('dangzhibu')->where($where)->count();
        $result = M('dangzhibu')->where($where)->order('zhibuid asc')->select();
        $data = array();
        if (is_array($result)&&!empty($result)) {
            foreach ($result as $d) {
                $data[$d['zhibuid']] = array(
                    'zhibuid' => $d['zhibuid'],
                    'zhibuname' => $d['zhibuname']
                );
            }
        }

        return array('total'=>$total, 'data'=>$data);
    }

    public function getDangzhibuByID($zhibuid=null)
    {
        if (!$zhibuid) return false;

        $zhibuinfo = $this->getDangzhibu($zhibuid);

        return $zhibuinfo['total'] ? array_shift($zhibuinfo['data']) : array();
    }

    //获取反馈留言
    public function getLvword($keyword=null, $start=0, $length=9999)
    {
        $where = array();
        if ($keyword) $where['_complex'] = array(
            '_logic'    => 'or',
            'a.title'   => array('like', '%'.$keyword.'%'),
            'a.content' => array('like', '%'.$keyword.'%'),
        );

        $total = M('lvword')->alias('a')->where($where)->count();
        $result = M('lvword')->alias('a')->field('a.*, b.account, b.username, b.department, b.position')->join(' left join __USER__ b on a.userid=b.userid ')->where($where)->order('a.createtime desc')->select();

        return array('total'=>$total, 'data'=>is_array($result)?$result:array());
    }
}