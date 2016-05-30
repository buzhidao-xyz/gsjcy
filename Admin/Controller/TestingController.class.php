<?php
/**
 * 试卷模块控制器
 * buzhidao
 * 2015-12-27
 */
namespace Admin\Controller;

use Any\Upload;

class TestingController extends CommonController
{
    //试题类型
    public $_exam_type = array(
        1 => array('id'=>1, 'name'=>'单选', 'class'=>'single'),
        2 => array('id'=>2, 'name'=>'多选', 'class'=>'multiple'),
    );
    
    public function __construct()
    {
        parent::__construct();

        $this->assign("sidebar_active", array("Testing","index"));

        $this->_page_location = __APP__.'?s=Testing/index';

        $this->assign("examtype", $this->_exam_type);
    }

    //获取课程id
    private function _getCourseid()
    {
        $courseid = mRequest('courseid');
        $this->assign('courseid', $courseid);

        return $courseid;
    }

    //获取试卷id
    private function _getTestingid()
    {
        $testingid = mRequest('testingid');
        $this->assign('testingid', $testingid);

        return $testingid;
    }

    //获取试卷状态
    private function _getStatus()
    {
        $status = mRequest('status');

        return $status;
    }

    //获取试题信息
    private function _getExams()
    {
        $examids = mRequest('examids', false);
        if (!is_array($examids) || empty($examids)) $this->ajaxReturn(1, '请添加试题！');
        $scores = mRequest('scores', false);
        if (!is_array($scores) || empty($scores)) $this->ajaxReturn(1, '请分配试题分数！');
        $sortnos = mRequest('sortnos', false);

        $exams = array();
        foreach ($examids as $examid) {
            $exams[] = array(
                'examid' => $examid,
                'score' => $scores[$examid],
                'sortno' => $sortnos[$examid],
            );
        }

        return $exams;
    }

    //获取搜索关键字
    private function _getKeywords()
    {
        $keywords = mRequest('keywords');
        $this->assign('keywords', $keywords);

        return $keywords;
    }

    //试卷管理
    public function index()
    {
        $keywords = $this->_getKeywords();

        list($start, $length) = $this->_mkPage();
        $data = D('Testing')->getTesting(null, null, $keywords, $start, $length);
        $total = $data['total'];
        $datalist = $data['data'];

        $this->assign('datalist', $datalist);

        $param = array(
            'keywords'   => $keywords,
        );
        $this->assign('param', $param);
        //解析分页数据
        $this->_mkPagination($total, $param);

        $this->display();
    }

    //添加试卷
    public function newtesting()
    {
        $courseid = $this->_getCourseid();

        //获取课程列表
        $courselist = D('Course')->getCourse(null, null, 0);
        $courselist = $courselist['data'];
        $this->assign('courselist', $courselist);

        $this->display();
    }

    //查看试卷详情 - 编辑
    public function profile()
    {
        $courseid = $this->_getCourseid();
        $testingid = $this->_getTestingid();

        //获取试卷信息
        $testinginfo = D('Testing')->getTestingByID($courseid, $testingid);
        if (!is_array($testinginfo) || empty($testinginfo)) {
            header('Location:'.__APP__.'?s=Testing/newtesting&courseid='.$courseid);
            exit;
        }

        $this->assign('testinginfo', $testinginfo);
        $this->display();
    }

    //保存试卷信息
    public function testingsave()
    {
        $testingid = $this->_getTestingid();
        if (!$testingid) {
            $courseid = $this->_getCourseid();
            if (!$courseid) $this->ajaxReturn(1, '请选择课程！');

            $status = $this->_getStatus();
            !$status ? $status = 0 : null;
        }

        $exams = $this->_getExams();

        if ($testingid) {
            $data = array(
                'examnum' => count($exams),
                'updatetime' => TIMESTAMP,
                'exams' => $exams,
            );
            $testingid = D('Testing')->testingsave($testingid, $data);
        } else {
            $data = array(
                'courseid' => $courseid,
                'examnum' => count($exams),
                'totalscore' => 100,
                'passscore' => 60,
                'donenum' => 0,
                'status' => (int)$status,
                'createtime' => TIMESTAMP,
                'updatetime' => TIMESTAMP,
                'exams' => $exams,
            );
            $testingid = D('Testing')->testingsave(null, $data);
        }

        if ($testingid) {
            $this->ajaxReturn(0, '试卷发布成功！');
        } else {
            $this->ajaxReturn(1, '试卷发布失败！');
        }
    }

    //启用、禁用试卷
    public function enable()
    {
        $testingid = $this->_getTestingid();
        if (!$testingid) $this->ajaxReturn(1, '未知试卷信息！');

        $status = $this->_getStatus();
        if (!in_array($status, array(0,1))) $this->ajaxReturn(1, '数据错误！');

        $data = array(
            'status' => $status,
            'updatetime' => TIMESTAMP,
        );
        $result = M('testing')->where(array('testingid'=>$testingid))->save($data);
        if ($result) {
            $this->ajaxReturn(0, '成功！');
        } else {
            $this->ajaxReturn(1, '失败！');
        }
    }

    //试题管理
    public function exam()
    {
        $this->display();
    }

    //获取试题box
    public function getexambox()
    {
        $html = $this->fetch('Testing/exambox');

        $this->ajaxReturn(0, null, array(
            'html' => $html
        ));
    }

    //保存试题
    public function examsave()
    {
        //类型
        $examtype = mRequest('examtype');
        if (!$examtype) $this->ajaxReturn(1, '请选择试题类型！');
        //题目
        $examtitle = mRequest('examtitle');
        if (!$examtitle) $this->ajaxReturn(1, '请填写试题题目！');
        //A、B、C、D选项
        $optiontitlea = mRequest('optiontitlea');
        if (!$optiontitlea) $this->ajaxReturn(1, '请填写A. 选项内容！');
        $optiontitleb = mRequest('optiontitleb');
        if (!$optiontitleb) $this->ajaxReturn(1, '请填写B. 选项内容！');
        $optiontitlec = mRequest('optiontitlec');
        if (!$optiontitlec) $this->ajaxReturn(1, '请填写C. 选项内容！');
        $optiontitled = mRequest('optiontitled');
        if (!$optiontitled) $this->ajaxReturn(1, '请填写D. 选项内容！');
        //E、F选项
        $optiontitlee = mRequest('optiontitlee');
        $optiontitlef = mRequest('optiontitlef');
        //答案
        $examanswer = mRequest('examanswer');
        if (!$examanswer) $this->ajaxReturn(1, '请填写试题答案！');

        //试题信息
        $data = array(
            'type' => $examtype,
            'title' => $examtitle,
            'answer' => $examanswer,
            'options' => array(
                array(
                    'name' => 'A',
                    'title' => $optiontitlea,
                ),
                array(
                    'name' => 'B',
                    'title' => $optiontitleb,
                ),
                array(
                    'name' => 'C',
                    'title' => $optiontitlec,
                ),
                array(
                    'name' => 'D',
                    'title' => $optiontitled,
                ),
            ),
            'createtime' => TIMESTAMP,
            'updatetime' => TIMESTAMP,
        );
        if ($optiontitlee) $data['options'][] = array('name'=>'E', 'title'=>$optiontitlee);
        if ($optiontitlef) $data['options'][] = array('name'=>'F', 'title'=>$optiontitlef);

        $examid = D('Testing')->examsave($data);
        if ($examid) {
            $data['examid'] = $examid;
            $this->assign('examinfo', $data);

            $examhtml = $this->fetch('Testing/examitem');
            $this->ajaxReturn(0, '试题保存成功！', array(
                'examhtml' => $examhtml
            ));
        } else {
            $this->ajaxReturn(0, '试题保存失败！');
        }
    }

    //excel试题上传
    public function excelexam()
    {
        //初始化上传类
        $Upload = new Upload();
        $Upload->maxSize  = $this->_upfilesize['attach']['size'];
        $Upload->exts     = $this->_upfilesize['attach']['exts'];
        $Upload->rootPath = UPLOAD_PATH;
        $Upload->savePath = 'file/excelexam/';
        $Upload->saveName = array('uniqid', array('', true));
        $Upload->autoSub  = true;
        $Upload->subName  = array('date', 'Ym');

        //上传
        $error = null;
        $msg = '上传成功！';
        $data = array();
        $info = $Upload->upload();
        if (!$info) {
            $error = 1;
            $msg = $Upload->getError();
        } else {
            $fileinfo = array_shift($info);
            $excelexamfile = UPLOAD_PATH.$fileinfo['savepath'].$fileinfo['savename'];

            //试题信息html
            $examhtml = '';

            //读取excel试题文件
            require_once VENDOR_PATH.'PHPExcel/PHPExcel/IOFactory.php';
            $objPHPExcel = \PHPExcel_IOFactory::load($excelexamfile);
            $cSheet = $objPHPExcel->getSheet(0);
            $maxRow = $cSheet->getHighestRow(); // 取得总行数
            $maxColumn = $cSheet->getHighestColumn(); // 取得总列数
            $row = 2;
            $col = 'A';
            for ($row=2; $row<=$maxRow; $row++) {
                $examitem = array();
                for ($col='A'; $col<=$maxColumn; $col++) {
                    $val = $cSheet->getCellByColumnAndRow(ord($col)-65, $row)->getValue();/**ord()将字符转为十进制数*/

                    //题目
                    if ($col == 'A') {
                        $examitem['title'] = $val;
                    }
                    //答案
                    if ($col == 'B') {
                        $examitem['answer'] = $val;
                        $examitem['type'] = strlen($val)>1 ? 2 : 1;
                    }
                    //选项A
                    if ($col == 'C') {
                        $c = 1;
                        $examitem['options'][] = array(
                            'name' => 'A',
                            'title' => str_replace('A.', '', $val, $c),
                        );
                    }
                    //选项B
                    if ($col == 'D') {
                        $c = 1;
                        $examitem['options'][] = array(
                            'name' => 'B',
                            'title' => str_replace('B.', '', $val, $c),
                        );
                    }
                    //选项C
                    if ($col == 'E') {
                        $c = 1;
                        $examitem['options'][] = array(
                            'name' => 'C',
                            'title' => str_replace('C.', '', $val, $c),
                        );
                    }
                    //选项D
                    if ($col == 'F') {
                        $c = 1;
                        $examitem['options'][] = array(
                            'name' => 'D',
                            'title' => str_replace('D.', '', $val, $c),
                        );
                    }
                    //分数
                    // if ($col == 'G') {
                    //     $examitem['score'] = $val;
                    // }
                }
                $examitem['createtime'] = TIMESTAMP;
                $examitem['updatetime'] = TIMESTAMP;

                $examid = D('Testing')->examsave($examitem);
                if ($examid) {
                    $examitem['examid'] = $examid;
                    $this->assign('examinfo', $examitem);

                    $examhtml .= $this->fetch('Testing/examitem');
                }
            }

            $data = array(
                'examhtml' => $examhtml,
            );
        }

        $this->ajaxReturn($error, $msg, $data);
    }
}