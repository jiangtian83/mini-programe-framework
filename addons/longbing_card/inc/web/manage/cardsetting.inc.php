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
	$data["force_phone"] = $_GPC["force_phone"];
	$data["preview_switch"] = $_GPC["preview_switch"];
	$data["card_type"] = $_GPC["card_type"];
	$data["exchange_switch"] = $_GPC["exchange_switch"];
	$data["motto_switch"] = $_GPC["motto_switch"];
	$data["btn_consult"] = $_GPC["btn_consult"];
	pdo_update("longbing_card_config", $data, array( "uniacid" => $uniacid ));
	if( $result === false ) 
	{
		message("编辑失败", "", "error");
	}
	message("编辑成功", $this->createWebUrl("manage/cardSetting"), "success");
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
include($this->template("manage/cardSetting"));
?>