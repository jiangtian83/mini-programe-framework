<?php  global $_GPC;
global $_W;
define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
$uniacid = $_W["uniacid"];
$module_name = $_W["current_module"]["name"];
if( $_GPC["action"] == "question_switch" ) 
{
	$time = time();
	$data = array( "uniacid" => $_W["uniacid"], "update_time" => $time, "question_switch" => ($_GPC["question_switch"] ? 1 : 0) );
	$result = pdo_update("longbing_card_config", $data, array( "uniacid" => $uniacid ));
	if( $result ) 
	{
		message("操作成功", $this->createWebUrl("manage/question"), "success");
	}
	message("操作失败", "", "error");
}
if( $_GPC["action"] == "add" ) 
{
	$where = array( "uniacid" => $_W["uniacid"], "status >" => -1, "title" => $_GPC["typeTitle"] );
	$item = pdo_getall("longbing_card_qn_questionnaire", $where);
	if( $item ) 
	{
		message("已经存在该内容了", "", "error");
	}
	$time = time();
	$data = array( "uniacid" => $_W["uniacid"], "status" => 0, "title" => $_GPC["typeTitle"], "create_time" => $time, "update_time" => $time );
	$result = pdo_insert("longbing_card_qn_questionnaire", $data);
	if( $result ) 
	{
		message("添加成功", $this->createWebUrl("manage/question"), "success");
	}
	message("添加失败", "", "error");
}
if( $_GPC["action"] == "edit" ) 
{
	$item = pdo_get("longbing_card_qn_questionnaire", array( "id" => $_GPC["id"] ));
	if( !$item || empty($item) ) 
	{
		message("未找到该数据", "", "error");
	}
	$result = pdo_update("longbing_card_qn_questionnaire", array( "title" => $_GPC["typeTitle"], "update_time" => time() ), array( "id" => $_GPC["id"] ));
	if( $result ) 
	{
		message("编辑成功", $this->createWebUrl("manage/question"), "success");
	}
	message("编辑失败", "", "error");
}
if( $_GPC["action"] == "disable" ) 
{
	$item = pdo_get("longbing_card_qn_questionnaire", array( "id" => $_GPC["id"] ));
	if( !$item || empty($item) ) 
	{
		message("未找到该数据", "", "error");
	}
	if( $item["status"] == 0 ) 
	{
		message("该数据已被禁用", "", "error");
	}
	$result = pdo_update("longbing_card_qn_questionnaire", array( "status" => 0, "update_time" => time() ), array( "id" => $_GPC["id"] ));
	if( $result ) 
	{
		message("禁用成功", $this->createWebUrl("manage/question"), "success");
	}
	message("禁用失败", "", "error");
}
if( $_GPC["action"] == "enable" ) 
{
	$item = pdo_get("longbing_card_qn_questionnaire", array( "id" => $_GPC["id"] ));
	if( !$item || empty($item) ) 
	{
		message("未找到该数据", "", "error");
	}
	if( $item["status"] == 1 ) 
	{
		message("该内容已启用", "", "error");
	}
	pdo_update("longbing_card_qn_questionnaire", array( "status" => 0, "update_time" => time() ), array( "uniacid" => $uniacid ));
	$result = pdo_update("longbing_card_qn_questionnaire", array( "status" => 1, "update_time" => time(), "uniacid" => $uniacid ), array( "id" => $_GPC["id"] ));
	if( $result ) 
	{
		message("启用成功", $this->createWebUrl("manage/question"), "success");
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
$list = pdo_getslice("longbing_card_qn_questionnaire", $where, $limit, $count, array( ), "", array( "status desc" ));
$perPage = 15;
$config_info = pdo_get("longbing_card_config", array( "uniacid" => $_W["uniacid"] ));
if( !$config_info || empty($config_info) ) 
{
	pdo_insert("longbing_card_config", array( "uniacid" => $_W["uniacid"], "create_time" => time(), "update_time" => time(), "copyright" => "", "mini_template_id" => "" ));
	$config_info = pdo_get("longbing_card_config", $where);
}
load()->func("tpl");
include($this->template("manage/question"));
?>