<?php  global $_GPC;
global $_W;
define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
pdo_update("lb_appoint_record", array( "status" => 3 ), array( "end_time <" => time(), "status" => 1 ));
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
$where = array( "uniacid" => $_W["uniacid"] );
$curr = $page;
$type = 0;
$statusArr = array( "已取消", "未服务", "已完成", "已过期" );
if( isset($_GPC["key"]) && isset($_GPC["key"]["projectTitle"]) && $_GPC["key"]["projectTitle"] ) 
{
	$where["project_id"] = $_GPC["key"]["projectTitle"];
}
if( isset($_GPC["key"]) && isset($_GPC["key"]["recordStatus"]) && $_GPC["key"]["recordStatus"] != 5 ) 
{
	$where["status"] = $_GPC["key"]["recordStatus"];
}
if( isset($_GPC["key"]) && isset($_GPC["key"]["searchName"]) && $_GPC["key"]["searchName"] ) 
{
	$searchName = $_GPC["key"]["searchName"];
	$searchName2 = "%" . $_GPC["key"]["searchName"] . "%";
	$where["name like"] = $searchName2;
}
$list = pdo_getslice("lb_appoint_record", $where, $limit, $count, array( ), "", array( "id desc" ));
$project_list = pdo_getall("lb_appoint_project", array( "uniacid" => $_W["uniacid"] ), array( ), "", array( ), array( 0, 100 ));
foreach( $list as $k => $v ) 
{
	foreach( $project_list as $index => $item ) 
	{
		if( $v["project_id"] == $item["id"] ) 
		{
			$list[$k]["cover"] = tomedia($item["cover"]);
			$list[$k]["project_title"] = $item["title"];
		}
	}
	$list[$k]["start_time"] = date("Y-m-d H:i", $v["start_time"]) . "-" . date("H:i", $v["end_time"]);
	$list[$k]["create_time"] = date("Y-m-d H:i", $v["create_time"]);
	switch( $v["status"] ) 
	{
		case 1: $list[$k]["status"] = "未服务";
		break;
		case 2: $list[$k]["status"] = "已完成";
		break;
		case 3: $list[$k]["status"] = "已过期";
		break;
		default: $list[$k]["status"] = "已取消";
		break;
	}
}
$perPage = $limit[1];
$returnData["code"] = 0;
$returnData["data"] = $list;
$returnData["msg"] = "success";
$returnData["count"] = $count;
echo json_encode($returnData, JSON_UNESCAPED_UNICODE);
?>