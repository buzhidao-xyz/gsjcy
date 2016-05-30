<?php
/**
 * 会员数据模型
 * 2015-12-22
 * buzhidao
 */
namespace Front\Model;

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

    //获取党员总数
    public function getUsernum()
    {
        $where = array(
            'status' => array('in',array(0,1,2)),
        );
        $subQuery = M('user_course')->field('userid')->where($where)->group('userid')->buildSql();
        $result = M('user_course')->table($subQuery.' a')->join(' __USER__ b on a.userid=b.userid and b.status=1 ')->count();

        return $result>0 ? $result : 0;
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

    //获取用户作业完成情况
    public function getUserWorkDone($userid=null, $workclass=array())
    {
        if (!$userid || !is_array($workclass) || empty($workclass)) return false;

        //作业完成情况
        $userworkinfo = array(
            'listi' => array(),
            'total' => array(
                'worktotalnum' => 0,
                'workdonenum'  => 0,
                'worknonenum'  => 0,
                'percent'      => 0,
            ),
        );

        //作业完成情况 按分类统计
        foreach ($workclass as $classinfo) {
            //该分类总作业数
            $worktotalnum = M('work')->where(array('classid'=>$classinfo['id']))->count();
            //该分类已完成作业数
            $workdonenum = M('work')->alias('a')->join(' __USER_WORK__ b on a.workid=b.workid and b.userid='.$userid.' and b.status=1 ')->where(array('a.classid'=>$classinfo['id']))->count();
            //未完成作业数
            $worknonenum = $worktotalnum-$workdonenum;
            $workpercent = $worktotalnum>0 ? floor($workdonenum/$worktotalnum*100) : 0;

            $userworkinfo['listi'][$classinfo['id']] = array(
                'worktotalnum' => $worktotalnum,
                'workdonenum'  => $workdonenum,
                'worknonenum'  => $worknonenum,
                'workpercent'  => $workpercent,
            );

            //合计 作业数
            $userworkinfo['total']['worktotalnum'] += $worktotalnum;
            $userworkinfo['total']['workdonenum'] += $workdonenum;
            $userworkinfo['total']['worknonenum'] += $worknonenum;
        }

        //合计 百分比
        $userworkinfo['total']['workpercent'] = $userworkinfo['total']['worktotalnum']>0 ? floor($userworkinfo['total']['workdonenum']/$userworkinfo['total']['worktotalnum']*100) : 0;

        return $userworkinfo;
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

    //获取党员学习得分排名 前十名
    public function getUserLearninglist()
    {
        //测评得分
        $userscores = M('user')->alias('u')->field('u.userid, u.username, SUM(ut.gotscore) as gotscore')
                               ->join(' left join __USER_TESTING__ ut on ut.userid=u.userid and ut.status=1 ')
                               ->join(' left join __TESTING__ t on t.testingid=ut.testingid and t.status=1 ')
                               ->where(array('u.status'=>1))
                               ->group('u.userid')
                               ->order('gotscore desc, u.userid asc')
                               ->select();
        
        //作业得分
        $userworks = M('user')->alias('u')->field('u.userid, uw.status')
                              ->join(' __USER_WORK__ uw on uw.userid=u.userid and uw.status=1 ')
                              ->join(' __WORK__ w on w.workid=uw.workid and w.type=2 ')
                              ->where(array('u.status'=>1))
                              ->group('u.userid')
                              ->select();

        //测评总数
        $testingtotalnum = M('testing')->where(array('status'=>1))->count();

        //计算平均分
        $userlearning = array();
        if (is_array($userscores)&&!empty($userscores)) {
            foreach ($userscores as $d) {
                $cache = array(
                    'userid'    => $d['userid'],
                    'username'  => $d['username'],
                    'gotscore'  => $d['gotscore']>0 ? (float)$d['gotscore'] : 0,
                    'workscore' => 0,
                );
                foreach ($userworks as $dd) {
                    if ($d['userid'] == $dd['userid']) {
                        $cache['workscore'] = 100;
                    }
                }
                $cache['avgscore'] = floor(($cache['gotscore']/$testingtotalnum)*0.75 + $cache['workscore']*0.25);

                $userlearning[] = $cache;
            }
        }

        //冒泡排序 大->小
        $n = count($userlearning);
        for ($i=0; $i<$n-1; $i++) {
            for ($j=$i+1; $j<$n; $j++) {
                $a = $userlearning[$i];
                $b = $userlearning[$j];

                if ($userlearning[$i]['avgscore']<$userlearning[$j]['avgscore']) {
                    $userlearning[$i] = $b;
                    $userlearning[$j] = $a;
                }
            }
        }

        //获取前十名
        $userlearning = array_slice($userlearning, 0, 10);
        $userids = array();
        $i = 1;
        foreach ($userlearning as $k=>$d) {
            $userids[] = $d['userid'];
            $userlearning[$k]['no'] = $i;
            $i++;
        }

        //获取课程和测评数
        $usercourses = M('user_course')->alias('a')->field('a.userid,COUNT(a.courseid) as coursenum')->join(' __COURSE__ b on a.courseid=b.courseid and b.isshow=1 ')->where(array('a.userid'=>array('in', $userids), 'a.status'=>array('in', array(1,2))))->group('a.userid')->select();
        $usertestings = M('user_testing')->alias('a')->field('a.userid,COUNT(a.testingid) as testingnum')->join(' __TESTING__ b on a.testingid=b.testingid and b.status=1 ')->where(array('a.userid'=>array('in', $userids), 'a.status'=>1))->group('a.userid')->select();
        foreach ($userlearning as $k=>$d) {
            $userlearning[$k]['coursenum'] = 0;
            $userlearning[$k]['testingnum'] = 0;
            foreach ($usercourses as $dd) {
                if ($d['userid'] == $dd['userid']) $userlearning[$k]['coursenum'] = $dd['coursenum'];
            }
            foreach ($usertestings as $dd) {
                if ($d['userid'] == $dd['userid']) $userlearning[$k]['testingnum'] = $dd['testingnum'];
            }
        }

        // dump($userlearning);exit;
        return $userlearning;
    }

    //各支部学习进度统计
    public function zhibuLearnStats()
    {
        //查询支部
        $zhibuList = M('dangzhibu')->order('zhibuid asc')->select();
        $zhibuIds = array();
        $zhibuLearnStats = array();
        foreach ($zhibuList as $d) {
            $zhibuIds[] = $d['zhibuid'];
            $zhibuLearnStats[$d['zhibuid']] = array(
                'zhibuid' => $d['zhibuid'],
                'zhibuname' => $d['zhibuname'],
            );
        }

        //查询支部人数
        $zhibuUser = M('user')->field('dangzhibu, count(userid) as usernum')->where(array('dangzhibu'=>array('in', $zhibuIds), 'status'=>1))->group('dangzhibu')->select();

        //查询支部学习人数
        $zhibuUserSubSqlt = M('user')->alias('a')
                          ->field('a.dangzhibu, a.userid')
                          ->join(' __USER_COURSE__ b on a.userid=b.userid and b.status in (0,1,2) ')
                          ->where(array('a.dangzhibu'=>array('in', $zhibuIds)))
                          ->group('a.dangzhibu, a.userid')
                          ->buildSql();
        $zhibuUserLearned = M('user')->field('dangzhibu, count(sub.userid) as usernumlearned')->table($zhibuUserSubSqlt.' sub')->group('sub.dangzhibu')->select();
        //查询支部学习通过人数
        $zhibuUserSubSqlt = M('user')->alias('a')
                          ->field('a.dangzhibu, a.userid')
                          ->join(' __USER_COURSE__ b on a.userid=b.userid and b.status in (2) ')
                          ->where(array('a.dangzhibu'=>array('in', $zhibuIds)))
                          ->group('a.dangzhibu, a.userid')
                          ->buildSql();
        $zhibuUserLearnOK = M('user')->field('dangzhibu, count(sub.userid) as usernumlearnok')->table($zhibuUserSubSqlt.' sub')->group('sub.dangzhibu')->select();

        //统计数据
        foreach ($zhibuLearnStats as $zhibuid=>$zhibu) {
            //党员总数
            $zhibuLearnStats[$zhibuid]['usernum'] = 0;
            foreach ($zhibuUser as $d) {
                if ($d['dangzhibu'] == $zhibuid) {
                    $zhibuLearnStats[$zhibuid]['usernum'] = (int)$d['usernum'];
                }
            }
            //学习人数
            $zhibuLearnStats[$zhibuid]['usernumlearned'] = 0;
            foreach ($zhibuUserLearned as $d) {
                if ($d['dangzhibu'] == $zhibuid) {
                    $zhibuLearnStats[$zhibuid]['usernumlearned'] = (int)$d['usernumlearned'];
                }
            }
            //通过人数
            $zhibuLearnStats[$zhibuid]['usernumlearnok'] = 0;
            foreach ($zhibuUserLearnOK as $d) {
                if ($d['dangzhibu'] == $zhibuid) {
                    $zhibuLearnStats[$zhibuid]['usernumlearnok'] = (int)$d['usernumlearnok'];
                }
            }

            //学习率
            $zhibuLearnStats[$zhibuid]['learnpercent'] = $zhibuLearnStats[$zhibuid]['usernum']>0 ? $zhibuLearnStats[$zhibuid]['usernumlearned']/$zhibuLearnStats[$zhibuid]['usernum']*100 : 0;
            $zhibuLearnStats[$zhibuid]['learnpercent'] = $zhibuLearnStats[$zhibuid]['learnpercent']>0 ? number_format($zhibuLearnStats[$zhibuid]['learnpercent'], 1) : 0;
            $zhibuLearnStats[$zhibuid]['learnpercent'] = $zhibuLearnStats[$zhibuid]['learnpercent'].'%';
            //通过率
            $zhibuLearnStats[$zhibuid]['passpercent'] = $zhibuLearnStats[$zhibuid]['usernum']>0 ? $zhibuLearnStats[$zhibuid]['usernumlearnok']/$zhibuLearnStats[$zhibuid]['usernum']*100 : 0;
            $zhibuLearnStats[$zhibuid]['passpercent'] = $zhibuLearnStats[$zhibuid]['passpercent']>0 ? number_format($zhibuLearnStats[$zhibuid]['passpercent'], 1) : 0;
            $zhibuLearnStats[$zhibuid]['passpercent'] = $zhibuLearnStats[$zhibuid]['passpercent'].'%';
        }
        //合计数据
        $usernum = 0;
        $usernumlearned = 0;
        $usernumlearnok = 0;
        foreach ($zhibuLearnStats as $zhibuid=>$zhibu) {
            $usernum += $zhibu['usernum'];
            $usernumlearned += $zhibu['usernumlearned'];
            $usernumlearnok += $zhibu['usernumlearnok'];
        }
        //学习率
        $learnpercent = $usernum>0 ? $usernumlearned/$usernum*100 : 0;
        $learnpercent = $learnpercent>0 ? number_format($learnpercent, 1) : 0;
        $learnpercent = $learnpercent.'%';
        //通过率
        $passpercent = $usernum>0 ? $usernumlearnok/$usernum*100 : 0;
        $passpercent = $passpercent>0 ? number_format($passpercent, 1) : 0;
        $passpercent = $passpercent.'%';
        $zhibuLearnStats['total'] = array(
            'zhibuid'        => 0,
            'zhibuname'      => '党委合计',
            'usernum'        => $usernum,
            'usernumlearned' => $usernumlearned,
            'usernumlearnok' => $usernumlearnok,
            'learnpercent'   => $learnpercent,
            'passpercent'    => $passpercent,
        );

        return $zhibuLearnStats;
    }
}
