<?php  global $_GPC;
global $_W;
define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
$uniacid = $_W["uniacid"];
$module_name = $_W["current_module"]["name"];
if( $_GPC["action"] == "edit" ) 
{
	$id = $_GPC["id"];
	$result = false;
	$data["img"] = $_GPC["img"];
	$data["top"] = $_GPC["top"];
	$time = time();
	$data["update_time"] = $time;
	if( $id ) 
	{
		$result = pdo_update("lb_pay_qr_carousel", $data, array( "id" => $id ));
	}
	else 
	{
		$data["create_time"] = $time;
		$data["status"] = 1;
		$data["uniacid"] = $uniacid;
		$result = pdo_insert("lb_pay_qr_carousel", $data);
	}
	if( $result ) 
	{
		message($id, $this->createWebUrl("manage/goods"), "success");
	}
	message("操作失败", "", "error");
}
$where = array( "uniacid" => $_W["uniacid"] );
$id = 0;
$info = array( );
if( isset($_GPC["id"]) ) 
{
	$where["id"] = $_GPC["id"];
	$id = $_GPC["id"];
	$info = pdo_get("lb_pay_qr_carousel", $where);
}
load()->func("tpl");
include($this->template("manage/payqrCarouselEdit"));
?>