/**
 * 课程视频播放类
 * buzhidao
 * 2015-12-12
 */
/**
 * [CourseVideoClass description]
 * @param int courseid 课程id
 * @param string containerid 视频容器ID
 */
var courseid = $("#coursevideo").attr('courseid');
var videoimg = $("#coursevideo").attr('videoimg');
var videofile = $("#coursevideo").attr('videofile');
var coursesign = $("#coursevideo").attr('coursesign');

//ckplayer加载完成-JS函数
function CourseVideoLoadedHandler(){
    if(CKobject.getObjectById('ckplayer_coursevideo').getType()){
        //HTML5播放
        // 播放结束动作-JS
        CKobject.getObjectById('ckplayer_coursevideo').addListener('ended',CourseVideoEndedHandler);
    }else{
        //Flash播放
        // 播放结束动作-JS
        CKobject.getObjectById('ckplayer_coursevideo').addListener('ended','CourseVideoEndedHandler');
    }
}

//播放结束 执行JS函数
function CourseVideoEndedHandler(){
    //AJAX请求服务器 通知该课程已学习完成
    var url = JS_APP+'?s=Course/scomplete&courseid='+courseid+'&coursesign='+coursesign;
    $.post(url, {}, function (data){
        //开启马上去测评按钮
        var CourseExamUrl = JS_APP+'?s=Testing/exam&courseid='+courseid;
        $("a#ExamBtn").attr('href', CourseExamUrl).removeClass('disabled');
    }, 'json');
}

//初始化CKobject对象
var flashvars={
    f:videofile,
    c:1,
    x:'ckplayer.xml',
    p:0,
    i:videoimg,
    wh:'16:9',
    e:6,
    b:0,
    loaded:'CourseVideoLoadedHandler'
};
var params={
    bgcolor:'#FFF',
    allowFullScreen:true,
    allowScriptAccess:'always',
    wmode:'transparent'
};
CKobject.embedSWF(PUBLIC_SERVER+'plugins/ckplayer/ckplayer.swf','coursevideo','ckplayer_coursevideo','800','450',flashvars,params);