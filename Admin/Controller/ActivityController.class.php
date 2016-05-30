<?php
/**
 * 活动模块控制器
 * buzhidao
 * 2015-12-27
 */
namespace Admin\Controller;

use Org\Util\Filter;
use Org\Util\String;

class ActivityController extends CommonController
{
    public function __construct()
    {
        parent::__construct();

        $this->_page_location = __APP__.'?s=Activity/index';

        $this->assign("sidebar_active", array("Activity","index"));
    }

    //获取activityid
    private function _getActivityid()
    {
        $activityid = mRequest('activityid');

        return $activityid;
    }

    public function index()
    {
        $this->display();
    }

    //发布活动
    public function newactivity()
    {
        $this->display();
    }
}