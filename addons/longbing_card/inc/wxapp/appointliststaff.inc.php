<?php  define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
pdo_update("lb_appoint_record", array( "status" => 3 ), array( "end_time <" => time(), "status" => 1 ));
global $_GPC;
global $_W;
$uniacid = $_W["uniacid"];
$user_id = $_GPC["user_id"];
$type = $_GPC["type"];
if( !$user_id || !$type ) 
{
	return $this->result(-1, "请传入参数", array( ));
}
$curr = 1;
$len = 15;
if( isset($_GPC["page"]) ) 
{
	$curr = $_GPC["page"];
}
$start = ($curr - 1) * $len;
switch( $type ) 
{
	case 1: $list = pdo_fetchall("SELECT a.*, b.title, b.cover, c.title as classify_title FROM " . tablename("lb_appoint_record") . " a LEFT JOIN " . tablename("lb_appoint_project") . " b ON a.project_id = b.id LEFT JOIN " . tablename("lb_appoint_classify") . " c ON b.classify_id = c.id WHERE a.uniacid = " . $uniacid . " && a.to_uid = " . $user_id . " && a.status = " . $type . " \r\n    ORDER BY a.id DESC LIMIT " . $start . ", " . $len);
	$count = pdo_fetch("SELECT COUNT(*) as count_data FROM " . tablename("lb_appoint_record") . " WHERE uniacid = " . $uniacid . " && to_uid = " . $user_id . " && status = " . $type);
	break;
	case 2: $list = pdo_fetchall("SELECT a.*, b.title, b.cover, c.title as classify_title FROM " . tablename("lb_appoint_record") . " a LEFT JOIN " . tablename("lb_appoint_project") . " b ON a.project_id = b.id LEFT JOIN " . tablename("lb_appoint_classify") . " c ON b.classify_id = c.id WHERE a.uniacid = " . $uniacid . " && a.to_uid = " . $user_id . " && a.status = " . $type . " \r\n    ORDER BY a.id DESC LIMIT " . $start . ", " . $len);
	$count = pdo_fetch("SELECT COUNT(*) as count_data FROM " . tablename("lb_appoint_record") . " WHERE uniacid = " . $uniacid . " && to_uid = " . $user_id . " && status = " . $type);
	break;
	case 3: $list = pdo_fetchall("SELECT a.*, b.title, b.cover, c.title as classify_title FROM " . tablename("lb_appoint_record") . " a LEFT JOIN " . tablename("lb_appoint_project") . " b ON a.project_id = b.id LEFT JOIN " . tablename("lb_appoint_classify") . " c ON b.classify_id = c.id WHERE a.uniacid = " . $uniacid . " && a.to_uid = " . $user_id . " && a.status = " . $type . " \r\n    ORDER BY a.id DESC LIMIT " . $start . ", " . $len);
	$count = pdo_fetch("SELECT COUNT(*) as count_data FROM " . tablename("lb_appoint_record") . " WHERE uniacid = " . $uniacid . " && to_uid = " . $user_id . " && status = " . $type);
	break;
	default: $list = pdo_fetchall("SELECT a.*, b.title, b.cover, c.title as classify_title FROM " . tablename("lb_appoint_record") . " a LEFT JOIN " . tablename("lb_appoint_project") . " b ON a.project_id = b.id LEFT JOIN " . tablename("lb_appoint_classify") . " c ON b.classify_id = c.id WHERE a.uniacid = " . $uniacid . " && a.to_uid = " . $user_id . " && a.status = 0 \r\n    ORDER BY a.id DESC LIMIT " . $start . ", " . $len);
	$count = pdo_fetch("SELECT COUNT(*) as count_data FROM " . tablename("lb_appoint_record") . " WHERE uniacid = " . $uniacid . " && to_uid = " . $user_id . " && status = 0");
	break;
}
foreach( $list as $index => $item ) 
{
	$list[$index]["cover"] = tomedia($item["cover"]);
	$list[$index]["start_time"] = date("Y-m-d H:i", $item["start_time"]);
	$list[$index]["create_time"] = date("Y-m-d H:i", $item["create_time"]);
}
$data = array( "page" => $curr, "total_page" => ceil($count["count_data"] / 15), "total_count" => $count["count_data"], "list" => $list );
return $this->result(0, "", $data);
?>