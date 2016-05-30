/**
 * 通用JS库
 * buzhidao
 */
//显示提示信息
function ShowAlert($type, $msg, $misecond) {
    var $misecond = $misecond ? $misecond : 1500;

    if ($type == "success") {
        $(".alert-success strong").html($msg);
        $(".alert-success").show();

        setTimeout(function () {
            $(".alert-success").fadeOut();
        }, $misecond);
    }
    if ($type == "error") {
        $(".alert-danger strong").html($msg);
        $(".alert-danger").show();
        
        setTimeout(function () {
            $(".alert-danger").fadeOut();
        }, $misecond);
    }
}
