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
	$item = pdo_get("lb_appoint_classify", array( "id" => $_GPC["id"] ));
	if( !$item || empty($item) ) 
	{
		message("未找到该数据", "", "error");
	}
	if( $item["status"] == 0 ) 
	{
		message("该数据已被禁用", "", "error");
	}
	$result = pdo_update("lb_appoint_classify", array( "status" => 0, "update_time" => time() ), array( "id" => $_GPC["id"] ));
	if( $result ) 
	{
		message("禁用成功", $this->createWebUrl("manage/appointClassify"), "success");
	}
	message("禁用失败", "", "error");
}
if( $_GPC["action"] == "enable" ) 
{
	$item = pdo_get("lb_appoint_classify", array( "id" => $_GPC["id"] ));
	if( !$item || empty($item) ) 
	{
		message("未找到该数据", "", "error");
	}
	if( $item["status"] == 1 ) 
	{
		message("该内容已启用", "", "error");
	}
	$result = pdo_update("lb_appoint_classify", array( "status" => 1, "update_time" => time() ), array( "id" => $_GPC["id"] ));
	if( $result ) 
	{
		message("启用成功", $this->createWebUrl("manage/appointClassify"), "success");
	}
	message("启用失败", "", "error");
}
$limit = array( 1, 15 );
$where = array( "uniacid" => $_W["uniacid"], "status >" => -1 );
$curr = 1;
if( isset($_GPC["page"]) ) 
{
	$limit[0] = $_GPC["page"];
	$curr = $_GPC["page"];
}
$list = pdo_getslice("lb_appoint_classify", $where, $limit, $count, array( ), "", array( "top desc" ));
$perPage = 15;
load()->func("tpl");
include($this->template("manage/appointClassify"));
?>