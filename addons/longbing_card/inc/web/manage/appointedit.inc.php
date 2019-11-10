<?php  global $_GPC;
global $_W;
define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
$uniacid = $_W["uniacid"];
$module_name = $_W["current_module"]["name"];
$id = $_GPC["id"];
if( !$id ) 
{
	echo "请求参数错误";
	exit();
}
$classify_list = pdo_getall("lb_appoint_classify", array( "uniacid" => $uniacid, "status" => 1 ), array( ), "", array( "top desc" ));
$time_start_arr = array( 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23 );
$time_range_arr = array( array( "value" => 30, "title" => "半小时" ), array( "value" => 60, "title" => "一小时" ), array( "value" => 120, "title" => "两小时" ) );
$time_limit_arr = array( array( "value" => 0, "title" => "不限制" ) );
for( $i = 1; $i < 101; $i++ ) 
{
	$tmp = array( "value" => $i, "title" => $i );
	array_push($time_limit_arr, $tmp);
}
$week_arr = array( array( "value" => 1, "title" => "星期一" ), array( "value" => 2, "title" => "星期二" ), array( "value" => 3, "title" => "星期三" ), array( "value" => 4, "title" => "星期四" ), array( "value" => 5, "title" => "星期五" ), array( "value" => 6, "title" => "星期六" ), array( "value" => 7, "title" => "星期天" ) );
$info = pdo_get("lb_appoint_project", array( "id" => $id ));
$info["carousel"] = explode(",", $info["carousel"]);
$info["days"] = explode(",", $info["days"]);
$is_add = 0;
load()->func("tpl");
include($this->template("manage/appointEdit"));
?>