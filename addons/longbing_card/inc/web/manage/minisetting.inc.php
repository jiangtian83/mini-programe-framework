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
if( $_GPC["action"] == "edit" ) 
{
	$appid = $_GPC["appid"];
	$appsecret = $_GPC["appsecret"];
	if( !$appid || !$appsecret ) 
	{
		message("编辑失败，appid和appsecret为必填写", "", "error");
	}
	$result = pdo_update("account_wxapp", array( "key" => $appid, "secret" => $appsecret ), array( "uniacid" => $_W["uniacid"] ));
	if( $result === 0 ) 
	{
		message("未做任何修改", $this->createWebUrl("manage/miniSetting"), "success");
	}
	if( $result ) 
	{
		message("编辑成功", $this->createWebUrl("manage/miniSetting"), "success");
	}
	message("编辑失败", "", "error");
}
$where = array( "uniacid" => $_W["uniacid"] );
$account = pdo_get("account_wxapp", array( "uniacid" => $_W["uniacid"] ));
$appid = $account["key"];
$appsecret = $account["secret"];
load()->func("tpl");
include($this->template("manage/miniSetting"));
?>