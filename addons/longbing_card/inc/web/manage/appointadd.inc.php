<?php  global $_GPC;
global $_W;
define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
$uniacid = $_W["uniacid"];
$module_name = $_W["current_module"]["name"];
if( $_GPC["action"] == "formatRange" ) 
{
	$time_start = intval($_GPC["time_start"]);
	$time_range = intval($_GPC["time_range"]);
	$id = intval($_GPC["id"]);
	$time_start = ($time_start ? $time_start : 0);
	$time_range = ($time_range ? $time_range : 30);
	$time_range = $time_range * 60;
	$today_time = strtotime(date("Y-m-d"));
	$tomorrow_time = strtotime(date("Y-m-d", strtotime("+1 day")));
	if( strlen($time_start) == 1 ) 
	{
		$today_time = date("Y-m-d", $today_time) . " 0" . $time_start . ":00:00";
	}
	else 
	{
		$today_time = date("Y-m-d", $today_time) . " " . $time_start . ":00:00";
	}
	$today_time = strtotime($today_time);
	$tmp_time = $today_time;
	$time_array = array( );
	for( $i = 0; $i < 100; $i++ ) 
	{
		if( $tomorrow_time <= $tmp_time ) 
		{
			break;
		}
		$tmp = date("H:i", $tmp_time) . "-" . date("H:i", $tmp_time + $time_range);
		$tmp_time += $time_range;
		array_push($time_array, $tmp);
	}
	$data = array( "today_time" => $today_time, "tomorrow_time" => $tomorrow_time, "time_array" => $time_array );
	if( $id ) 
	{
		$info = pdo_get("lb_appoint_project", array( "id" => $id ));
		$times_start = explode(",", trim($info["times_start"], ","));
		$times_end = explode(",", trim($info["times_end"], ","));
		$tmp = array( );
		for( $i = 0; $i < count($times_start);
		$i++ ) 
		{
			array_push($tmp, $times_start[$i] . "-" . $times_end[$i]);
		}
		$data["time_selected"] = $tmp;
	}
	echo json_encode($data);
	exit();
}
if( $_GPC["action"] == "edit" ) 
{
	$uniacid = $_W["uniacid"];
	$id = (isset($_GPC["id"]) ? $_GPC["id"] : 0);
	$post = $_GPC;
	$data = array( "title" => $post["title"], "cover" => (isset($post["cover"]) ? $post["cover"] : ""), "carousel" => (isset($post["carousel"]) ? $post["carousel"] : array( )), "content" => ($post["content"] ? $post["content"] : ""), "desc" => ($post["desc"] ? $post["desc"] : ""), "days" => (isset($post["date"]) ? $post["date"] : ""), "time_start" => ($post["start_time"] ? $post["start_time"] : ""), "time_range" => ($post["project_range"] ? $post["project_range"] : ""), "times_start" => (isset($post["time_ranges"]) ? $post["time_ranges"] : array( )), "times_end" => (isset($post["time_ranges"]) ? $post["time_ranges"] : array( )), "limit" => ($post["limit"] ? $post["limit"] : 0), "appoint_price" => ($post["appoint_price"] ? $post["appoint_price"] : ""), "classify_id" => ($post["classify_id"] ? $post["classify_id"] : ""), "top" => ($post["top"] ? $post["top"] : 0), "status" => 1, "uniacid" => $uniacid );
	if( !is_array($data["times_start"]) || !is_array($data["days"]) || empty($data["times_start"]) || empty($data["days"]) ) 
	{
		return $this->returnFail(array( ), "操作失败, 请确认选择时间段以及日期");
	}
	$data = formatRangeData($data);
	if( is_array($data["carousel"]) && is_array($data["carousel"]) ) 
	{
		$data["carousel"] = implode(",", $data["carousel"]);
	}
	$data["days"] = implode(",", $data["days"]);
	$time = time();
	$data["update_time"] = $time;
	$result = false;
	if( $id ) 
	{
		$result = pdo_update("lb_appoint_project", $data, array( "id" => $id ));
	}
	else 
	{
		$data["create_time"] = $time;
		$result = pdo_insert("lb_appoint_project", $data, array( "id" => $id ));
	}
	if( $result === false ) 
	{
		message("操作失败", "", "error");
	}
	message($id, $this->createWebUrl("manage/appointClassify"), "success");
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
$is_add = 1;
load()->func("tpl");
include($this->template("manage/appointEdit"));
function formatRangeData($data) 
{
	$range = $data["times_start"];
	foreach( $range as $index => $item ) 
	{
		$tmp = explode("-", $item);
		$range[$index] = $tmp;
	}
	$data["times_start"] = "";
	$data["times_end"] = "";
	foreach( $range as $index => $item ) 
	{
		$data["times_start"] .= $item[0] . ",";
		$data["times_end"] .= $item[1] . ",";
	}
	$data["times_start"] = trim($data["times_start"], ",");
	$data["times_end"] = trim($data["times_end"], ",");
	return $data;
}
?>