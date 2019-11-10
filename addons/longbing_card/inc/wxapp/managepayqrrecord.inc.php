<?php  global $_GPC;
global $_W;
define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
$uniacid = $_W["uniacid"];
$module_name = $_W["current_module"]["name"];
$page = intval($_GPC["page"]);
$limit = intval($_GPC["limit"]);
if( !$page ) 
{
	$page = 1;
}
if( !$limit ) 
{
	$limit = 10;
}
$limit = array( $page, $limit );
$where = array( "uniacid" => $uniacid, "pay_status" => 1 );
$curr = 1;
$keyword = "";
$mark_value = "";
$deal_value = "";
if( isset($_GPC["key"]) ) 
{
	if( $_GPC["key"]["keyword"] ) 
	{
		$keyword = $_GPC["key"]["keyword"];
		$search = "%" . $_GPC["key"]["keyword"] . "%";
		$staff = pdo_get("longbing_card_user_info", array( "name like" => $search ));
		if( $staff ) 
		{
			$where["staff_id"] = $staff["fans_id"];
		}
		else 
		{
			$where["staff_id"] = 0;
		}
	}
	if( $_GPC["key"]["dateRange"] ) 
	{
		$dateRange = $_GPC["key"]["dateRange"];
		$dateRangeArr = explode(" - ", $dateRange);
		$where["create_time >"] = strtotime($dateRangeArr[0] . " 00:00:00");
		$where["update_time <"] = strtotime($dateRangeArr[1] . " 23:59:59");
	}
	$list = pdo_getslice("lb_pay_qr_record", $where, $limit, $count, array( ), "");
}
else 
{
	$list = pdo_getslice("lb_pay_qr_record", $where, $limit, $count, array( ), "", array( "id desc" ));
}
foreach( $list as $k => $v ) 
{
	$user = pdo_get("longbing_card_user", array( "id" => $v["user_id"] ));
	$list[$k]["user_name"] = $user["nickName"];
	$list[$k]["avatarUrl"] = $user["avatarUrl"];
	$staff = pdo_get("longbing_card_user_info", array( "fans_id" => $v["staff_id"] ));
	$staff["avatar"] = tomedia($staff["avatar"]);
	$list[$k]["staff_name"] = $staff["name"];
	$list[$k]["avatar"] = $staff["avatar"];
	$list[$k]["update_time"] = date("Y-m-d H:i:s", $v["update_time"]);
}
$perPage = 15;
$returnData["code"] = 0;
$returnData["data"] = $list;
$returnData["msg"] = "success";
$returnData["count"] = $count;
echo json_encode($returnData, JSON_UNESCAPED_UNICODE);
?>