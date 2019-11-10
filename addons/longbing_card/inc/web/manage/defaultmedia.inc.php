<?php  global $_GPC;
global $_W;
define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
$uniacid = $_W["uniacid"];
$module_name = $_W["current_module"]["name"];
if( $_GPC["action"] == "edit" ) 
{
	$data = array( );
	$data["default_video"] = $_GPC["default_video"];
	$data["default_voice"] = $_GPC["default_voice"];
	$data["default_voice_switch"] = $_GPC["default_voice_switch"];
	$data["default_video_cover"] = $_GPC["default_video_cover"];
	pdo_update("longbing_card_config", $data, array( "uniacid" => $uniacid ));
	if( $result === false ) 
	{
		message("编辑失败", "", "error");
	}
	message("编辑成功", $this->createWebUrl("manage/defaultMedia"), "success");
}
$where = array( "uniacid" => $_W["uniacid"] );
$info = pdo_get("longbing_card_config", $where);
if( !$info || empty($info) ) 
{
	pdo_insert("longbing_card_config", array( "uniacid" => $_W["uniacid"], "create_time" => time(), "update_time" => time(), "copyright" => "", "mini_template_id" => "" ));
	$info = pdo_get("longbing_card_config", $where);
}
$id = $info["id"];
load()->func("tpl");
include($this->template("manage/defaultMedia"));
?>