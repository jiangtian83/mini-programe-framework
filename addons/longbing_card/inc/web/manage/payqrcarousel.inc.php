<?php  global $_GPC;
global $_W;
define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
$uniacid = $_W["uniacid"];
$module_name = $_W["current_module"]["name"];
$add = $this->createWebUrl("manage/payqrCarouselEdit");
if( $_GPC["action"] == "delete" ) 
{
	$id = $_GPC["id"];
	$info = pdo_get("lb_pay_qr_carousel", array( "id" => $_GPC["id"] ));
	if( !$info || empty($info) ) 
	{
		message("未找到数据", "", "error");
	}
	$result = pdo_update("lb_pay_qr_carousel", array( "status" => -1, "update_time" => time() ), array( "id" => $_GPC["id"] ));
	if( $result ) 
	{
		if( $redis_sup_v3 ) 
		{
			$redis_server_v3->flushDB();
		}
		message("删除成功", $this->createWebUrl("manage/payqrCarousel"), "success");
	}
	message("删除失败", "", "error");
}
if( $_GPC["action"] == "disable" ) 
{
	$job = pdo_get("lb_pay_qr_carousel", array( "id" => $_GPC["id"] ));
	if( !$job || empty($job) ) 
	{
		message("未找到数据", "", "error");
	}
	$result = pdo_update("lb_pay_qr_carousel", array( "status" => 0, "update_time" => time() ), array( "id" => $_GPC["id"] ));
	if( $result ) 
	{
		message("禁用成功", $this->createWebUrl("manage/payqrCarousel"), "success");
	}
	message("禁用失败", "", "error");
}
if( $_GPC["action"] == "enable" ) 
{
	$job = pdo_get("lb_pay_qr_carousel", array( "id" => $_GPC["id"] ));
	if( !$job || empty($job) ) 
	{
		message("未找到数据", "", "error");
	}
	$result = pdo_update("lb_pay_qr_carousel", array( "status" => 1, "update_time" => time() ), array( "id" => $_GPC["id"] ));
	if( $result ) 
	{
		message("启用成功", $this->createWebUrl("manage/payqrCarousel"), "success");
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
$list = pdo_getslice("lb_pay_qr_carousel", $where, $limit, $count, array( ), "", array( "top desc" ));
foreach( $list as $k => $v ) 
{
	$list[$k]["img"] = tomedia($v["img"]);
}
$perPage = 15;
load()->func("tpl");
include($this->template("manage/payqrCarousel"));
?>