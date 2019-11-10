<?php  define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
global $_GPC;
global $_W;
$classify_id = $_GPC["classify_id"];
$classify_id = intval($classify_id);
if( !$classify_id ) 
{
	$classify_id = 0;
}
$limit = array( 1, 10 );
$curr = 1;
if( isset($_GPC["page"]) ) 
{
	$limit[0] = $_GPC["page"];
	$curr = $_GPC["page"];
}
$uniacid = $_W["uniacid"];
$where = array( "uniacid" => $uniacid, "status" => 1 );
if( $classify_id ) 
{
	$where["classify_id"] = $classify_id;
}
$list = pdo_getslice("lb_appoint_project", $where, $limit, $count, array( "id", "title", "cover", "desc", "limit", "appoint_price", "classify_id" ), "", array( "top desc" ));
unset($where["classify_id"]);
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
$data = array( "page" => $curr, "total_page" => ceil($count / 10), "list" => $list );
return $this->result(0, "", $data);
?>