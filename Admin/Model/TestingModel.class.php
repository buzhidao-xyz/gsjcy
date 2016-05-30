<?php
/**
 * 试卷数据模型
 * 2015-12-22
 * buzhidao
 */
namespace Admin\Model;

class TestingModel extends CommonModel
{
    public function __construct()
    {
        parent::__construct();
    }

    //获取试卷信息
    public function getTesting($courseid=null, $testingid=null, $keyword=null, $start=0, $length=9999)
    {
        $wheresub = array();
        if ($courseid) $wheresub['a.courseid'] = $courseid;
        if ($testingid) $wheresub['a.testingid'] = $testingid;
        if ($keyword) $wheresub['b.title'] = array('like', '%'.$keyword.'%');
        $subQuery = M('testing')->alias('a')
                                ->field('a.*, b.title, b.classid, b.viewnum, b.learnnum, b.isshow')
                                ->join('__COURSE__ b on a.courseid=b.courseid')
                                ->where($wheresub)->order('a.createtime desc')->buildSql();

        $total = M('testing')->table($subQuery.' sub')->count();
        $result = M('testing')->table($subQuery.' sub')->limit($start, $length)->select();

        return array('total'=>$total, 'data'=>is_array($result)?$result:array());
    }

    //获取试卷信息 通过ID
    public function getTestingByID($courseid=null, $testingid=null)
    {
        if (!$courseid && !$testingid) return false;

        $testinginfo = $this->getTesting($courseid, $testingid);
        $testinginfo = $testinginfo['total'] ? $testinginfo['data'][0] : array();
        if (!empty($testinginfo)) {
            $testingid = $testinginfo['testingid'];

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

    //保存试卷
    public function testingsave($testingid=null, $data=array())
    {
        if (!is_array($data) || empty($data)) return false;

        $examsdata = $data['exams'];
        unset($data['exams']);

        //开始事务
        M('testing')->startTrans();

        if ($testingid) {
            $result = M('testing')->where(array('testingid'=>$testingid))->save($data);
            !$result ? $testingid = 0 : null;
        } else {
            $testingid = M('testing')->add($data);
        }
        $examsresult = false;
        $courseresult = false;
        if ($testingid) {
            //清除原试卷试题信息
            M('testing_exam')->where(array('testingid'=>$testingid))->delete();

            //保存试题
            foreach ($examsdata as $k=>$d) {
                $examsdata[$k]['testingid'] = $testingid;
            }
            $examsresult = M('testing_exam')->addAll($examsdata);

            //课程istesting字段更新为1
            if (isset($data['courseid'])) {
                $courseresult = D('Course')->saveCourse($data['courseid'], array('istesting'=>1));
            } else {
                $courseresult = true;
            }
        }

        //提交事务
        if ($testingid && $examsresult && $courseresult) {
            M('testing')->commit();
            return $testingid;
        } else {
            M('testing')->rollback();
            return false;
        }
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

    //保存试题
    public function examsave($data=array())
    {
        if (!is_array($data) || empty($data)) return false;

        $examdata = array(
            'type' => $data['type'],
            'title' => $data['title'],
            'answer' => $data['answer'],
            'createtime' => $data['createtime'],
            'updatetime' => $data['updatetime'],
        );

        //开始事务
        M('exam')->startTrans();

        $examid = M('exam')->add($examdata);
        $optionsresult = false;
        if ($examid) {
            $optionsdata = $data['options'];
            foreach ($optionsdata as $k=>$d) {
                $optionsdata[$k]['examid'] = $examid;
                $optionsdata[$k]['updatetime'] = $data['updatetime'];
            }
            $optionsresult = M('exam_option')->addAll($optionsdata);
        }

        //提交事务
        if ($examid && $optionsresult) {
            M('exam')->commit();
            return $examid;
        } else {
            M('exam')->rollback();
            return false;
        }
    }
}