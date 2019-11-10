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
	switch( $_GPC["sign"] ) 
	{
		case 1: $data["order_overtime"] = $_GPC["order_overtime"];
		$data["collage_overtime"] = $_GPC["collage_overtime"];
		break;
		case 2: $data["receiving"] = $_GPC["receiving"];
		$data["order_pwd"] = $_GPC["order_pwd"];
		$data["self_text"] = $_GPC["self_text"];
		break;
		case 3: $data["btn_talk"] = $_GPC["btn_talk"];
		$data["ios_pay"] = $_GPC["ios_pay"];
		$data["android_pay"] = $_GPC["android_pay"];
		break;
		case 4: $data["myshop_switch"] = $_GPC["myshop_switch"];
		break;
		case 5: $data["default_shop_pic"] = $_GPC["default_shop_pic"];
		$data["default_shop_name"] = $_GPC["default_shop_name"];
		break;
		case 6: $data["shop_version"] = $_GPC["shop_version"];
		$data["shop_carousel_more"] = $_GPC["shop_carousel_more"];
		break;
	}
	pdo_update("longbing_card_config", $data, array( "uniacid" => $uniacid ));
	if( $result === false ) 
	{
		message("编辑失败", "", "error");
	}
	message("编辑成功", $this->createWebUrl("manage/shopSetting"), "success");
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
include($this->template("manage/shopSetting"));
?>