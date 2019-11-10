<?php  global $_GPC;
global $_W;
define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
$uniacid = $_W["uniacid"];
$module_name = $_W["current_module"]["name"];
$carouselEdit = $this->createWebUrl("manage/shopCarouselEdit");
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
if( $_GPC["action"] == "disable" ) 
{
	$job = pdo_get("longbing_card_shop_carousel", array( "id" => $_GPC["id"] ));
	if( !$job || empty($job) ) 
	{
		message("未找到数据", "", "error");
	}
	$result = pdo_update("longbing_card_shop_carousel", array( "status" => 0, "update_time" => time() ), array( "id" => $_GPC["id"] ));
	if( $result ) 
	{
		if( $redis_sup_v3 ) 
		{
			$redis_server_v3->flushDB();
		}
		message("禁用成功", $this->createWebUrl("manage/shopCarousel"), "success");
	}
	message("禁用失败", "", "error");
}
if( $_GPC["action"] == "enable" ) 
{
	$job = pdo_get("longbing_card_shop_carousel", array( "id" => $_GPC["id"] ));
	if( !$job || empty($job) ) 
	{
		message("未找到数据", "", "error");
	}
	$result = pdo_update("longbing_card_shop_carousel", array( "status" => 1, "update_time" => time() ), array( "id" => $_GPC["id"] ));
	if( $result ) 
	{
		if( $redis_sup_v3 ) 
		{
			$redis_server_v3->flushDB();
		}
		message("启用成功", $this->createWebUrl("manage/shopCarousel"), "success");
	}
	message("启用失败", "", "error");
}
if( $_GPC["action"] == "delete" ) 
{
	$id = $_GPC["id"];
	$info = pdo_get("longbing_card_shop_carousel", array( "id" => $_GPC["id"] ));
	if( !$info || empty($info) ) 
	{
		message("未找到数据", "", "error");
	}
	$result = pdo_update("longbing_card_shop_carousel", array( "status" => -1, "update_time" => time() ), array( "id" => $_GPC["id"] ));
	if( $result ) 
	{
		if( $redis_sup_v3 ) 
		{
			$redis_server_v3->flushDB();
		}
		message("删除成功", $this->createWebUrl("manage/shopCarousel"), "success");
	}
	message("删除失败", "", "error");
}
$limit = array( 1, $this->limit );
$where = array( "uniacid" => $_W["uniacid"], "status >" => -1 );
$curr = 1;
if( isset($_GPC["page"]) ) 
{
	$limit[0] = $_GPC["page"];
	$curr = $_GPC["page"];
}
$list = pdo_getslice("longbing_card_shop_carousel", $where, $limit, $count, array( ), "", array( "top desc" ));
$company = pdo_getall("longbing_card_company", $where, array( ), "", array( "top desc" ));
foreach( $list as $index => $item ) 
{
	$list[$index]["company_name"] = "";
	$list[$index]["img"] = tomedia($item["img"]);
	foreach( $company as $k => $v ) 
	{
		if( $item["c_id"] == $v["id"] ) 
		{
			$list[$index]["company_name"] = $v["name"];
		}
	}
}
$perPage = $this->limit;
load()->func("tpl");
include($this->template("manage/shopCarousel"));
?>