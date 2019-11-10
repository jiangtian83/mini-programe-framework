<?php  define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
global $_GPC;
global $_W;
$to_uid = (isset($_GPC["to_uid"]) ? $_GPC["to_uid"] : 0);
$limit = array( 1, 10 );
$curr = 1;
if( isset($_GPC["page"]) ) 
{
	$limit[0] = $_GPC["page"];
	$curr = $_GPC["page"];
}
$uniacid = $_W["uniacid"];
$where = array( "uniacid" => $uniacid, "status" => 1 );
$list = pdo_getslice("lb_appoint_project", $where, $limit, $count, array( "id", "title", "cover", "desc", "limit", "appoint_price", "classify_id" ), "", array( "top desc" ));
$classify_list = pdo_getall("lb_appoint_classify", $where, array( "id", "title" ));
foreach( $list as $k => $v ) 
{
	$list[$k]["cover"] = tomedia($v["cover"]);
	foreach( $classify_list as $index => $item ) 
	{
		if( $item["id"] == $v["classify_id"] ) 
		{
			$list[$k]["classify_title"] = $item["title"];
		}
	}
}
$staff_company_id = 0;
if( $to_uid ) 
{
	$staff_info = pdo_get("longbing_card_user_info", array( "fans_id" => $to_uid ));
	if( $staff_info ) 
	{
		$staff_info["avatar"] = tomedia($staff_info["avatar"]);
		$staff_company_id = $staff_info["company_id"];
		$job = pdo_get("longbing_card_job", array( "id" => $staff_info["job_id"] ));
		$staff_info["job"] = ($job ? $job["name"] : "");
	}
}
if( $staff_company_id ) 
{
	$staff_company_info = pdo_get("longbing_card_company", array( "id" => $staff_company_id, "status" => 1 ));
	if( !$staff_company_info ) 
	{
		$staff_company_info = pdo_get("longbing_card_company", array( "uniacid" => $uniacid, "status" => 1 ));
	}
}
else 
{
	$staff_company_info = pdo_get("longbing_card_company", array( "uniacid" => $uniacid, "status" => 1 ));
}
if( $staff_company_info ) 
{
	$staff_company_info["logo"] = tomedia($staff_company_info["logo"]);
	$staff_company_info["desc"] = tomedia($staff_company_info["desc"]);
}
$data = array( "page" => $curr, "total_page" => ceil($count / 10), "list" => $list, "classify_list" => $classify_list );
if( $to_uid ) 
{
	$data["staff_info"] = $staff_info;
}
$data["staff_company_info"] = $staff_company_info;
return $this->result(0, "", $data);
?>