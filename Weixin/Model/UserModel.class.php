<?php
/**
 * 用户数据模型
 * buzhidao
 */
namespace Weixin\Model;

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

    //获取党员信息
    public function getUser($userid=null, $account=null)
    {
        if (!$userid && !$account) return false;

        $where = array(
            'status' => 1,
        );
        if ($userid) $where['userid'] = $userid;
        if ($account) $where['account'] = $account;

        $result = M('user')->where($where)->find();

        return is_array($result) ? $result : array();
    }

    //查询用户信息 根据openid
    public function getUserByOpenID($openid=null)
    {
        if (!$openid) return false;

        $where = array(
            'openid' => $openid,
        );
        $wxuserinfo = M('wx_user')->where($where)->find();

        $userinfo = $wxuserinfo;
        if (is_array($userinfo) && !empty($userinfo)) {
            //获取系统用户信息 通过UserID
            $suserinfo = $this->getUser($userinfo['UserID']);
            if (is_array($suserinfo)&&!empty($suserinfo)) $userinfo = array_merge($userinfo, $suserinfo);
        }

        return is_array($userinfo) ? $userinfo : array();
    }

    //保存微信用户信息
    public function saveWXUserInfo($data=array())
    {
        if (!is_array($data)||empty($data)) return false;

        $result = M('wx_user')->add($data);

        return $result ? true : false;
    }

    //关联微信用户并设置自动登录
    public function linkWXUser($openid=null, $userid=null)
    {
        if (!$openid || !$userid) return false;

        $result = M('wx_user')->where(array('openid'=>$openid))->save(array(
            'userid'    => $userid,
            'autologin' => 1,
        ));

        return $result ? true : false;
    }

    //取消微信用户自动登录
    public function WXUserAutoLoginDisabled($openid=null)
    {
        if (!$openid) return false;

        $result = M('wx_user')->where(array('openid'=>$openid))->save(array(
            'autologin' => 0
        ));

        return $result ? true : false;
    }

    //开启微信用户自动登录
    public function WXUserAutoLoginEnabled($openid=null)
    {
        if (!$openid) return false;

        $result = M('wx_user')->where(array('openid'=>$openid))->save(array(
            'autologin' => 1
        ));

        return $result ? true : false;
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
                'testinglearnnum' => 0,
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
            $testingdonenum = M('testing')->table($subquery.' sub')->join(' __USER_TESTING__ ut on ut.testingid=sub.testingid and ut.userid='.$userid.' and ut.status=2 ')->count();
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

    //获取用户学习课程的学习经历
    public function getUserCourse($userid=null, $courseid=null, $start=0, $length=9999)
    {
        if (!$userid) return false;

        $where = array(
            'a.userid' => $userid,
            'a.status' => array('in', array(1,2)),
        );
        if ($courseid) $where['a.courseid'] = $courseid;

        $total = M('user_course')->alias('a')->join(' __COURSE__ b on a.courseid=b.courseid and b.isshow=1 ')->where($where)->count();
        $result = M('user_course')->alias('a')->join(' __COURSE__ b on a.courseid=b.courseid and b.isshow=1 ')->field('a.*, b.title, b.classid')->where($where)->order('a.completetime desc')->limit($start, $length)->select();

        return array('total'=>$total, 'data'=>is_array($result)?$result:array());
    }

    //获取用户作业
    public function getUserWork($userid=null, $workid=null)
    {
        
    }

    //获取用户上传报告的作业完成情况
    public function getUserWorkFiled($userid=null, $weight=1)
    {
        if (!$userid) return false;

        //上传报告的作业总数
        $worktotalnum = M('work')->where(array('type'=>2))->count();
        //已完成的上传报告的作业总数
        $workdonenum = M('user_work')->alias('a')->join(' __WORK__ b on b.workid=a.workid and b.type=2 ')->where(array('a.status'=>1))->count();
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

    //检查作业完成情况
    public function ckUserCourseWork($userid=null, $courseid=null)
    {
        if (!$userid) return false;

        //查出所有课程作业
        $where = array(
            'a.type' => 1
        );
        if ($courseid) $where['a.courseid'] = is_array($courseid) ? array('in', $courseid) : $courseid;
        $workcourse = M('work')->alias('a')->join(' left join __USER_WORK__ b on a.workid=b.workid and b.userid='.$userid.' and b.status=1 ')->field('a.*, b.userid, b.status, b.completetime')->where($where)->select();

        //筛选未完成的课程作业的课程id
        $courseids = array(0);
        $courseworkids = array();
        if (is_array($workcourse) && !empty($workcourse)) {
            foreach ($workcourse as $workinfo) {
                if ($workinfo['userid']&&$workinfo['status']) continue;

                $courseids[] = $workinfo['courseid'];
                $courseworkids[$workinfo['courseid']][] = $workinfo['workid'];
            }
        }

        //检查课程是否已完成(已学习并完成测评)
        $donecourse = M('user_course')->where(array('userid'=>$userid, 'courseid'=>array('in', $courseids), 'status'=>2))->select();
        if (is_array($donecourse) && !empty($donecourse)) {
            $data = array();
            foreach ($donecourse as $courseinfo) {
                foreach ($courseworkids[$courseinfo['courseid']] as $workid) {
                    $data[] = array(
                        'userid' => $userid,
                        'workid' => $workid,
                        'status' => 1,
                        'completetime' => $courseinfo['completetime'],
                    );
                }
            }

            //新增作业完成记录
            if (!empty($data)) M('user_work')->addAll($data);
        }

        return true;
    }
}