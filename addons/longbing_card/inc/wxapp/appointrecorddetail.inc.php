<?php  define("ROOT_PATH", IA_ROOT . "/addons/longbing_card/");
is_file(ROOT_PATH . "/inc/we7.php") or exit( "Access Denied Longbing" );
require_once(ROOT_PATH . "/inc/we7.php");
global $_GPC;
global $_W;
$id = $_GPC["id"];
if( !$id ) 
{
	return $this->result(-1, "请传入参数", array( ));
}
$uniacid = $_W["uniacid"];
$where = array( "uniacid" => $uniacid, "id" => $id );
$info = pdo_fetch("SELECT a.*, b.title, b.cover, c.title as classify_title FROM " . tablename("lb_appoint_record") . " a LEFT JOIN " . tablename("lb_appoint_project") . " b ON a.project_id = b.id LEFT JOIN " . tablename("lb_appoint_classify") . " c ON b.classify_id = c.id WHERE a.uniacid = " . $uniacid . " && a.id = " . $id);
$info["cover"] = tomedia($info["cover"]);
$info["start_time"] = date("Y-m-d H:i", $info["start_time"]) . "-" . date("H:i", $info["end_time"]);
$info["create_time"] = date("Y-m-d H:i", $info["create_time"]);
return $this->result(0, "", $info);
?>