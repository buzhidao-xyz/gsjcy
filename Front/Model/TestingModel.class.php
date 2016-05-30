<?php
/**
 * 试卷数据模型
 * 2015-12-22
 * buzhidao
 */
namespace Front\Model;

class TestingModel extends CommonModel
{
    public function __construct()
    {
        parent::__construct();
    }

    //获取课程信息
    public function getTesting($testingid=null, $courseid=null, $classid=null, $userid=null, $start=0, $length=9999)
    {
        $where = array(
            'a.status' => 1,
            'b.isshow' => 1,
        );
        if ($testingid) $where['a.testingid'] = $testingid;
        if ($courseid) $where['a.courseid'] = $courseid;
        if ($classid) $where['b.classid'] = $classid;
        // if ($userid) $where['c.userid'] = $userid;
        $cwhere = $userid ? ' and c.userid='.$userid.' ' : '';
        $subQuery = M('testing')->alias('a')
                                ->field('a.*, b.title, b.classid, b.viewnum, b.learnnum, c.status as ucstatus')
                                ->join('__COURSE__ b on a.courseid=b.courseid')
                                ->join(' LEFT JOIN __USER_COURSE__ c on a.courseid=c.courseid '.$cwhere)
                                ->where($where)->order('a.createtime desc')->buildSql();

        $total = M('testing')->table($subQuery.' sub')->count();
        if ($userid) {
            $result = M('testing')->table($subQuery.' sub')->field('sub.*, bb.status as utstatus, bb.rightexam, bb.wrongexam, bb.gotscore, bb.completetime')->join(' LEFT JOIN __USER_TESTING__ bb ON sub.testingid=bb.testingid AND bb.userid= '.$userid)
                                  ->order('sub.createtime desc')->limit($start, $length)->select();
        } else {
            $result = M('testing')->table($subQuery.' sub')->field('sub.*, bb.status as utstatus, bb.rightexam, bb.wrongexam, bb.gotscore, bb.completetime')->join(' LEFT JOIN __USER_TESTING__ bb ON sub.testingid=bb.testingid')
                                  ->order('sub.createtime desc')->limit($start, $length)->select();
        }

        //获取复习资料
        if (is_array($result)&&!empty($result)) {
            $courseids = array();
            foreach ($result as $d) {
                $courseids[] = $d['courseid'];
            }
            $coursereview = M('course_review')->where(array('courseid'=>array('in',$courseids)))->select();
            foreach ($result as $k=>$d) {
                $n = 0;
                $reviewinfo = array();
                foreach ($coursereview as $dd) {
                    if ($d['courseid'] == $dd['courseid']) {
                        $n = 1;
                        $reviewinfo = $dd;
                        break;
                    }
                }
                $result[$k]['reviewinfo'] = $reviewinfo;
            }
        }

        return array('total'=>$total, 'data'=>is_array($result)?$result:array());
    }

    //获取试卷信息 通过ID
    public function getTestingByID($courseid=null, $testingid=null, $userid=null)
    {
        if (!$courseid && !$testingid) return false;

        $testinginfo = $this->getTesting($testingid, $courseid, null, $userid);
        $testinginfo = $testinginfo['total'] ? $testinginfo['data'][0] : array();
        if (!empty($testinginfo)) {
            $testingid = $testinginfo['testingid'];
            $courseid = $testinginfo['courseid'];

            //获取复习资料
            // $reviewinfo = M('course_review')->where(array('courseid'=>$courseid))->find();
            // $testinginfo['reviewinfo'] = is_array($reviewinfo)&&!empty($reviewinfo) ? $reviewinfo : array();

            //获取试卷的试题
            $testingexam = M('testing_exam')->where(array('testingid'=>$testingid))->order('sortno asc')->select();
            $examids = array();
            foreach ($testingexam as $exam) {
                $examids[] = $exam['examid'];
            }
            //获取试题信息
            $examdata = $this->getExamByID($examids);

            //遍历试题
            foreach ($testingexam as $exam) {
                foreach ($examdata as $examinfo) {
                    if ($exam['examid'] == $examinfo['examid']) {
                        $testinginfo['exams'][] = array(
                            'examid' => $exam['examid'],
                            'score' => $exam['score'],
                            'sortno' => $exam['sortno'],
                            'type' => $examinfo['type'],
                            'title' => $examinfo['title'],
                            'answer' => $examinfo['answer'],
                            'options' => $examinfo['options'],
                        );
                    }
                }
            }
        }

        return $testinginfo;
    }

    //获取试题信息
    public function getExam($examid=null, $type=null, $title=null, $start=0, $length=9999)
    {
        if (is_string($examid)&&$examid) $examid = array($examid);

        $where = array();
        if (is_array($examid)&&!empty($examid)) $where['examid'] = is_array($examid) ? array('in', $examid) : $examid;
        if ($type) $where['type'] = $type;
        if ($title) $where['title'] = array('like', '%'.$title.'%');

        $total = M('exam')->where($where)->count();
        $result = M('exam')->where($where)->limit($start,$length)->order('createtime desc')->select();
        if (!empty($result)&&is_array($examid)&&!empty($examid)) {
            $examoption = M('exam_option')->where(array('examid'=>array('in', $examid)))->select();
            foreach ($result as $k=>$exam) {
                foreach ($examoption as $option) {
                    if ($option['examid'] == $exam['examid']) {
                        $result[$k]['options'][] = array(
                            'optionid' => $option['optionid'],
                            'name' => $option['name'],
                            'title' => $option['title'],
                        );
                    }
                }
            }
        }

        return array('total'=>$total, 'data'=>is_array($result)?$result:array());
    }

    //获取试题信息 通过examid
    public function getExamByID($examid=null)
    {
        if (!$examid || empty($examid)) return false;

        $result = $this->getExam($examid);

        return $result['total'] ? (is_array($examid)?$result['data']:$result['data'][0]) : array();
    }

    //获取上一试卷、下一试卷
    public function getPrevNextTesting($testingid=null, $classid=null)
    {
        if ($testingid === null || !$classid) return false;

        $where = array(
            'a.status' => 1,
            'a.testingid' => array('LT', $testingid),
            'b.isshow' => 1,
            'b.classid' => $classid,
        );
        $prevtestinginfo = M('testing')->alias('a')->field('a.*, b.title')->join(' __COURSE__ b ON a.courseid=b.courseid ')
                                      ->where($where)->order('a.testingid desc')->limit(0, 1)->find();

        $where = array(
            'a.status' => 1,
            'a.testingid' => array('GT', $testingid),
            'b.isshow' => 1,
            'b.classid' => $classid,
        );
        $nexttestinginfo = M('testing')->alias('a')->field('a.*, b.title')->join(' __COURSE__ b ON a.courseid=b.courseid ')
                                      ->where($where)->order('a.testingid asc')->limit(0, 1)->find();

        return array(
            'prev' => is_array($prevtestinginfo) ? $prevtestinginfo : array(),
            'next' => is_array($nexttestinginfo) ? $nexttestinginfo : array(),
        );
    }
}