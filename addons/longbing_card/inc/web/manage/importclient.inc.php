<?php  global $_GPC;
global $_W;
define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
$uniacid = $_W["uniacid"];
$module_name = $_W["current_module"]["name"];
if( $_GPC["action"] == "edit" ) 
{
	$id = ($_GPC["id"] ? $_GPC["id"] : 0);
	$nickName = $_GPC["nickName"];
	$avatarUrl = $_GPC["avatarUrl"];
	$result = false;
	$time = time();
	$data = array( "nickName" => $nickName, "avatarUrl" => $avatarUrl, "update_time" => $time, "import" => 1 );
	if( $id ) 
	{
		$result = pdo_update("longbing_card_user", $data, array( "id" => $id ));
	}
	else 
	{
		$data["create_time"] = $time;
		$data["openid"] = "import-" . uniqid();
		$data["uniacid"] = $uniacid;
		$result = pdo_insert("longbing_card_user", $data);
	}
	if( $result ) 
	{
		message($id, $this->createWebUrl("manage/importClient"), "success");
	}
	message("操作失败" . $id, $result, "error");
}
$where = array( "uniacid" => $_W["uniacid"], "import" => 1 );
$id = 0;
$info = array( );
if( isset($_GPC["id"]) ) 
{
	$where["id"] = $_GPC["id"];
	$id = $_GPC["id"];
	$info = pdo_get("longbing_card_user", $where);
	if( $info["images"] ) 
	{
		$info["avatarUrl"] = tomedia($info["avatarUrl"]);
	}
}
load()->func("tpl");
include($this->template("manage/importClient"));
?>