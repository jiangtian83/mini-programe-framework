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
	$data["title"] = $_GPC["title"];
	$data["first_full"] = $_GPC["first_full"];
	$data["first_reduce"] = $_GPC["first_reduce"];
	$data["company_name"] = $_GPC["company_name"];
	$data["company_logo"] = $_GPC["company_logo"];
	pdo_update("lb_pay_qr_config", $data, array( "uniacid" => $uniacid ));
	if( $result === false ) 
	{
		message("编辑失败", "", "error");
	}
	message("编辑成功", $this->createWebUrl("manage/payqrSetting"), "success");
}
$where = array( "uniacid" => $_W["uniacid"] );
$info = pdo_get("lb_pay_qr_config", $where);
if( !$info || empty($info) ) 
{
	pdo_insert("lb_pay_qr_config", array( "uniacid" => $_W["uniacid"], "create_time" => time(), "update_time" => time() ));
	$info = pdo_get("lb_pay_qr_config", $where);
}
$id = $info["id"];
load()->func("tpl");
include($this->template("manage/payqrSetting"));
?>