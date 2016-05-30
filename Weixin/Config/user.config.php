<?php
/**
 * 用户配置文件
 * 2014-12-03
 * imbzd
 */
return array(
    //课程分类
    'course_class' => array(
        // 0 => array('id'=>'', 'name'=>'全部'),
        1 => array('id'=>1, 'name'=>'学党章党规(上)', 'weight'=>0.25),
        2 => array('id'=>2, 'name'=>'学党章党规(下)', 'weight'=>0.25),
        3 => array('id'=>3, 'name'=>'学系列讲话', 'weight'=>0.25),
    ),
    //课程学习状态
    'user_course_status' => array(
        0 => array('id'=>0, 'name'=>'开始学习'),
        1 => array('id'=>1, 'name'=>'完成学习'),
        2 => array('id'=>2, 'name'=>'完成测评'),
    ),
    //作业分类
    'work_class' => array(
        // 0 => array('id'=>'', 'name'=>'全部作业'),
        1 => array('id'=>1, 'name'=>'党建作业'),
        2 => array('id'=>2, 'name'=>'党史作业'),
    ),
    //作业类型
    'work_type' => array(
        1 => array('id'=>1, 'name'=>'课程作业', 'remark'=>'*党员需要完成某课程的学习', 'checked'=>true),
        2 => array('id'=>2, 'name'=>'报告作业', 'remark'=>'*党员需要上传学习报告', 'checked'=>false),
    ),
    //作业权重
    'work_weight' => 0.25,
);