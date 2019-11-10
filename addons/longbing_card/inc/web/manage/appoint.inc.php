<?php  global $_GPC;
global $_W;
define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
$uniacid = $_W["uniacid"];
$module_name = $_W["current_module"]["name"];
if( $_GPC["action"] == "add" ) 
{
	$where = array( "uniacid" => $_W["uniacid"], "status >" => -1, "title" => $_GPC["typeTitle"] );
	$item = pdo_getall("lb_appoint_classify", $where);
	if( $item ) 
	{
		message("已经存在该内容了", "", "error");
	}
	$time = time();
	$data = array( "uniacid" => $_W["uniacid"], "status" => 1, "title" => $_GPC["typeTitle"], "top" => $_GPC["typeTop"], "create_time" => $time, "update_time" => $time );
	$result = pdo_insert("lb_appoint_classify", $data);
	if( $result ) 
	{
		message("添加成功", $this->createWebUrl("manage/appointClassify"), "success");
	}
	message("添加失败", "", "error");
}
if( $_GPC["action"] == "edit" ) 
{
	$item = pdo_get("lb_appoint_classify", array( "id" => $_GPC["id"] ));
	if( !$item || empty($item) ) 
	{
		message("未找到该数据", "", "error");
	}
	$result = pdo_update("lb_appoint_classify", array( "title" => $_GPC["typeTitle"], "top" => $_GPC["typeTop"], "update_time" => time() ), array( "id" => $_GPC["id"] ));
	if( $result ) 
	{
		message("编辑成功", $this->createWebUrl("manage/appointClassify"), "success");
	}
	message("编辑失败", "", "error");
}
if( $_GPC["action"] == "disable" ) 
{
	$item = pdo_get("lb_appoint_project", array( "id" => $_GPC["id"] ));
	if( !$item || empty($item) ) 
	{
		message("未找到该数据", "", "error");
	}
	if( $item["status"] == 0 ) 
	{
		message("该数据已被禁用", "", "error");
	}
	$result = pdo_update("lb_appoint_project", array( "status" => 0, "update_time" => time() ), array( "id" => $_GPC["id"] ));
	if( $result ) 
	{
		message("禁用成功", $this->createWebUrl("manage/appoint"), "success");
	}
	message("禁用失败", "", "error");
}
if( $_GPC["action"] == "enable" ) 
{
	$item = pdo_get("lb_appoint_project", array( "id" => $_GPC["id"] ));
	if( !$item || empty($item) ) 
	{
		message("未找到该数据", "", "error");
	}
	if( $item["status"] == 1 ) 
	{
		message("该内容已启用", "", "error");
	}
	$result = pdo_update("lb_appoint_project", array( "status" => 1, "update_time" => time() ), array( "id" => $_GPC["id"] ));
	if( $result ) 
	{
		message("启用成功", $this->createWebUrl("manage/appoint"), "success");
	}
	message("启用失败", "", "error");
}
if( $_GPC["action"] == "delete" ) 
{
	$item = pdo_get("lb_appoint_project", array( "id" => $_GPC["id"] ));
	if( !$item || empty($item) ) 
	{
		message("未找到该数据", "", "error");
	}
	if( $item["status"] == -1 ) 
	{
		message("该内容已删除", "", "error");
	}
	$result = pdo_update("lb_appoint_project", array( "status" => -1, "update_time" => time() ), array( "id" => $_GPC["id"] ));
	if( $result ) 
	{
		message("删除成功", $this->createWebUrl("manage/appoint"), "success");
	}
	message("删除失败", "", "error");
}
$limit = array( 1, 15 );
$where = array( "uniacid" => $_W["uniacid"], "status >" => -1 );
$curr = 1;
if( isset($_GPC["page"]) ) 
{
	$limit[0] = $_GPC["page"];
	$curr = $_GPC["page"];
}
$list = pdo_getslice("lb_appoint_project", $where, $limit, $count, array( ), "", array( "top desc" ));
$classify_list = pdo_getall("lb_appoint_classify", array( "uniacid" => $uniacid ));
foreach( $list as $k => $v ) 
{
	$list[$k]["cover"] = tomedia($v["cover"]);
	$list[$k]["classify_title"] = "";
	foreach( $classify_list as $index => $item ) 
	{
		if( $v["classify_id"] == $item["id"] ) 
		{
			$list[$k]["classify_title"] = $item["title"];
			break;
		}
	}
	$list[$k]["days"] = explode(",", $list[$k]["days"]);
	$list[$k]["days_week"] = formatWeek($list[$k]["days"]);
	$list[$k]["days_week"] = implode(",", $list[$k]["days_week"]);
}
$perPage = 15;
load()->func("tpl");
include($this->template("manage/appoint"));
function formatWeek($data) 
{
	foreach( $data as $index => $item ) 
	{
		switch( $item ) 
		{
			case 1: $data[$index] = "星期一";
			break;
			case 2: $data[$index] = "星期二";
			break;
			case 3: $data[$index] = "星期三";
			break;
			case 4: $data[$index] = "星期四";
			break;
			case 5: $data[$index] = "星期五";
			break;
			case 6: $data[$index] = "星期六";
			break;
			case 7: $data[$index] = "星期天";
			break;
		}
	}
	return $data;
}
?>