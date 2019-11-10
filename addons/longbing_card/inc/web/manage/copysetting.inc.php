<?php  global $_GPC;
global $_W;
define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
$uniacid = $_W["uniacid"];
$module_name = $_W["current_module"]["name"];
$redis_sup_v3 = false;
$redis_server_v3 = false;
include_once($_SERVER["DOCUMENT_ROOT"] . "/addons/longbing_card/images/phpqrcode/func_longbing.php");
if( function_exists("longbing_check_redis") ) 
{
	$config = $_W["config"]["setting"]["redis"];
	$password = "";
	if( $config && isset($config["requirepass"]) && $config["requirepass"] ) 
	{
		$password = $config["requirepass"];
	}
	if( $config && isset($config["server"]) && $config["server"] && isset($config["port"]) && $config["port"] ) 
	{
		list($redis_sup_v3, $redis_server_v3) = longbing_check_redis($config["server"], $config["port"], $password);
	}
}
if( $_GPC["action"] == "edit" && $_GPC["action"] == "edit" ) 
{
	$data = array( );
	$data["mini_app_name"] = $_GPC["mini_app_name"];
	if( isset($_GPC["copyright"]) ) 
	{
		$data["copyright"] = $_GPC["copyright"];
	}
	if( isset($_GPC["logo_text"]) ) 
	{
		$data["logo_text"] = $_GPC["logo_text"];
	}
	if( isset($_GPC["logo_switch"]) ) 
	{
		$data["logo_switch"] = $_GPC["logo_switch"];
	}
	if( isset($_GPC["click_copy_way"]) ) 
	{
		$data["click_copy_way"] = $_GPC["click_copy_way"];
	}
	if( isset($_GPC["click_copy_show_img"]) ) 
	{
		$data["click_copy_show_img"] = $_GPC["click_copy_show_img"];
	}
	if( isset($_GPC["click_copy_content"]) ) 
	{
		$data["click_copy_content"] = $_GPC["click_copy_content"];
	}
	pdo_update("longbing_card_config", $data, array( "uniacid" => $uniacid ));
	if( $result === false ) 
	{
		message("编辑失败", "", "error");
	}
	message("编辑成功", $this->createWebUrl("manage/copySetting"), "success");
}
$where = array( "uniacid" => $_W["uniacid"] );
$info = pdo_get("longbing_card_config", $where);
if( !$info || empty($info) ) 
{
	pdo_insert("longbing_card_config", array( "uniacid" => $_W["uniacid"], "create_time" => time(), "update_time" => time(), "copyright" => "", "mini_template_id" => "" ));
	$info = pdo_get("longbing_card_config", $where);
}
$id = $info["id"];
$allow = true;
$checkExists = pdo_tableexists("longbing_cardauth2_config");
if( $checkExists ) 
{
	$auth_info = pdo_get("longbing_cardauth2_config", array( "modular_id" => $_W["uniacid"] ));
	if( $auth_info && $auth_info["copyright_id"] ) 
	{
		$allow = false;
	}
}
load()->func("tpl");
include($this->template("manage/copySetting"));
?>