<?php
/**
 * 文章数据模型
 * 2015-12-22
 * buzhidao
 */
namespace Front\Model;

class ArticleModel extends CommonModel
{
    public function __construct()
    {
        parent::__construct();
    }

    //获取文章分类
    public function getArcclass($classid=null)
    {
        $where = array();
        if ($classid) $where['classid'] = $classid;

        $result = M('article_class')->where($where)->select();

        return is_array($result) ? $result : array();
    }

    /**
     * 获取文章
     * @param  [type]  $arcid   [description]
     * @param  [type]  $classid [description]
     * @param  [type]  $keyword [description]
     * @param  integer $start   [description]
     * @param  integer $length  [description]
     * @return [type]           [description]
     */
    public function getArc($arcid=null, $classid=null, $keyword=null, $start=0, $length=9999)
    {
        $where = array(
            'status' => 1,
        );
        if ($arcid) $where['arcid'] = $arcid;
        if ($classid) $where['classid'] = $classid;
        if ($keyword) $where['_complex'] = array(
            '_logic'  => 'or',
            'title'   => array('like', '%'.$keyword.'%'),
            'keyword' => array('like', '%'.$keyword.'%'),
        );

        $count = M('article')->where($where)->count();
        $result = M('article')->where($where)->order('createtime desc')->limit($start, $length)->select();

        return array('total'=>$count, 'data'=>is_array($result)?$result:array());
    }

    //获取文章详情
    public function getArcByID($arcid=null)
    {
        if (!$arcid) return false;

        $arcinfo = $this->getArc($arcid);

        return $arcinfo['total'] ? $arcinfo['data'][0] : array();
    }

    //保存文章
    public function saveArc($arcid=null, $data=array())
    {
        if (!is_array($data) || empty($data)) return false;

        if ($arcid) {
            $result = M('article')->where(array('arcid'=>$arcid))->save($data);
            $result = $result ? $arcid : false;
        } else {
            $arcid = M('article')->add($data);
        }

        return $arcid ? $arcid : false;
    }

    //获取上一文章、下一文章
    public function getPrevNextArticle($arcid=null, $classid=null)
    {
        if ($arcid === null || !$classid) return false;

        $where = array(
            'a.status'  => 1,
            'a.arcid'   => array('LT', $arcid),
            'a.classid' => $classid,
        );
        $prevarcinfo = M('article')->alias('a')->field('a.*')->where($where)->order('a.arcid desc')->limit(0, 1)->find();

        $where = array(
            'a.status'  => 1,
            'a.arcid'   => array('GT', $arcid),
            'a.classid' => $classid,
        );
        $nextarcinfo = M('article')->alias('a')->field('a.*')->where($where)->order('a.arcid asc')->limit(0, 1)->find();

        return array(
            'prev' => is_array($prevarcinfo) ? $prevarcinfo : array(),
            'next' => is_array($nextarcinfo) ? $nextarcinfo : array(),
        );
    }
}