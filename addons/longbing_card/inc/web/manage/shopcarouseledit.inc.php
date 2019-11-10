<?php  define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
global $_GPC;
global $_W;
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
	$table_name = false;
	$data = $_GPC["formData"];
	$data["update_time"] = time();
	$id = $_GPC["id"];
	$result = false;
	if( $id ) 
	{
		$result = pdo_update("longbing_card_shop_carousel", $data, array( "id" => $id ));
	}
	else 
	{
		$data["create_time"] = time();
		$data["uniacid"] = $_W["uniacid"];
		$result = pdo_insert("longbing_card_shop_carousel", $data);
	}
	if( $result ) 
	{
		if( $redis_sup_v3 ) 
		{
			$redis_server_v3->flushDB();
		}
		message("操作成功", $this->createWebUrl("manage/shopCarousel"), "success");
	}
	message("操作失败", "", "error");
}
$where = array( "uniacid" => $_W["uniacid"] );
$id = 0;
$info = array( );
$company = pdo_getall("longbing_card_company", $where, array( ), "", array( "top desc" ));
if( isset($_GPC["id"]) && $_GPC["id"] ) 
{
	$id = $_GPC["id"];
	$where["id"] = $_GPC["id"];
	$info = pdo_get("longbing_card_shop_carousel", $where);
}
else 
{
	$info["top"] = 0;
}
load()->func("tpl");
include($this->template("manage/shopCarouselEdit"));
?>