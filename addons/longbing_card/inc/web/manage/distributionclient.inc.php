<?php  global $_GPC;
global $_W;
define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
$uniacid = $_W["uniacid"];
$module_name = $_W["current_module"]["name"];
$id = ($_GPC["id"] ? $_GPC["id"] : 0);
if( !$id ) 
{
	echo "请传入参数";
	exit();
}
if( $_GPC["action"] == "edit" && $_GPC["id"] ) 
{
	$coll = pdo_getall("longbing_card_collection", array( "uniacid" => $uniacid, "uid" => $_GPC["id"] ));
	$delete_arr = array( );
	$insert_arr = array( );
	foreach( $_GPC["staffs"] as $index => $item ) 
	{
		$insert = true;
		foreach( $coll as $k => $v ) 
		{
			if( $v["to_uid"] == $item ) 
			{
				$insert = false;
				break;
			}
		}
		if( $insert ) 
		{
			array_push($insert_arr, $item);
		}
	}
	foreach( $coll as $index => $item ) 
	{
		$del = true;
		foreach( $_GPC["staffs"] as $k => $v ) 
		{
			if( $v == $item["to_uid"] ) 
			{
				$del = false;
				break;
			}
		}
		if( $del ) 
		{
			array_push($delete_arr, $item["to_uid"]);
		}
	}
	$sql = "INSERT INTO " . tablename("longbing_card_collection") . " (`uniacid`, `uid`, `to_uid`, `create_time`, `update_time`, `distribution`) values ";
	$time = time();
	if( is_array($insert_arr) ) 
	{
		foreach( $insert_arr as $index => $item ) 
		{
			$tmp = "(" . $uniacid . ", " . $_GPC["id"] . ", " . $item . ", " . $time . ", " . $time . ", 1),";
			$sql .= $tmp;
		}
	}
	$sql = trim($sql, ",");
	if( is_array($insert_arr) && count($insert_arr) ) 
	{
		$result = pdo_query($sql);
	}
	$sql = "DELETE FROM " . tablename("longbing_card_collection") . " WHERE `to_uid` in (";
	if( is_array($delete_arr) ) 
	{
		foreach( $delete_arr as $index => $item ) 
		{
			$tmp = (string) $item . ",";
			$sql .= $tmp;
		}
	}
	$sql = trim($sql, ",");
	$sql .= ") && `uid` = " . $_GPC["id"];
	$result = false;
	if( is_array($delete_arr) && count($delete_arr) ) 
	{
		$result = pdo_query($sql);
	}
	message($id, $this->createWebUrl("manage/client"), "success");
}
$coll = pdo_getall("longbing_card_collection", array( "uniacid" => $uniacid, "uid" => $id ));
$staffs = pdo_getall("longbing_card_user_info", array( "uniacid" => $uniacid, "is_staff" => 1 ), array( "id", "fans_id", "name" ));
foreach( $staffs as $index => $item ) 
{
	$staffs[$index]["name"] = ($item["name"] ? $item["name"] : "未填写姓名");
	$staffs[$index]["bind"] = 0;
	foreach( $coll as $k => $v ) 
	{
		if( $item["fans_id"] == $v["to_uid"] ) 
		{
			$staffs[$index]["bind"] = 1;
		}
	}
}
load()->func("tpl");
include($this->template("manage/distributionClient"));
?>