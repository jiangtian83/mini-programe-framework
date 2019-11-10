<?php  define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
global $_GPC;
global $_W;
$uniacid = $_W["uniacid"];
$user_id = $_GPC["user_id"];
$id = $_GPC["id"];
$name = $_GPC["name"];
$phone = $_GPC["phone"];
$date = $_GPC["date"];
$range = $_GPC["range"];
$remark = $_GPC["remark"];
$to_uid = $_GPC["to_uid"];
if( !$user_id || !$id || !$name || !$phone || !$date || !$range || !$to_uid ) 
{
	return $this->result(-1, "请传入参数", array( ));
}
$range = explode("-", $range);
if( !is_array($range) || count($range) != 2 ) 
{
	return $this->result(-1, "请传入参数", array( ));
}
$date_start = $date . $range[0] . ":00";
$date_end = $date . $range[1] . ":00";
$date_start = strtotime($date_start);
$date_end = strtotime($date_end);
if( $date_start < time() ) 
{
	return $this->result(-2, "不能选择已经过去的时间段", array( ));
}
if( !$date_start || !$date_end ) 
{
	return $this->result(-2, "传入日期错误", array( ));
}
$project = pdo_getall("lb_appoint_project", array( "id" => $id ));
if( !$project ) 
{
	return $this->result(-2, "未找到该服务项目, 请重新选择", array( ));
}
$check = false;
if( $project["limit"] != 0 ) 
{
	$check = pdo_getall("lb_appoint_record", array( "start_time" => $date_start, "end_time" => $date_end, "project_id" => $id ));
	$count = count($check);
	if( $project["limit"] <= $count ) 
	{
		$check = true;
	}
}
if( $check ) 
{
	return $this->result(-2, "该时间预约人数已达到限制, 请重新选择", array( ));
}
$check = pdo_getall("lb_appoint_record", array( "start_time" => $date_start, "end_time" => $date_end, "project_id" => $id, "user_id" => $user_id ));
if( $check ) 
{
	return $this->result(-2, "您已经预约过该时间段了, 请重新选择", array( ));
}
$time = time();
$insertData = array( "user_id" => $user_id, "to_uid" => $to_uid, "project_id" => $id, "name" => $name, "phone" => $phone, "start_time" => $date_start, "end_time" => $date_end, "remark" => $remark, "status" => 1, "uniacid" => $uniacid, "create_time" => $time, "update_time" => $time );
$result = pdo_insert("lb_appoint_record", $insertData);
if( $result ) 
{
	return $this->result(0, "预约成功", array( ));
}
return $this->result(-2, "预约失败", $insertData);
?>